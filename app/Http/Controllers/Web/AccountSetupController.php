<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\OnboardingStatusService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountSetupController extends Controller
{
    public function __construct(private OnboardingStatusService $onboarding)
    {
    }

    public function show(Request $request): View
    {
        $user = $request->user()->load('player');
        $isPlayer = $user->role === 'player';
        $nextUrl = $this->onboarding->hasSelfAssessment($user)
            ? $this->onboarding->dashboardPath($user)
            : route($this->onboarding->selfAssessmentRouteName($user));

        return view('dashboard.shared.account-setup', [
            'user' => $user,
            'player' => $user->player,
            'isPlayer' => $isPlayer,
            'roleLabel' => $isPlayer ? 'Player' : 'PIC Kontingen',
            'nextUrl' => $nextUrl,
        ]);
    }
}
