<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PublicContingentResource;
use App\Http\Resources\Public\PublicMatchResource;
use App\Http\Resources\Public\PublicSportResource;
use App\Http\Resources\Public\PublicTeamResource;
use App\Models\Contingent;
use App\Models\Game;
use App\Models\Registration;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicTournamentController extends Controller
{
    private const CACHE_SECONDS = 15;

    public function sports(Request $request)
    {
        return $this->cachedResponse($request, 'sports', function () use ($request) {
            $sports = Sport::with('categories')->orderBy('name')->get();

            return $this->success(
                $this->resolveCollection(PublicSportResource::class, $sports, $request),
                ['total' => $sports->count(), 'filter' => []]
            );
        });
    }

    public function contingents(Request $request)
    {
        $validated = $request->validate([
            'sport_id' => 'nullable|integer|exists:sports,id',
        ]);

        return $this->cachedResponse($request, 'contingents', function () use ($request, $validated) {
            $query = Contingent::query()
                ->withCount(['registrations' => fn ($query) => $this->verifiedRegistrations($query)])
                ->with(['registrations' => function ($query) {
                    $this->verifiedRegistrations($query)
                        ->with(['sport', 'sportCategory']);
                }]);

            if (!empty($validated['sport_id'])) {
                $query->whereHas('registrations', function ($query) use ($validated) {
                    $this->verifiedRegistrations($query)
                        ->where('sport_id', $validated['sport_id']);
                });
            }

            $contingents = $query->orderBy('name')->get();

            return $this->success(
                $this->resolveCollection(PublicContingentResource::class, $contingents, $request),
                ['total' => $contingents->count(), 'filter' => $validated]
            );
        });
    }

    public function teams(Request $request)
    {
        $validated = $request->validate([
            'sport_id' => 'nullable|integer|exists:sports,id',
            'contingent_id' => 'nullable|integer|exists:contingents,id',
        ]);

        return $this->cachedResponse($request, 'teams', function () use ($request, $validated) {
            $query = Registration::with(['contingent', 'sport', 'sportCategory'])
                ->where('status', 'verified');

            if (!empty($validated['sport_id'])) {
                $query->where('sport_id', $validated['sport_id']);
            }

            if (!empty($validated['contingent_id'])) {
                $query->where('contingent_id', $validated['contingent_id']);
            }

            $teams = $query
                ->join('contingents', 'registrations.contingent_id', '=', 'contingents.id')
                ->orderBy('contingents.name')
                ->select('registrations.*')
                ->get();

            return $this->success(
                $this->resolveCollection(PublicTeamResource::class, $teams, $request),
                ['total' => $teams->count(), 'filter' => $validated]
            );
        });
    }

    public function schedule(Request $request)
    {
        $validated = $request->validate([
            'sport_id' => 'nullable|integer|exists:sports,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        return $this->cachedResponse($request, 'matches.schedule', function () use ($request, $validated) {
            $limit = (int) ($validated['limit'] ?? 20);
            $query = $this->matchQuery()
                ->whereIn('status', ['scheduled', 'live']);

            if (!empty($validated['sport_id'])) {
                $query->where('sport_id', $validated['sport_id']);
            }

            if (!empty($validated['date_from'])) {
                $query->whereDate('match_date', '>=', $validated['date_from']);
            } else {
                $today = now()->toDateString();
                $query->where(function ($query) use ($today) {
                    $query->whereNull('match_date')
                        ->orWhereDate('match_date', '>=', $today);
                });
            }

            if (!empty($validated['date_to'])) {
                $query->whereDate('match_date', '<=', $validated['date_to']);
            }

            $matches = $this->orderMatches($query)->limit($limit)->get();

            return $this->success(
                $this->resolveCollection(PublicMatchResource::class, $matches, $request),
                ['total' => $matches->count(), 'filter' => $validated + ['limit' => $limit]]
            );
        });
    }

    public function results(Request $request)
    {
        $validated = $request->validate([
            'sport_id' => 'nullable|integer|exists:sports,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        return $this->cachedResponse($request, 'matches.results', function () use ($request, $validated) {
            $limit = (int) ($validated['limit'] ?? 20);
            $query = $this->matchQuery()->where('status', 'finished');

            if (!empty($validated['sport_id'])) {
                $query->where('sport_id', $validated['sport_id']);
            }

            if (!empty($validated['date_from'])) {
                $query->whereDate('match_date', '>=', $validated['date_from']);
            }

            if (!empty($validated['date_to'])) {
                $query->whereDate('match_date', '<=', $validated['date_to']);
            }

            $matches = $query
                ->orderByRaw('match_date IS NULL, match_date DESC')
                ->orderByRaw('match_time IS NULL, match_time DESC')
                ->orderBy('sport_id')
                ->orderBy('round')
                ->orderBy('match_number')
                ->limit($limit)
                ->get();

            return $this->success(
                $this->resolveCollection(PublicMatchResource::class, $matches, $request),
                ['total' => $matches->count(), 'filter' => $validated + ['limit' => $limit]]
            );
        });
    }

    public function match(Request $request, int $id)
    {
        return $this->cachedResponse($request, "matches.$id", function () use ($request, $id) {
            $match = $this->matchQuery()->findOrFail($id);

            return $this->success(
                (new PublicMatchResource($match))->resolve($request),
                ['total' => 1, 'filter' => ['id' => $id]]
            );
        });
    }

    public function brackets(Request $request)
    {
        $validated = $request->validate([
            'sport_id' => 'nullable|integer|exists:sports,id',
        ]);

        return $this->cachedResponse($request, 'brackets', function () use ($request, $validated) {
            $query = $this->matchQuery();

            if (!empty($validated['sport_id'])) {
                $query->where('sport_id', $validated['sport_id']);
            }

            $matches = $query
                ->orderBy('sport_id')
                ->orderBy('sport_category_id')
                ->orderBy('round')
                ->orderBy('match_number')
                ->get();

            $groups = $matches
                ->groupBy(fn (Game $game) => $game->sport_id . ':' . ($game->sport_category_id ?? 'none'))
                ->map(function ($group) use ($request) {
                    $first = $group->first();
                    $mainMatches = $group->where('is_third_place_match', false);
                    $thirdPlace = $group->firstWhere('is_third_place_match', true);

                    return [
                        'sport' => $first->sport ? [
                            'id' => $first->sport->id,
                            'name' => $first->sport->name,
                        ] : null,
                        'sport_category' => $first->sportCategory ? [
                            'id' => $first->sportCategory->id,
                            'name' => $first->sportCategory->name,
                        ] : null,
                        'rounds' => $mainMatches
                            ->groupBy('round')
                            ->map(fn ($roundMatches, $round) => [
                                'round' => (int) $round,
                                'name' => $roundMatches->first()->round_name,
                                'matches' => $this->resolveCollection(PublicMatchResource::class, $roundMatches->values(), $request),
                            ])
                            ->values(),
                        'third_place_match' => $thirdPlace
                            ? (new PublicMatchResource($thirdPlace))->resolve($request)
                            : null,
                    ];
                })
                ->values();

            return $this->success($groups, [
                'total' => $groups->count(),
                'filter' => $validated,
            ]);
        });
    }

    public function venues(Request $request)
    {
        return $this->cachedResponse($request, 'venues', function () {
            $games = Game::with(['sport', 'sportCategory'])
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->where('status', '!=', 'bye')
                ->orderBy('location')
                ->get(['id', 'sport_id', 'sport_category_id', 'location']);

            $venues = $games
                ->groupBy('location')
                ->map(fn ($venueGames, $location) => [
                    'name' => $location,
                    'sports' => $venueGames
                        ->filter(fn ($game) => $game->sport)
                        ->map(fn ($game) => [
                            'id' => $game->sport->id,
                            'name' => $game->sport->name,
                            'category' => $game->sportCategory ? [
                                'id' => $game->sportCategory->id,
                                'name' => $game->sportCategory->name,
                            ] : null,
                        ])
                        ->unique(fn ($sport) => $sport['id'] . ':' . ($sport['category']['id'] ?? 'none'))
                        ->values(),
                    'match_count' => $venueGames->count(),
                ])
                ->values();

            return $this->success($venues, [
                'total' => $venues->count(),
                'filter' => [],
            ]);
        });
    }

    private function verifiedRegistrations($query)
    {
        return $query->where('status', 'verified');
    }

    private function matchQuery()
    {
        return Game::with([
            'sport',
            'sportCategory',
            'registrationA.contingent',
            'registrationB.contingent',
            'winner.contingent',
        ])->where('status', '!=', 'bye');
    }

    private function orderMatches($query)
    {
        return $query
            ->orderByRaw('match_date IS NULL, match_date')
            ->orderByRaw('match_time IS NULL, match_time')
            ->orderBy('sport_id')
            ->orderBy('round')
            ->orderBy('match_number');
    }

    private function cachedResponse(Request $request, string $name, callable $callback)
    {
        $cacheKey = 'public.' . $name . '.' . md5($request->path() . '?' . json_encode($request->query()));

        return response()->json(Cache::remember($cacheKey, self::CACHE_SECONDS, $callback));
    }

    private function success($data, array $meta): array
    {
        return [
            'status' => 'success',
            'data' => $data,
            'meta' => $meta,
        ];
    }

    private function resolveCollection(string $resourceClass, $items, Request $request): array
    {
        return collect($items)
            ->map(fn ($item) => (new $resourceClass($item))->resolve($request))
            ->values()
            ->all();
    }
}
