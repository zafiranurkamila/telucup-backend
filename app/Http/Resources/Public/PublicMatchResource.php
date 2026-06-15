<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicMatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sport' => $this->sport ? [
                'id' => $this->sport->id,
                'name' => $this->sport->name,
            ] : null,
            'sport_category' => $this->sportCategory ? [
                'id' => $this->sportCategory->id,
                'name' => $this->sportCategory->name,
            ] : null,
            'round' => $this->round,
            'round_name' => $this->round_name,
            'match_number' => $this->match_number,
            'is_third_place_match' => (bool) $this->is_third_place_match,
            'status' => $this->status,
            'match_date' => $this->match_date?->format('Y-m-d'),
            'match_time' => $this->match_time,
            'location' => $this->location,
            'score_a' => $this->score_a,
            'score_b' => $this->score_b,
            'team_a' => $this->team($this->registrationA, $this->registration_a_id),
            'team_b' => $this->team($this->registrationB, $this->registration_b_id),
            'winner' => $this->winner_registration_id
                ? $this->team($this->winner, $this->winner_registration_id)
                : null,
            'next_match_id' => $this->next_match_id,
            'next_match_slot' => $this->next_match_slot,
        ];
    }

    private function team($registration, ?int $registrationId): ?array
    {
        if (!$registration) {
            return null;
        }

        return [
            'registration_id' => $registrationId,
            'contingent_id' => $registration->contingent?->id,
            'contingent_name' => $registration->contingent?->name,
        ];
    }
}
