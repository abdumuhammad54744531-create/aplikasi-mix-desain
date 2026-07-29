<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
class AccountController extends Controller {
 private function admin():void{abort_unless(in_array(auth()->user()->role,['admin','administrator']),403);}
 public function index(){ $this->admin();return view('accounts.index',['users'=>User::orderBy('name')->get()]); }
 public function store(Request $r){$this->admin();$d=$r->validate(['name'=>'required|max:255','username'=>'required|max:100|unique:users','email'=>'required|email|unique:users','password'=>'required|min:8|confirmed','role'=>'required|in:teknisi,pemohon','access_level'=>'required|in:read,edit','employee_number'=>'nullable|max:100','position'=>'nullable|max:255','institution'=>'nullable|max:255','approval_authority'=>'nullable|max:255']);if($d['role']==='pemohon')$d['access_level']='read';User::create([...$d,'is_active'=>true,'must_change_password'=>true]);return back()->with('success','Akun berhasil ditambahkan.');}
 public function update(Request $r,User $user){$this->admin();$d=$r->validate(['name'=>'required|max:255','username'=>['required','max:100',Rule::unique('users')->ignore($user)],'email'=>['required','email',Rule::unique('users')->ignore($user)],'role'=>'nullable|in:teknisi,pemohon','access_level'=>'required|in:read,edit','is_active'=>'nullable|boolean','employee_number'=>'nullable|max:100','position'=>'nullable|max:255','institution'=>'nullable|max:255','approval_authority'=>'nullable|max:255']);if(in_array($user->role,['admin','administrator'],true))unset($d['role']);elseif(($d['role']??$user->role)==='pemohon')$d['access_level']='read';$user->update([...$d,'is_active'=>$user->id===auth()->id()?true:$r->boolean('is_active')]);return back()->with('success','Akun berhasil diperbarui.');}
 public function password(Request $r){$d=$r->validate(['current_password'=>'required','password'=>'required|min:8|confirmed']);if(!Hash::check($d['current_password'],auth()->user()->password))return back()->withErrors(['current_password'=>'Kata sandi lama tidak sesuai.']);auth()->user()->update(['password'=>$d['password'],'must_change_password'=>false]);return back()->with('success','Kata sandi berhasil diganti.');}
}
