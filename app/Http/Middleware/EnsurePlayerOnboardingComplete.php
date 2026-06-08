<?php

namespace App\Http\Middleware;

use App\Services\OnboardingStatusService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlayerOnboardingComplete
{
    public function __construct(private OnboardingStatusService $onboarding)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$this->onboarding->appliesTo($user)) {
            return $next($request);
        }

        if (!$this->onboarding->isAccountComplete($user)) {
            return redirect()
                ->route($this->onboarding->accountSetupRouteName($user))
                ->with('warning', 'Lengkapi data akun dan enroll face terlebih dahulu sebelum masuk dashboard.');
        }

        if (!$this->onboarding->hasSelfAssessment($user) && !$request->routeIs('dashboard.*.self-assessment.index')) {
            return redirect()
                ->route($this->onboarding->selfAssessmentRouteName($user))
                ->with('warning', 'Isi self-assessment terlebih dahulu untuk melanjutkan ke dashboard.');
        }

        return $next($request);
    }
}
