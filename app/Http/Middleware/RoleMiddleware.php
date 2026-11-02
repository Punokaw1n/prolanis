<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Check if user is active
        if (! $user->is_active) {
            auth()->logout();

            return redirect('/login')->withErrors(['username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.']);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if ($user->peran === 'admin' || in_array($user->peran, $roles, true)) {
            // Dokter (termasuk dr. Ageng selaku Kepala Puskesmas) disamakan fiturnya dengan dokter:
            // Tidak boleh mengakses modul farmasi maupun modul admin murni (petugas, prolanis jadwal, consent, log).
            if ($user->isDokter()) {
                if ($roles === ['farmasi'] || $roles === ['admin']) {
                    abort(403, 'Anda tidak memiliki akses ke halaman ini.');
                }
            }

            return $next($request);
        }

        // Cek kemampuan akses berbasis profesi/jabatan
        foreach ($roles as $role) {
            if ($role === 'prb' && method_exists($user, 'canAccessPrb') && $user->canAccessPrb()) {
                return $next($request);
            }
            if ($role === 'farmasi' && method_exists($user, 'canAccessFarmasi') && $user->canAccessFarmasi()) {
                return $next($request);
            }
            if ($role === 'pengingat' && method_exists($user, 'canAccessPengingat') && $user->canAccessPengingat()) {
                return $next($request);
            }
            if ($role === 'dokter' && method_exists($user, 'isDokter') && ($user->isAdmin() || $user->isDokter())) {
                return $next($request);
            }
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
