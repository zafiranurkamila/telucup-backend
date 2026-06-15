<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicTeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'registration_id' => $this->id,
            'team_name' => $this->contingent?->name,
            'status' => $this->status,
            'contingent' => $this->contingent ? [
                'id' => $this->contingent->id,
                'name' => $this->contingent->name,
            ] : null,
            'sport' => $this->sport ? [
                'id' => $this->sport->id,
                'name' => $this->sport->name,
            ] : null,
            'sport_category' => $this->sportCategory ? [
                'id' => $this->sportCategory->id,
                'name' => $this->sportCategory->name,
            ] : null,
        ];
    }
}
