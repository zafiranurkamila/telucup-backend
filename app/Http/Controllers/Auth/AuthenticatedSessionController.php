<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     * Jika user sudah login, redirect ke dashboard sesuai role.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to($this->dashboardForRole(Auth::user()->role));
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     * Setelah login berhasil, redirect ke dashboard berdasarkan role user.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $role = Auth::user()->role;

        return redirect()->intended($this->dashboardForRole($role));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Tentukan path dashboard berdasarkan role user.
     */
    protected function dashboardForRole(string $role): string
    {
        return match ($role) {
            'admin', 'panitia' => '/dashboard/panitia',
            'player'           => '/dashboard/player',
            'pic_kontingen', 'pic' => '/dashboard/pic-kontingen',
            default            => '/',
        };
    }
}
