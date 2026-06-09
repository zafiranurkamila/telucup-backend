<?php

namespace App\Services;

use App\Models\SelfAssessment;
use App\Models\User;

class OnboardingStatusService
{
    public function appliesTo(User $user): bool
    {
        return in_array($user->role, ['player', 'pic_kontingen', 'pic'], true);
    }

    public function isAccountComplete(User $user): bool
    {
        $player = $user->player;

        if (!$player) {
            return false;
        }

        return filled($player->nim_nip)
            && filled($player->employee_status)
            && filled($player->work_location)
            && filled($player->photo_path)
            && $player->faceEmbeddings()->exists();
    }

    public function hasSelfAssessment(User $user): bool
    {
        $player = $user->player;

        if (!$player) {
            return false;
        }

        return SelfAssessment::where('player_id', $player->id)->exists();
    }

    public function accountSetupRouteName(User $user): string
    {
        return $user->role === 'player'
            ? 'dashboard.player.account-setup'
            : 'dashboard.pic.account-setup';
    }

    public function selfAssessmentRouteName(User $user): string
    {
        return $user->role === 'player'
            ? 'dashboard.player.self-assessment.index'
            : 'dashboard.pic.self-assessment.index';
    }

    public function dashboardPath(User $user): string
    {
        return match ($user->role) {
            'admin', 'panitia' => '/dashboard/panitia',
            'player' => '/dashboard/player',
            'pic_kontingen', 'pic' => '/dashboard/pic-kontingen',
            default => '/',
        };
    }

    public function nextPath(User $user): string
    {
        if (!$this->appliesTo($user)) {
            return $this->dashboardPath($user);
        }

        if (!$this->isAccountComplete($user)) {
            return route($this->accountSetupRouteName($user), absolute: false);
        }

        if (!$this->hasSelfAssessment($user)) {
            return route($this->selfAssessmentRouteName($user), absolute: false);
        }

        return $this->dashboardPath($user);
    }
}
