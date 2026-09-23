<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PasienAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('pasien.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
            'tanggal_lahir' => 'required|date',
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal Lahir wajib diisi.',
        ]);

        $pasien = Pasien::where('nik', $request->nik)
            ->where('tanggal_lahir', $request->tanggal_lahir)
            ->first();

        if ($pasien) {
            Auth::guard('pasien')->login($pasien);

            return redirect()->route('pasien.dashboard');
        }

        return back()->withErrors([
            'nik' => 'Data NIK dan Tanggal Lahir tidak cocok.',
        ])->withInput($request->only('nik'));
    }

    public function logout(Request $request)
    {
        Auth::guard('pasien')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pasien.login');
    }
}
