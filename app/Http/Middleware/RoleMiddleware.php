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

        if (in_array($user->peran, $roles)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
