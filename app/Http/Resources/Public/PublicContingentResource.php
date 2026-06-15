<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicContingentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image_url' => $this->image_url,
            'team_count' => $this->whenCounted('registrations'),
            'sports' => $this->whenLoaded('registrations', function () {
                return $this->registrations
                    ->filter(fn ($registration) => $registration->sport)
                    ->map(fn ($registration) => [
                        'id' => $registration->sport->id,
                        'name' => $registration->sport->name,
                        'category' => $registration->sportCategory ? [
                            'id' => $registration->sportCategory->id,
                            'name' => $registration->sportCategory->name,
                        ] : null,
                    ])
                    ->unique(fn ($sport) => $sport['id'] . ':' . ($sport['category']['id'] ?? 'none'))
                    ->values();
            }, []),
        ];
    }
}
