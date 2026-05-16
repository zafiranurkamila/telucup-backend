<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $allowedRoles = $roles;
        // pic_kontingen adalah player yang di-promote, sehingga dia bisa mengakses fitur player
        if (in_array('player', $allowedRoles) && !in_array('pic_kontingen', $allowedRoles)) {
            $allowedRoles[] = 'pic_kontingen';
        }

        if (!in_array($user->role, $allowedRoles)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke fitur ini!'], 403);
        }

        return $next($request);
    }
}
