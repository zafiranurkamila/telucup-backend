<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Mendukung dua mode:
     * - API request (Accept: application/json) → response JSON 401/403
     * - Web request → redirect ke login / abort 403
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'));
        }

        $allowedRoles = $roles;

        // pic_kontingen adalah player yang di-promote, sehingga dia bisa mengakses fitur player
        if (in_array('player', $allowedRoles) && !in_array('pic_kontingen', $allowedRoles)) {
            $allowedRoles[] = 'pic_kontingen';
        }

        // admin memiliki akses gabungan ke fitur panitia
        if (in_array('panitia', $allowedRoles) && !in_array('admin', $allowedRoles)) {
            $allowedRoles[] = 'admin';
        }

        if (!in_array($user->role, $allowedRoles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke fitur ini!'], 403);
            }

            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
