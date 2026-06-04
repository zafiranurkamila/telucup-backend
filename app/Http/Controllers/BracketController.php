<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use App\Models\Game;
use App\Models\GamePlayerCheckin;
use App\Models\Registration;
use App\Models\Sport;
use App\Models\SportCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BracketController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    //  SEMUA PERTANDINGAN KONTINGEN (PIC KONTINGEN)
    // ──────────────────────────────────────────────────────────────

    #[OA\Get(
        path: "/api/my-matches",
        operationId: "myMatches",
        tags: ["Bracket"],
        summary: "Semua pertandingan kontingen saya",
        description: "PIC kontingen mengambil **seluruh** pertandingan kontingennya lintas semua cabang olahraga.\n\n**Urutan hasil:** diurutkan berdasarkan `sport_id` → `round` → `match_number` → `match_date` → `match_time`. Pertandingan dengan tanggal belum dijadwalkan muncul di paling akhir.\n\nPertandingan berstatus `bye` selalu dikecualikan.\n\nSemua query param bersifat opsional dan dapat dikombinasikan.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "sport_id",
        in: "query",
        required: false,
        description: "Filter hanya pertandingan pada cabang olahraga tertentu",
        schema: new OA\Schema(type: "integer", example: 1)
    )]
    #[OA\Parameter(
        name: "sport_category_id",
        in: "query",
        required: false,
        description: "Filter berdasarkan sub-kategori (Putra / Putri / Reguler / dll)",
        schema: new OA\Schema(type: "integer", example: 2)
    )]
    #[OA\Parameter(
        name: "status",
        in: "query",
        required: false,
        description: "Filter berdasarkan status pertandingan",
        schema: new OA\Schema(type: "string", enum: ["scheduled", "live", "finished"])
    )]
    #[OA\Parameter(
        name: "date",
        in: "query",
        required: false,
        description: "Filter hanya pertandingan pada tanggal tertentu (format YYYY-MM-DD)",
        schema: new OA\Schema(type: "string", format: "date", example: "2026-06-15")
    )]
    #[OA\Response(
        response: 200,
        description: "Daftar seluruh pertandingan kontingen berhasil diambil",
        content: new OA\JsonContent(ref: "#/components/schemas/MyMatchesResponse")
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated — token tidak diberikan atau sudah kedaluwarsa",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    #[OA\Response(
        response: 403,
        description: "Forbidden — user login belum ditugaskan sebagai PIC kontingen manapun",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    #[OA\Response(
        response: 422,
        description: "Parameter filter tidak valid (sport_id / sport_category_id tidak ditemukan, format date salah, dll)",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function myMatches(Request $request)
    {
        $contingent = $request->user()->managedContingent;

        if (!$contingent) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda belum ditugaskan sebagai PIC kontingen manapun.',
            ], 403);
        }

        $validated = $request->validate([
            'sport_id'          => 'nullable|integer|exists:sports,id',
            'sport_category_id' => 'nullable|integer|exists:sport_categories,id',
            'status'            => 'nullable|in:scheduled,live,finished',
            'date'              => 'nullable|date',
        ]);

        $registrationIds = Registration::where('contingent_id', $contingent->id)->pluck('id');

        $query = Game::with([
            'sport',
            'sportCategory',
            'registrationA.contingent',
            'registrationA.players',
            'registrationB.contingent',
            'registrationB.players',
            'winner.contingent',
        ])
        ->where('status', '!=', 'bye')
        ->where(function ($q) use ($registrationIds) {
            $q->whereIn('registration_a_id', $registrationIds)
              ->orWhereIn('registration_b_id', $registrationIds);
        });

        if (!empty($validated['sport_id'])) {
            $query->where('sport_id', $validated['sport_id']);
        }
        if (!empty($validated['sport_category_id'])) {
            $query->where('sport_category_id', $validated['sport_category_id']);
        }
        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }
        if (!empty($validated['date'])) {
            $query->whereDate('match_date', $validated['date']);
        }

        $matches = $query
            ->orderBy('sport_id')
            ->orderBy('round')
            ->orderBy('match_number')
            ->orderByRaw('match_date IS NULL, match_date')
            ->orderByRaw('match_time IS NULL, match_time')
            ->get();

        $data = $matches->map(function (Game $game) use ($registrationIds) {
            $mySlot = $registrationIds->contains($game->registration_a_id) ? 'a' : 'b';
            return array_merge($this->formatMatch($game), ['my_slot' => $mySlot]);
        })->values();

        return response()->json([
            'status'     => 'success',
            'contingent' => [
                'id'   => $contingent->id,
                'name' => $contingent->name,
            ],
            'filters' => array_filter($validated, fn ($v) => !is_null($v)),
            'total'   => $data->count(),
            'data'    => $data,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  JADWAL HARI INI (PIC KONTINGEN)
    // ──────────────────────────────────────────────────────────────

    #[OA\Get(
        path: "/api/my-matches/today",
        operationId: "myTodayMatches",
        tags: ["Bracket"],
        summary: "Pertandingan kontingen saya hari ini",
        description: "PIC kontingen melihat semua pertandingan kontingennya yang dijadwalkan pada hari ini, diurutkan berdasarkan waktu mulai. Hanya pertandingan dengan `match_date` = hari ini yang dikembalikan. Pertandingan berstatus `bye` dikecualikan.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Daftar pertandingan hari ini berhasil diambil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "date",   type: "string", format: "date", example: "2026-05-29"),
                new OA\Property(property: "total",  type: "integer", example: 2),
                new OA\Property(property: "data",   type: "array",
                    items: new OA\Items(ref: "#/components/schemas/TodayMatchItem")),
            ]
        )
    )]
    #[OA\Response(response: 403, description: "Belum ditugaskan sebagai PIC", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function myTodayMatches(Request $request)
    {
        $contingent = $request->user()->managedContingent;

        if (!$contingent) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum ditugaskan sebagai PIC kontingen manapun.'], 403);
        }

        $registrationIds = Registration::where('contingent_id', $contingent->id)->pluck('id');

        $today   = Carbon::today()->toDateString();
        $matches = Game::with([
            'sport',
            'sportCategory',
            'registrationA.contingent',
            'registrationA.players',
            'registrationB.contingent',
            'registrationB.players',
            'winner.contingent',
        ])
            ->whereDate('match_date', $today)
            ->where('status', '!=', 'bye')
            ->where(function ($q) use ($registrationIds) {
                $q->whereIn('registration_a_id', $registrationIds)
                  ->orWhereIn('registration_b_id', $registrationIds);
            })
            ->orderBy('match_time')
            ->get();

        $data = $matches->map(function (Game $game) use ($registrationIds) {
            $mySlot = $registrationIds->contains($game->registration_a_id) ? 'a' : 'b';
            return array_merge($this->formatMatch($game), ['my_slot' => $mySlot]);
        })->values();

        return response()->json([
            'status' => 'success',
            'date'   => $today,
            'total'  => $data->count(),
            'data'   => $data,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  SEMUA PERTANDINGAN — PUBLIK (PENONTON)
    // ──────────────────────────────────────────────────────────────

    #[OA\Get(
        path: "/api/matches",
        operationId: "listMatches",
        tags: ["Bracket"],
        summary: "Daftar semua pertandingan (publik)",
        description: "Mengembalikan daftar semua pertandingan yang dapat diakses tanpa login. Ditujukan untuk penonton atau pengunjung yang ingin memantau jadwal dan hasil pertandingan.\n\nPertandingan `bye` dikecualikan secara default. Hasil diurutkan berdasarkan tanggal/waktu (terjadwal terlebih dahulu), lalu berdasarkan cabang olahraga dan ronde.",
    )]
    #[OA\Parameter(
        name: "sport_id",
        in: "query",
        required: false,
        description: "Filter berdasarkan cabang olahraga",
        schema: new OA\Schema(type: "integer", example: 1)
    )]
    #[OA\Parameter(
        name: "sport_category_id",
        in: "query",
        required: false,
        description: "Filter berdasarkan sub-kategori (Putra / Putri / Reguler / dll)",
        schema: new OA\Schema(type: "integer", example: 2)
    )]
    #[OA\Parameter(
        name: "status",
        in: "query",
        required: false,
        description: "Filter berdasarkan status pertandingan",
        schema: new OA\Schema(type: "string", enum: ["scheduled", "live", "finished"])
    )]
    #[OA\Parameter(
        name: "date",
        in: "query",
        required: false,
        description: "Filter pertandingan pada tanggal tertentu (YYYY-MM-DD)",
        schema: new OA\Schema(type: "string", format: "date", example: "2026-06-15")
    )]
    #[OA\Parameter(
        name: "contingent_id",
        in: "query",
        required: false,
        description: "Filter pertandingan yang melibatkan kontingen tertentu — berguna bagi pendukung tim",
        schema: new OA\Schema(type: "integer", example: 3)
    )]
    #[OA\Parameter(
        name: "round",
        in: "query",
        required: false,
        description: "Filter berdasarkan nomor ronde",
        schema: new OA\Schema(type: "integer", example: 2)
    )]
    #[OA\Parameter(
        name: "per_page",
        in: "query",
        required: false,
        description: "Jumlah item per halaman (default: 20, maks: 100)",
        schema: new OA\Schema(type: "integer", example: 20)
    )]
    #[OA\Parameter(
        name: "page",
        in: "query",
        required: false,
        description: "Nomor halaman",
        schema: new OA\Schema(type: "integer", example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: "Daftar pertandingan berhasil diambil",
        content: new OA\JsonContent(ref: "#/components/schemas/PublicMatchListResponse")
    )]
    #[OA\Response(
        response: 422,
        description: "Parameter filter tidak valid",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function allMatches(Request $request)
    {
        $validated = $request->validate([
            'sport_id'          => 'nullable|integer|exists:sports,id',
            'sport_category_id' => 'nullable|integer|exists:sport_categories,id',
            'status'            => 'nullable|in:scheduled,live,finished',
            'date'              => 'nullable|date',
            'contingent_id'     => 'nullable|integer|exists:contingents,id',
            'round'             => 'nullable|integer|min:1',
            'per_page'          => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = Game::with([
            'sport',
            'sportCategory',
            'registrationA.contingent',
            'registrationB.contingent',
            'winner.contingent',
        ])
        ->where('status', '!=', 'bye');

        if (!empty($validated['sport_id'])) {
            $query->where('sport_id', $validated['sport_id']);
        }
        if (!empty($validated['sport_category_id'])) {
            $query->where('sport_category_id', $validated['sport_category_id']);
        }
        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }
        if (!empty($validated['date'])) {
            $query->whereDate('match_date', $validated['date']);
        }
        if (!empty($validated['round'])) {
            $query->where('round', $validated['round']);
        }
        if (!empty($validated['contingent_id'])) {
            $contingentId = $validated['contingent_id'];
            $regIds = Registration::where('contingent_id', $contingentId)->pluck('id');
            $query->where(function ($q) use ($regIds) {
                $q->whereIn('registration_a_id', $regIds)
                  ->orWhereIn('registration_b_id', $regIds);
            });
        }

        $query->orderByRaw('match_date IS NULL, match_date')
              ->orderByRaw('match_time IS NULL, match_time')
              ->orderBy('sport_id')
              ->orderBy('round')
              ->orderBy('match_number');

        $paginated = $query->paginate($perPage);

        $data = collect($paginated->items())->map(
            fn (Game $game) => $this->formatPublicMatch($game)
        )->values();

        return response()->json([
            'status' => 'success',
            'meta'   => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'filters'      => array_filter($validated, fn ($v) => !is_null($v) && $v !== 'per_page'),
            ],
            'data'   => $data,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  GENERATE
    // ──────────────────────────────────────────────────────────────

    #[OA\Post(
        path: "/api/bracket/generate",
        operationId: "generateBracket",
        tags: ["Bracket"],
        summary: "Generate bagan gugur otomatis",
        description: "Membuat semua slot pertandingan bagan gugur dari registrasi tim yang sudah diverifikasi. Bye disisipkan merata agar pembagian adil. Jika bagan sudah ada, hapus dulu via DELETE /bracket/reset.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["sport_id"],
            properties: [
                new OA\Property(property: "sport_id", type: "integer", example: 1),
                new OA\Property(property: "sport_category_id", type: "integer", example: 2, description: "Wajib jika sport memiliki sub-kategori"),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Bagan berhasil digenerate",
        content: new OA\JsonContent(ref: "#/components/schemas/BracketGenerateResponse")
    )]
    #[OA\Response(
        response: 422,
        description: "Jumlah tim tidak valid atau bagan sudah ada",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'sport_id'          => 'required|integer|exists:sports,id',
            'sport_category_id' => 'nullable|integer|exists:sport_categories,id',
        ]);

        $sportId    = $validated['sport_id'];
        $categoryId = $validated['sport_category_id'] ?? null;

        // Cek apakah bagan sudah ada
        $existing = Game::where('sport_id', $sportId)
            ->where('sport_category_id', $categoryId)
            ->exists();

        if ($existing) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Bagan untuk cabang olahraga ini sudah ada. Hapus dulu via DELETE /api/bracket/reset.',
            ], 422);
        }

        // Ambil tim yang sudah diverifikasi
        $teams = Registration::with(['contingent'])
            ->where('sport_id', $sportId)
            ->where('sport_category_id', $categoryId)
            ->where('status', 'verified')
            ->get()
            ->shuffle()
            ->values();

        $n = $teams->count();

        if ($n < 2) {
            return response()->json([
                'status'  => 'error',
                'message' => "Minimal 2 tim terverifikasi diperlukan (ditemukan: {$n}).",
            ], 422);
        }

        DB::beginTransaction();
        try {
            $games = $this->buildBracket($teams->toArray(), $sportId, $categoryId);
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Bagan berhasil digenerate.',
                'data'    => $this->bracketView($sportId, $categoryId),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal generate bagan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  VIEW BRACKET
    // ──────────────────────────────────────────────────────────────

    #[OA\Get(
        path: "/api/bracket",
        operationId: "viewBracket",
        tags: ["Bracket"],
        summary: "Tampilkan bagan pertandingan lengkap",
        description: "Mengembalikan semua ronde dan pertandingan terorganisir untuk ditampilkan sebagai bagan gugur. Termasuk informasi tim, pemain, kontingen, skor, jadwal, dan lokasi.",
    )]
    #[OA\Parameter(name: "sport_id", in: "query", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "sport_category_id", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Bagan berhasil diambil",
        content: new OA\JsonContent(ref: "#/components/schemas/BracketViewResponse")
    )]
    #[OA\Response(
        response: 404,
        description: "Bagan belum digenerate",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function bracket(Request $request)
    {
        $request->validate([
            'sport_id'          => 'required|integer|exists:sports,id',
            'sport_category_id' => 'nullable|integer|exists:sport_categories,id',
        ]);

        $view = $this->bracketView(
            (int) $request->sport_id,
            $request->sport_category_id ? (int) $request->sport_category_id : null
        );

        if (empty($view['rounds'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Bagan belum digenerate untuk cabang olahraga ini.',
            ], 404);
        }

        return response()->json(['status' => 'success', 'data' => $view]);
    }

    // ──────────────────────────────────────────────────────────────
    //  MATCH DETAIL
    // ──────────────────────────────────────────────────────────────

    #[OA\Get(
        path: "/api/matches/{id}",
        operationId: "getMatchDetail",
        tags: ["Bracket"],
        summary: "Detail pertandingan lengkap",
        description: "Mengembalikan informasi pertandingan secara lengkap termasuk kedua tim, semua pemain yang bertanding, kontingen, skor, lokasi, wasit, dan pertandingan berikutnya.",
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Detail pertandingan lengkap beserta informasi cabang olahraga",
        content: new OA\JsonContent(ref: "#/components/schemas/MatchDataResponse")
    )]
    #[OA\Response(
        response: 404,
        description: "Pertandingan tidak ditemukan",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function matchDetail($id)
    {
        $game = Game::with([
            'sport',
            'sportCategory',
            'registrationA.contingent',
            'registrationA.players',
            'registrationB.contingent',
            'registrationB.players',
            'winner.contingent',
            'nextMatch',
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $this->formatMatch($game),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  UPDATE SCORE & TENTUKAN PEMENANG
    // ──────────────────────────────────────────────────────────────

    #[OA\Patch(
        path: "/api/matches/{id}/score",
        operationId: "updateMatchScore",
        tags: ["Bracket"],
        summary: "Update skor dan tentukan pemenang",
        description: "Memperbarui skor pertandingan, menentukan pemenang, dan secara otomatis menempatkan pemenang ke slot pertandingan berikutnya. Jika skor seri, tentukan pemenang secara eksplisit via winner_registration_id.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["score_a", "score_b"],
            properties: [
                new OA\Property(property: "score_a", type: "integer", example: 3),
                new OA\Property(property: "score_b", type: "integer", example: 1),
                new OA\Property(property: "winner_registration_id", type: "integer", example: 5,
                    description: "Wajib jika skor seri. Opsional jika skor tidak seri (ditentukan otomatis)."),
                new OA\Property(property: "notes", type: "string", example: "Tim A mendominasi babak kedua."),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Skor diperbarui dan pemenang dilanjutkan ke babak berikutnya",
        content: new OA\JsonContent(ref: "#/components/schemas/MatchActionResponse")
    )]
    #[OA\Response(
        response: 422,
        description: "Skor seri tanpa pemenang eksplisit atau pertandingan bye",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function updateScore(Request $request, $id)
    {
        $game = Game::with(['registrationA', 'registrationB'])->findOrFail($id);

        if ($game->status === 'bye') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pertandingan bye tidak dapat diupdate skor.',
            ], 422);
        }

        if ($game->status === 'scheduled') {
            if (!$game->registration_a_id || !$game->registration_b_id) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Pertandingan belum memiliki dua tim yang lengkap.',
                ], 422);
            }

            $totalPlayers = $game->registrationA->players()->count() + $game->registrationB->players()->count();
            if ($totalPlayers === 0) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak ada pemain terdaftar di kedua tim.',
                ], 422);
            }

            $checkedInCount = $game->playerCheckins()->count();
            if ($checkedInCount < $totalPlayers) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Tidak bisa menyimpan skor. Baru $checkedInCount dari $totalPlayers pemain yang check-in.",
                ], 422);
            }
        }

        $validated = $request->validate([
            'score_a'               => 'required|integer|min:0',
            'score_b'               => 'required|integer|min:0',
            'winner_registration_id'=> 'nullable|integer|exists:registrations,id',
            'notes'                 => 'nullable|string|max:1000',
        ]);

        $scoreA = $validated['score_a'];
        $scoreB = $validated['score_b'];

        // Tentukan pemenang
        if ($scoreA !== $scoreB) {
            $winnerId = $scoreA > $scoreB
                ? $game->registration_a_id
                : $game->registration_b_id;
        } else {
            // Seri — butuh pemenang eksplisit
            if (empty($validated['winner_registration_id'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Skor seri. Tentukan pemenang via winner_registration_id.',
                ], 422);
            }
            $winnerId = $validated['winner_registration_id'];
        }

        // Validasi pemenang harus salah satu peserta
        if (!in_array($winnerId, [$game->registration_a_id, $game->registration_b_id])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Pemenang harus salah satu dari kedua tim yang bertanding.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $game->update([
                'score_a'               => $scoreA,
                'score_b'               => $scoreB,
                'winner_registration_id'=> $winnerId,
                'status'                => 'finished',
                'notes'                 => $validated['notes'] ?? $game->notes,
            ]);

            // Auto-advance pemenang ke pertandingan berikutnya
            if ($game->next_match_id && $game->next_match_slot) {
                $nextGame = Game::findOrFail($game->next_match_id);
                $slotColumn = 'registration_' . $game->next_match_slot . '_id';
                $nextGame->update([$slotColumn => $winnerId]);
            }

            // Auto-advance kalah ke pertandingan perebutan juara 3
            $loserId = ($winnerId === $game->registration_a_id)
                ? $game->registration_b_id
                : $game->registration_a_id;

            if ($game->loser_next_match_id && $game->loser_next_match_slot && $loserId) {
                $thirdPlaceGame = Game::findOrFail($game->loser_next_match_id);
                $loserSlot = 'registration_' . $game->loser_next_match_slot . '_id';
                $thirdPlaceGame->update([$loserSlot => $loserId]);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Skor diperbarui dan pemenang dilanjutkan ke babak berikutnya.',
                'data'    => $this->formatMatch($game->fresh()),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal update skor: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  UPDATE JADWAL & LOKASI
    // ──────────────────────────────────────────────────────────────

    #[OA\Patch(
        path: "/api/matches/{id}/schedule",
        operationId: "updateMatchSchedule",
        tags: ["Bracket"],
        summary: "Atur jadwal dan lokasi pertandingan",
        description: "Admin mengatur tanggal, waktu, lokasi, dan wasit untuk sebuah pertandingan.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "match_date", type: "string", format: "date", example: "2026-06-15"),
                new OA\Property(property: "match_time", type: "string", example: "09:00"),
                new OA\Property(property: "location", type: "string", example: "Gedung Sport Center Lt. 2"),
                new OA\Property(property: "referee_name", type: "string", example: "Budi Santoso"),
                new OA\Property(property: "notes", type: "string", example: "Pakai seragam biru"),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Jadwal berhasil diperbarui — response menyertakan state pertandingan terbaru beserta cabang olahraga",
        content: new OA\JsonContent(ref: "#/components/schemas/MatchActionResponse")
    )]
    public function updateSchedule(Request $request, $id)
    {
        $game = Game::findOrFail($id);

        $validated = $request->validate([
            'match_date'   => 'nullable|date',
            'match_time'   => 'nullable|string|max:10',
            'location'     => 'nullable|string|max:255',
            'referee_name' => 'nullable|string|max:255',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $game->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Jadwal pertandingan berhasil diperbarui.',
            'data'    => $this->formatMatch($game->fresh()),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  ATUR TIM SECARA MANUAL
    // ──────────────────────────────────────────────────────────────

    #[OA\Patch(
        path: "/api/matches/{id}/teams",
        operationId: "setMatchTeams",
        tags: ["Bracket"],
        summary: "Atur tim secara manual",
        description: "Admin menempatkan atau mengganti tim di slot A dan/atau B sebuah pertandingan secara manual. Hanya dapat dilakukan pada pertandingan berstatus 'scheduled'.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "registration_a_id", type: "integer", example: 3, description: "ID registrasi tim untuk slot A (null untuk kosongkan)"),
                new OA\Property(property: "registration_b_id", type: "integer", example: 7, description: "ID registrasi tim untuk slot B (null untuk kosongkan)"),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Tim berhasil diperbarui — response menyertakan state pertandingan terbaru beserta cabang olahraga",
        content: new OA\JsonContent(ref: "#/components/schemas/MatchActionResponse")
    )]
    #[OA\Response(
        response: 422,
        description: "Pertandingan sudah berlangsung atau selesai",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function setTeams(Request $request, $id)
    {
        $game = Game::findOrFail($id);

        if (in_array($game->status, ['live', 'finished'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tim tidak dapat diubah pada pertandingan yang sedang berlangsung atau sudah selesai.',
            ], 422);
        }

        $validated = $request->validate([
            'registration_a_id' => 'nullable|integer|exists:registrations,id',
            'registration_b_id' => 'nullable|integer|exists:registrations,id',
        ]);

        DB::beginTransaction();
        try {
            $newRegA = array_key_exists('registration_a_id', $validated) ? $validated['registration_a_id'] : $game->registration_a_id;
            $newRegB = array_key_exists('registration_b_id', $validated) ? $validated['registration_b_id'] : $game->registration_b_id;

            \Log::info("setTeams called for game {$game->id}. Old A: {$game->registration_a_id}, New A: {$newRegA}. Old B: {$game->registration_b_id}, New B: {$newRegB}");

            // Helper for swapping
            $handleSwap = function ($slotColumn, $newRegId) use ($game) {
                \Log::info("handleSwap for {$slotColumn} with newRegId: " . ($newRegId ?? 'null'));
                if ($newRegId && $newRegId != $game->{$slotColumn}) {
                    $otherGame = Game::where('sport_id', $game->sport_id)
                        ->where('sport_category_id', $game->sport_category_id)
                        ->where('id', '!=', $game->id)
                        ->where(function($q) use ($newRegId) {
                            $q->where('registration_a_id', $newRegId)
                              ->orWhere('registration_b_id', $newRegId);
                        })->first();

                    if ($otherGame) {
                        \Log::info("Found otherGame {$otherGame->id} which has {$newRegId}. Swapping to {$game->{$slotColumn}}");
                        if ($otherGame->registration_a_id == $newRegId) {
                            $otherGame->update(['registration_a_id' => $game->{$slotColumn}]);
                        } else {
                            $otherGame->update(['registration_b_id' => $game->{$slotColumn}]);
                        }
                    } else {
                        \Log::info("No otherGame found with {$newRegId}");
                    }
                } else {
                    \Log::info("Skip handleSwap because newRegId is null or unchanged.");
                }
            };

            $handleSwap('registration_a_id', $newRegA);
            $handleSwap('registration_b_id', $newRegB);

            $game->update([
                'registration_a_id' => $newRegA,
                'registration_b_id' => $newRegB,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Tim pertandingan berhasil diperbarui.',
                'data'    => $this->formatMatch($game->fresh()),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengubah tim: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  TUKAR POSISI DUA TIM DALAM SATU MATCH
    // ──────────────────────────────────────────────────────────────

    #[OA\Patch(
        path: "/api/matches/{id}/swap",
        operationId: "swapMatchTeams",
        tags: ["Bracket"],
        summary: "Tukar posisi tim A dan tim B",
        description: "Menukar posisi slot A dan slot B dari sebuah pertandingan. Berguna untuk penyesuaian posisi bagan secara manual.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Posisi tim berhasil ditukar — response menyertakan state pertandingan terbaru beserta cabang olahraga",
        content: new OA\JsonContent(ref: "#/components/schemas/MatchActionResponse")
    )]
    public function swapTeams($id)
    {
        $game = Game::findOrFail($id);

        if (in_array($game->status, ['live', 'finished', 'bye'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tim tidak dapat ditukar pada pertandingan yang sedang berlangsung, selesai, atau bye.',
            ], 422);
        }

        $game->update([
            'registration_a_id' => $game->registration_b_id,
            'registration_b_id' => $game->registration_a_id,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Posisi tim A dan B berhasil ditukar.',
            'data'    => $this->formatMatch($game->fresh()),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  SET STATUS PERTANDINGAN
    // ──────────────────────────────────────────────────────────────

    #[OA\Patch(
        path: "/api/matches/{id}/status",
        operationId: "setMatchStatus",
        tags: ["Bracket"],
        summary: "Ubah status pertandingan",
        description: "Admin mengubah status pertandingan: scheduled → live → finished. Status 'bye' tidak dapat diubah secara manual.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["status"],
            properties: [
                new OA\Property(property: "status", type: "string", enum: ["scheduled", "live", "finished"]),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Status pertandingan diperbarui — response menyertakan state pertandingan terbaru beserta cabang olahraga",
        content: new OA\JsonContent(ref: "#/components/schemas/MatchActionResponse")
    )]
    public function setStatus(Request $request, $id)
    {
        $game = Game::findOrFail($id);

        if ($game->status === 'bye') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Status pertandingan bye tidak dapat diubah secara manual.',
            ], 422);
        }

        $validated = $request->validate([
            'status' => 'required|in:scheduled,live,finished',
        ]);

        if (in_array($validated['status'], ['live', 'finished']) && $game->status === 'scheduled') {
            if (!$game->registration_a_id || !$game->registration_b_id) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Pertandingan belum memiliki dua tim yang lengkap.',
                ], 422);
            }

            $totalPlayers = $game->registrationA->players()->count() + $game->registrationB->players()->count();
            if ($totalPlayers === 0) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak ada pemain terdaftar di kedua tim.',
                ], 422);
            }

            $checkedInCount = $game->playerCheckins()->count();
            if ($checkedInCount < $totalPlayers) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Tidak bisa memulai pertandingan. Baru $checkedInCount dari $totalPlayers pemain yang check-in.",
                ], 422);
            }
        }

        $game->update(['status' => $validated['status']]);

        return response()->json([
            'status'  => 'success',
            'message' => "Status pertandingan diubah menjadi '{$validated['status']}'.",
            'data'    => $this->formatMatch($game->fresh()),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  GET CHECKIN STATUS PERTANDINGAN
    // ──────────────────────────────────────────────────────────────

    #[OA\Get(
        path: "/api/matches/{id}/checkin",
        operationId: "getMatchCheckin",
        tags: ["Bracket"],
        summary: "Daftar player kedua tim beserta status checkin",
        description: "Mengembalikan semua player dari tim A dan tim B beserta status checkin masing-masing untuk pertandingan tertentu. Player yang belum pernah dicheckin akan memiliki `checked_in: false` dan `checked_in_at: null`. `team_a` atau `team_b` bernilai `null` jika tim belum ditentukan.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID pertandingan (game)",
        schema: new OA\Schema(type: "integer", example: 12)
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data",   ref: "#/components/schemas/MatchCheckinData"),
            ]
        )
    )]
    #[OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 403, description: "Forbidden — hanya role panitia yang dapat mengakses", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 404, description: "Pertandingan tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function getCheckin($id)
    {
        $game = Game::with([
            'registrationA.contingent',
            'registrationA.players',
            'registrationB.contingent',
            'registrationB.players',
            'playerCheckins',
        ])->findOrFail($id);

        $checkinMap = $game->playerCheckins->keyBy('player_id');

        $formatTeam = function ($registration, string $slot) use ($checkinMap) {
            if (!$registration) return null;

            return [
                'registration_id' => $registration->id,
                'slot'            => $slot,
                'contingent'      => $registration->contingent,
                'players'         => $registration->players->map(function ($player) use ($checkinMap, $registration) {
                    $checkin = $checkinMap->get($player->id);
                    return [
                        'id'            => $player->id,
                        'name'          => $player->name,
                        'nim_nip'       => $player->nim_nip,
                        'photo_path'    => $player->photo_path,
                        'risk_lvl'      => $player->risk_lvl,
                        'checked_in'    => $checkin?->checked_in ?? false,
                        'checked_in_at' => $checkin?->checked_in_at?->toIso8601String(),
                    ];
                })->values(),
            ];
        };

        return response()->json([
            'status' => 'success',
            'data'   => [
                'match_id'     => $game->id,
                'round_name'   => $game->round_name,
                'match_number' => $game->match_number,
                'status'       => $game->status,
                'team_a'       => $formatTeam($game->registrationA, 'a'),
                'team_b'       => $formatTeam($game->registrationB, 'b'),
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  CHECKIN PLAYER
    // ──────────────────────────────────────────────────────────────

    #[OA\Post(
        path: "/api/matches/{id}/checkin/{player_id}",
        operationId: "checkinPlayer",
        tags: ["Bracket"],
        summary: "Checkin player — tandai hadir pada pertandingan",
        description: "Panitia menandai seorang player sebagai hadir dan siap bertanding. Operasi ini **idempoten**: jika player sudah pernah dicheckin sebelumnya, record diperbarui tanpa error. Player wajib terdaftar di salah satu tim (registrasi A atau B) pada pertandingan ini.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID pertandingan (game)",
        schema: new OA\Schema(type: "integer", example: 12)
    )]
    #[OA\Parameter(
        name: "player_id",
        in: "path",
        required: true,
        description: "ID player yang akan dicheckin",
        schema: new OA\Schema(type: "integer", example: 7)
    )]
    #[OA\Response(
        response: 200,
        description: "Player berhasil dicheckin — `checked_in` menjadi `true` dan `checked_in_at` diisi timestamp saat ini",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Player berhasil dicheckin."),
                new OA\Property(property: "data",    ref: "#/components/schemas/CheckinRecord"),
            ]
        )
    )]
    #[OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 403, description: "Forbidden — hanya role panitia yang dapat mengakses", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 404, description: "Pertandingan tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 422, description: "Player tidak terdaftar dalam pertandingan ini", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function checkinPlayer($id, $playerId)
    {
        $game = Game::with([
            'registrationA.players',
            'registrationB.players',
        ])->findOrFail($id);

        // Cari player ada di tim mana
        $registrationId = null;

        if ($game->registrationA && $game->registrationA->players->contains('id', $playerId)) {
            $registrationId = $game->registration_a_id;
        } elseif ($game->registrationB && $game->registrationB->players->contains('id', $playerId)) {
            $registrationId = $game->registration_b_id;
        }

        if (!$registrationId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Player tidak terdaftar dalam pertandingan ini.',
            ], 422);
        }

        $checkin = GamePlayerCheckin::updateOrCreate(
            ['game_id' => $game->id, 'player_id' => $playerId],
            [
                'registration_id' => $registrationId,
                'checked_in'      => true,
                'checked_in_at'   => now(),
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Player berhasil dicheckin.',
            'data'    => [
                'game_id'        => $checkin->game_id,
                'player_id'      => $checkin->player_id,
                'registration_id'=> $checkin->registration_id,
                'checked_in'     => $checkin->checked_in,
                'checked_in_at'  => $checkin->checked_in_at?->toIso8601String(),
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  UNDO CHECKIN PLAYER
    // ──────────────────────────────────────────────────────────────

    #[OA\Delete(
        path: "/api/matches/{id}/checkin/{player_id}",
        operationId: "undoCheckinPlayer",
        tags: ["Bracket"],
        summary: "Batalkan checkin player pada pertandingan",
        description: "Panitia membatalkan status kehadiran seorang player pada pertandingan tertentu. `checked_in` dikembalikan ke `false` dan `checked_in_at` dikosongkan. Mengembalikan 404 jika player belum pernah dicheckin sama sekali pada pertandingan ini.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID pertandingan (game)",
        schema: new OA\Schema(type: "integer", example: 12)
    )]
    #[OA\Parameter(
        name: "player_id",
        in: "path",
        required: true,
        description: "ID player yang akan dibatalkan checkin-nya",
        schema: new OA\Schema(type: "integer", example: 7)
    )]
    #[OA\Response(
        response: 200,
        description: "Checkin berhasil dibatalkan",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Checkin player berhasil dibatalkan."),
            ]
        )
    )]
    #[OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 403, description: "Forbidden — hanya role panitia yang dapat mengakses", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 404, description: "Data checkin tidak ditemukan — player belum pernah dicheckin pada pertandingan ini", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function undoCheckin($id, $playerId)
    {
        $checkin = GamePlayerCheckin::where('game_id', $id)
            ->where('player_id', $playerId)
            ->first();

        if (!$checkin) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data checkin tidak ditemukan.',
            ], 404);
        }

        $checkin->update([
            'checked_in'    => false,
            'checked_in_at' => null,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Checkin player berhasil dibatalkan.',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  RESET BRACKET
    // ──────────────────────────────────────────────────────────────

    #[OA\Delete(
        path: "/api/bracket/reset",
        operationId: "resetBracket",
        tags: ["Bracket"],
        summary: "Hapus seluruh bagan untuk satu cabang olahraga",
        description: "Menghapus semua pertandingan pada bagan untuk sport_id + sport_category_id tertentu. Digunakan sebelum generate ulang bagan.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "sport_id", in: "query", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "sport_category_id", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Bagan berhasil dihapus",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "8 pertandingan berhasil dihapus."),
            ]
        )
    )]
    public function reset(Request $request)
    {
        $request->validate([
            'sport_id'          => 'required|integer|exists:sports,id',
            'sport_category_id' => 'nullable|integer|exists:sport_categories,id',
        ]);

        $deleted = Game::where('sport_id', $request->sport_id)
            ->where('sport_category_id', $request->sport_category_id ?? null)
            ->delete();

        return response()->json([
            'status'  => 'success',
            'message' => "{$deleted} pertandingan berhasil dihapus.",
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  HELPER: BUILD BRACKET
    // ──────────────────────────────────────────────────────────────

    private function buildBracket(array $teams, int $sportId, ?int $categoryId): void
    {
        $n           = count($teams);
        $totalRounds = (int) ceil(log($n, 2));
        $bracketSize = (int) pow(2, $totalRounds);
        $byeCount    = $bracketSize - $n;
        $roundLabels = $this->roundLabels($totalRounds);

        // Susun slot: bye disisipkan di awal tiap match agar tersebar merata
        $slots = [];
        $teamIndex = 0;
        $byeGiven  = 0;

        for ($match = 0; $match < $bracketSize / 2; $match++) {
            $slots[] = $teams[$teamIndex++]; // slot A selalu tim nyata
            if ($byeGiven < $byeCount) {
                $slots[] = null;             // slot B = bye
                $byeGiven++;
            } else {
                $slots[] = $teams[$teamIndex++]; // slot B = tim nyata
            }
        }

        // Buat semua ronde, simpan game per ronde untuk linking
        $prevRoundGames = [];

        for ($round = 1; $round <= $totalRounds; $round++) {
            $matchesInRound   = (int) ($bracketSize / pow(2, $round));
            $currentRoundGames = [];

            for ($matchIdx = 0; $matchIdx < $matchesInRound; $matchIdx++) {
                if ($round === 1) {
                    $regA = $slots[$matchIdx * 2];
                    $regB = $slots[$matchIdx * 2 + 1];

                    $regAId   = $regA ? $regA['id'] : null;
                    $regBId   = $regB ? $regB['id'] : null;
                    $isBye    = ($regAId === null || $regBId === null);
                    $winnerId = null;

                    if ($isBye && ($regAId || $regBId)) {
                        $winnerId = $regAId ?? $regBId;
                    }

                    $game = Game::create([
                        'sport_id'              => $sportId,
                        'sport_category_id'     => $categoryId,
                        'round'                 => $round,
                        'round_name'            => $roundLabels[$round],
                        'match_number'          => $matchIdx + 1,
                        'registration_a_id'     => $regAId,
                        'registration_b_id'     => $regBId,
                        'winner_registration_id'=> $winnerId,
                        'status'                => $isBye ? 'bye' : 'scheduled',
                        'score_a'               => 0,
                        'score_b'               => 0,
                    ]);
                } else {
                    // Buat slot kosong untuk ronde ini
                    $game = Game::create([
                        'sport_id'          => $sportId,
                        'sport_category_id' => $categoryId,
                        'round'             => $round,
                        'round_name'        => $roundLabels[$round],
                        'match_number'      => $matchIdx + 1,
                        'status'            => 'scheduled',
                        'score_a'           => 0,
                        'score_b'           => 0,
                    ]);

                    // Link dua match ronde sebelumnya ke match ini
                    $prevA = $prevRoundGames[$matchIdx * 2]     ?? null;
                    $prevB = $prevRoundGames[$matchIdx * 2 + 1] ?? null;

                    if ($prevA) {
                        $prevA->update(['next_match_id' => $game->id, 'next_match_slot' => 'a']);
                        // Propagasi pemenang bye ke slot berikutnya
                        if ($prevA->winner_registration_id) {
                            $game->update(['registration_a_id' => $prevA->winner_registration_id]);
                        }
                    }

                    if ($prevB) {
                        $prevB->update(['next_match_id' => $game->id, 'next_match_slot' => 'b']);
                        if ($prevB->winner_registration_id) {
                            $game->update(['registration_b_id' => $prevB->winner_registration_id]);
                        }
                    }
                }

                $currentRoundGames[] = $game->fresh();
            }

            $prevRoundGames = $currentRoundGames;
        }

        // Buat pertandingan perebutan juara 3 jika ada ronde semifinal (totalRounds >= 2)
        if ($totalRounds >= 2) {
            $semiRound = $totalRounds - 1;

            $thirdPlace = Game::create([
                'sport_id'             => $sportId,
                'sport_category_id'    => $categoryId,
                'round'                => $totalRounds,
                'round_name'           => 'Perebutan Juara 3',
                'match_number'         => 2,
                'is_third_place_match' => true,
                'status'               => 'scheduled',
                'score_a'              => 0,
                'score_b'              => 0,
            ]);

            // Link dua pertandingan semifinal ke pertandingan juara 3 (untuk propagasi loser)
            $semiFinals = Game::where('sport_id', $sportId)
                ->where('sport_category_id', $categoryId)
                ->where('round', $semiRound)
                ->orderBy('match_number')
                ->get();

            foreach ($semiFinals->take(2) as $index => $sf) {
                $sf->update([
                    'loser_next_match_id'   => $thirdPlace->id,
                    'loser_next_match_slot' => $index === 0 ? 'a' : 'b',
                ]);
            }
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  HELPER: NAMA RONDE
    // ──────────────────────────────────────────────────────────────

    private function roundLabels(int $totalRounds): array
    {
        $names = ['Final', 'Semifinal', 'Perempat Final', 'Babak 16 Besar', 'Babak 32 Besar', 'Babak 64 Besar'];
        $labels = [];

        for ($r = 1; $r <= $totalRounds; $r++) {
            $fromEnd    = $totalRounds - $r;
            $labels[$r] = $names[$fromEnd] ?? "Ronde {$r}";
        }

        return $labels;
    }

    // ──────────────────────────────────────────────────────────────
    //  HELPER: TAMPILAN BAGAN
    // ──────────────────────────────────────────────────────────────

    private function bracketView(int $sportId, ?int $categoryId): array
    {
        $sport    = Sport::find($sportId);
        $category = $categoryId ? SportCategory::find($categoryId) : null;

        $games = Game::with([
            'sport',
            'sportCategory',
            'registrationA.contingent',
            'registrationA.players',
            'registrationB.contingent',
            'registrationB.players',
            'winner.contingent',
        ])
        ->where('sport_id', $sportId)
        ->where('sport_category_id', $categoryId)
        ->orderBy('round')
        ->orderBy('match_number')
        ->get();

        // Pisahkan pertandingan perebutan juara 3 dari bagan utama
        $thirdPlaceGame = $games->firstWhere('is_third_place_match', true);
        $mainGames      = $games->where('is_third_place_match', false);

        $rounds = $mainGames->groupBy('round')->map(function ($matches, $round) {
            return [
                'round'   => (int) $round,
                'name'    => $matches->first()->round_name,
                'matches' => $matches->map(fn ($g) => $this->formatMatch($g))->values(),
            ];
        })->values();

        // Final adalah pertandingan bagan utama di ronde tertinggi
        $finalGame = $mainGames->sortByDesc('round')->first();

        return [
            'sport'             => $sport,
            'sport_category'    => $category,
            'total_rounds'      => $rounds->count(),
            'rounds'            => $rounds,
            'third_place_match' => $thirdPlaceGame ? $this->formatMatch($thirdPlaceGame) : null,
            'results'           => $this->computeResults($finalGame, $thirdPlaceGame),
        ];
    }

    private function computeResults(?Game $finalGame, ?Game $thirdPlaceGame): array
    {
        $juara1 = null;
        $juara2 = null;
        $juara3 = null;

        if ($finalGame && $finalGame->winner_registration_id) {
            $juara1 = [
                'registration_id' => $finalGame->winner_registration_id,
                'contingent'      => optional($finalGame->winner)->contingent,
            ];

            // Loser Final = Juara 2
            if ($finalGame->registration_a_id && $finalGame->registration_b_id) {
                $isAWinner  = $finalGame->winner_registration_id === $finalGame->registration_a_id;
                $loserRegId = $isAWinner ? $finalGame->registration_b_id : $finalGame->registration_a_id;
                $loserReg   = $isAWinner ? $finalGame->registrationB : $finalGame->registrationA;

                $juara2 = [
                    'registration_id' => $loserRegId,
                    'contingent'      => optional($loserReg)->contingent,
                ];
            }
        }

        if ($thirdPlaceGame && $thirdPlaceGame->winner_registration_id) {
            $juara3 = [
                'registration_id' => $thirdPlaceGame->winner_registration_id,
                'contingent'      => optional($thirdPlaceGame->winner)->contingent,
            ];
        }

        return compact('juara1', 'juara2', 'juara3');
    }

    // ──────────────────────────────────────────────────────────────
    //  HELPER: FORMAT MATCH
    // ──────────────────────────────────────────────────────────────

    /**
     * Format ringkas untuk tampilan publik (penonton).
     * Tidak menyertakan daftar pemain individual agar response tetap ringan.
     */
    private function formatPublicMatch(Game $game): array
    {
        return [
            'id'                   => $game->id,
            'sport'                => $game->sport ? [
                'id'       => $game->sport->id,
                'name'     => $game->sport->name,
                'icon_path'=> $game->sport->icon_path,
            ] : null,
            'sport_category'       => $game->sportCategory ? [
                'id'   => $game->sportCategory->id,
                'name' => $game->sportCategory->name,
            ] : null,
            'round'                => $game->round,
            'round_name'           => $game->round_name,
            'match_number'         => $game->match_number,
            'is_third_place_match' => (bool) $game->is_third_place_match,
            'status'               => $game->status,
            'match_date'           => $game->match_date?->format('Y-m-d'),
            'match_time'           => $game->match_time,
            'location'             => $game->location,
            'score_a'              => $game->score_a,
            'score_b'              => $game->score_b,
            'team_a'               => $game->registrationA ? [
                'registration_id' => $game->registration_a_id,
                'contingent_id'   => $game->registrationA->contingent?->id,
                'contingent_name' => $game->registrationA->contingent?->name,
                'image_url'       => $game->registrationA->contingent?->image_url,
            ] : null,
            'team_b'               => $game->registrationB ? [
                'registration_id' => $game->registration_b_id,
                'contingent_id'   => $game->registrationB->contingent?->id,
                'contingent_name' => $game->registrationB->contingent?->name,
                'image_url'       => $game->registrationB->contingent?->image_url,
            ] : null,
            'winner'               => $game->winner_registration_id ? [
                'registration_id' => $game->winner_registration_id,
                'contingent_id'   => $game->winner?->contingent?->id,
                'contingent_name' => $game->winner?->contingent?->name,
            ] : null,
        ];
    }

    private function formatMatch(Game $game): array
    {
        // Pastikan semua relasi tersedia, baik dari eager load maupun fresh()
        $game->loadMissing([
            'sport',
            'sportCategory',
            'registrationA.contingent',
            'registrationA.players',
            'registrationB.contingent',
            'registrationB.players',
            'winner.contingent',
        ]);

        return [
            'id'                   => $game->id,

            // Cabang olahraga
            'sport' => $game->sport ? [
                'id'        => $game->sport->id,
                'name'      => $game->sport->name,
                'icon_path' => $game->sport->icon_path,
            ] : null,
            'sport_category' => $game->sportCategory ? [
                'id'   => $game->sportCategory->id,
                'name' => $game->sportCategory->name,
            ] : null,

            'round'                => $game->round,
            'round_name'           => $game->round_name,
            'match_number'         => $game->match_number,
            'is_third_place_match' => (bool) $game->is_third_place_match,
            'status'               => $game->status,

            // Jadwal & venue
            'match_date'   => $game->match_date?->format('Y-m-d'),
            'match_time'   => $game->match_time,
            'location'     => $game->location,
            'referee_name' => $game->referee_name,
            'notes'        => $game->notes,

            // Skor
            'score_a' => $game->score_a,
            'score_b' => $game->score_b,

            // Tim A
            'team_a' => $game->registrationA ? [
                'registration_id' => $game->registration_a_id,
                'contingent'      => $game->registrationA->contingent,
                'players'         => $game->registrationA->players,
            ] : null,

            // Tim B
            'team_b' => $game->registrationB ? [
                'registration_id' => $game->registration_b_id,
                'contingent'      => $game->registrationB->contingent,
                'players'         => $game->registrationB->players,
            ] : null,

            // Pemenang
            'winner' => $game->winner_registration_id ? [
                'registration_id' => $game->winner_registration_id,
                'contingent'      => optional($game->winner)->contingent,
            ] : null,

            // Link ke babak berikutnya
            'next_match_id'   => $game->next_match_id,
            'next_match_slot' => $game->next_match_slot,
        ];
    }
}
