<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\OnboardingStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private OnboardingStatusService $onboarding)
    {
    }

    /**
     * Display the login view.
     * Jika user sudah login, redirect ke dashboard sesuai role.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to($this->onboarding->nextPath(Auth::user()));
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

        return redirect()->intended($this->onboarding->nextPath(Auth::user()));
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

}
