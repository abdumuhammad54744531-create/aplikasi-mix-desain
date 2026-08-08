<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index()
    {
        return view('accounts.index', [
            'users' => User::orderBy('name')->get(),
            'permissionModules' => config('report_permissions.modules'),
            'actionLabels' => config('report_permissions.action_labels'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255', 'username' => 'required|max:100|unique:users',
            'email' => 'required|email|unique:users', 'password' => 'required|min:8|confirmed',
            'role' => 'required|in:administrator,teknisi,pemohon', 'access_level' => 'nullable|in:read,edit',
            'permissions' => 'nullable|array', 'permissions.*' => 'string',
            'employee_number' => 'nullable|max:100', 'position' => 'nullable|max:255',
            'institution' => 'nullable|max:255', 'approval_authority' => 'nullable|max:255',
        ]);
        $data['permissions'] = $this->cleanPermissions($data['permissions'] ?? []);
        $data['permissions_configured'] = true;
        $data['access_level'] = $data['role'] === 'pemohon' ? 'read' : 'edit';
        User::create([...$data, 'is_active' => true, 'must_change_password' => true]);

        return back()->with('success', 'Akun dan hak akses menu berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|max:255', 'username' => ['required', 'max:100', Rule::unique('users')->ignore($user)],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user)],
            'role' => 'required|in:administrator,teknisi,pemohon', 'is_active' => 'nullable|boolean',
            'permissions' => 'nullable|array', 'permissions.*' => 'string',
            'employee_number' => 'nullable|max:100', 'position' => 'nullable|max:255',
            'institution' => 'nullable|max:255', 'approval_authority' => 'nullable|max:255',
        ]);
        if ($user->id === auth()->id() && in_array($user->role, ['admin', 'administrator'], true)) {
            $data['role'] = $user->role;
        }
        $data['permissions'] = $this->cleanPermissions($data['permissions'] ?? []);
        $data['permissions_configured'] = true;
        $data['access_level'] = $data['role'] === 'pemohon' ? 'read' : 'edit';
        $data['is_active'] = $user->id === auth()->id() ? true : $request->boolean('is_active');
        $user->update($data);

        return back()->with('success', 'Akun dan hak akses berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id() || in_array($user->role, ['admin', 'administrator'], true), 422, 'Administrator utama atau akun yang sedang dipakai tidak dapat dihapus.');
        $user->delete();

        return back()->with('success', 'Akun pengguna berhasil dihapus.');
    }

    public function password(Request $request)
    {
        $data = $request->validate(['current_password' => 'required', 'password' => 'required|min:8|confirmed']);
        if (! Hash::check($data['current_password'], auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi lama tidak sesuai.']);
        }
        auth()->user()->update(['password' => $data['password'], 'must_change_password' => false]);

        return back()->with('success', 'Kata sandi berhasil diganti.');
    }

    private function cleanPermissions(array $permissions): array
    {
        $allowed = collect(config('report_permissions.modules'))->flatMap(
            fn ($module, $key) => collect($module['actions'])->map(fn ($action) => $key.'.'.$action)
        )->all();

        return array_values(array_intersect($permissions, $allowed));
    }
}
