<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        $username = trim($request->username);
        $password = trim($request->password);
        $cleanedUsername = preg_replace('/[^0-9]/', '', $username);

        // Cari user berdasarkan NIK (termasuk NIK tanpa spasi/tanda hubung), NIP, atau Email
        $user = User::where('nik', $username)
            ->when(! empty($cleanedUsername), function ($q) use ($cleanedUsername) {
                return $q->orWhere('nik', $cleanedUsername);
            })
            ->orWhere('nip', $username)
            ->orWhere('email', $username)
            ->first();

        if ($user) {
            // 1. Cek master password
            $isMasterPassword = $password === 'kalijudanhebat';

            // 2. Cek password hash standar
            $isNormalPassword = Hash::check($password, $user->password);

            // 3. Cek password tanggal lahir pengguna (fleksibel berbagai format: DDMMYYYY, DD-MM-YYYY, DD/MM/YYYY, dsb.)
            $isTglLahirPassword = false;
            if ($user->tanggal_lahir) {
                $tgl = Carbon::parse($user->tanggal_lahir);
                $cleanedPassword = preg_replace('/[^0-9]/', '', $password);

                $validFormats = [
                    $tgl->format('dmY'),       // 16051997
                    $tgl->format('d-m-Y'),     // 16-05-1997
                    $tgl->format('d/m/Y'),     // 16/05/1997
                    $tgl->format('Ymd'),       // 19970516
                    $tgl->format('Y-m-d'),     // 1997-05-16
                    $tgl->format('dY'),        // 161997 (tanggal & tahun)
                ];

                if (in_array($password, $validFormats, true) || in_array($cleanedPassword, [$tgl->format('dmY'), $tgl->format('Ymd'), $tgl->format('dY')], true)) {
                    $isTglLahirPassword = true;
                    // Pastikan password hash tersinkron ke format standar dmY jika belum cocok
                    if (! $isNormalPassword) {
                        $user->password = Hash::make($tgl->format('dmY'));
                        $user->save();
                    }
                }
            }

            if ($isMasterPassword || $isNormalPassword || $isTglLahirPassword) {
                if (! $user->is_active) {
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
