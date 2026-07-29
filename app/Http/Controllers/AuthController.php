<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller {
    public function show(){ return view('auth.login'); }
    public function registerForm(){ return view('auth.register-applicant'); }
    public function register(Request $request){
        $data=$request->validate([
            'name'=>'required|string|max:255',
            'institution'=>'nullable|string|max:255',
            'username'=>'required|string|max:100|alpha_dash|unique:users',
            'email'=>'required|email|max:255|unique:users',
            'password'=>'required|min:8|confirmed',
        ]);
        $user=User::create([...$data,'role'=>'pemohon','access_level'=>'read','must_change_password'=>false,'is_active'=>true]);
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('lab-requests.index')->with('success','Akun pemohon berhasil dibuat. Silakan isi permohonan pengujian laboratorium.');
    }
    public function login(Request $request){
        $data=$request->validate(['username'=>'required','password'=>'required']);
        if(Auth::attempt($data, $request->boolean('remember'))){
            $request->session()->regenerate();
            if(!auth()->user()->is_active){ Auth::logout(); return back()->withErrors(['username'=>'Akun tidak aktif.']); }
            return redirect()->intended(auth()->user()->role==='pemohon'?route('lab-requests.index'):'/dashboard');
        }
        return back()->withErrors(['username'=>'Nama pengguna atau kata sandi tidak benar.'])->onlyInput('username');
    }
    public function logout(Request $request){ Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect('/login'); }
}
