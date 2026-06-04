<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use App\Models\Registration;
use App\Models\Player;
use App\Models\Sport;
use App\Models\SportCategory;
use App\Models\Contingent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    #[OA\Post(
        path: "/api/registrations",
        operationId: "createRegistration",
        tags: ["Registrations"],
        summary: "PIC mendaftarkan tim ke cabang olahraga (tahap draft)",
        description: "PIC kontingen mendaftarkan timnya ke satu cabang olahraga. Pendaftaran dibuat dengan status `draft` — PIC masih bisa menambah/mengeluarkan pemain. Setelah siap, PIC harus memanggil endpoint submit untuk mengajukan ke panitia.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["sport_id"],
            properties: [
                new OA\Property(property: "sport_id", type: "integer", example: 1),
                new OA\Property(property: "sport_category_id", type: "integer", example: 2, description: "Wajib jika sport memiliki sub-kategori"),
                new OA\Property(property: "player_ids", type: "array", items: new OA\Items(type: "integer"), example: [3, 7, 11], description: "Opsional — bisa ditambahkan belakangan"),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Draft tim berhasil dibuat",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Draft tim berhasil dibuat."),
                new OA\Property(property: "data",    ref: "#/components/schemas/RegistrationObject"),
            ]
        )
    )]
    #[OA\Response(response: 422, description: "Validasi gagal atau kontingen sudah terdaftar", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 403, description: "Belum ditugaskan sebagai PIC", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function store(Request $request)
    {
        $user       = $request->user();
        $contingent = $user->managedContingent;

        if (!$contingent) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum ditugaskan sebagai PIC kontingen manapun.'], 403);
        }

        $validated = $request->validate([
            'sport_id'          => 'required|integer|exists:sports,id',
            'sport_category_id' => 'nullable|integer|exists:sport_categories,id',
            'player_ids'        => 'nullable|array',
            'player_ids.*'      => 'integer|exists:players,id',
        ]);

        $sport    = Sport::findOrFail($validated['sport_id']);
        $category = isset($validated['sport_category_id'])
                        ? SportCategory::findOrFail($validated['sport_category_id'])
                        : null;

        if ($category && $category->sport_id !== $sport->id) {
            return response()->json(['status' => 'error', 'message' => 'Kategori tidak termasuk dalam cabang olahraga tersebut.'], 422);
        }

        if ($sport->categories()->exists() && !$category) {
            return response()->json(['status' => 'error', 'message' => 'Cabang olahraga ini memiliki sub-kategori. Harap pilih salah satu.'], 422);
        }

        $exists = Registration::where('contingent_id', $contingent->id)
            ->where('sport_id', $sport->id)
            ->where('sport_category_id', $validated['sport_category_id'] ?? null)
            ->exists();

        if ($exists) {
            return response()->json(['status' => 'error', 'message' => 'Kontingen Anda sudah mendaftarkan tim untuk cabang olahraga ini.'], 422);
        }

        $maxMembers = $category ? $category->max_members : $sport->max_members;
        $playerIds  = $validated['player_ids'] ?? [];

        if ($maxMembers !== null && count($playerIds) > $maxMembers) {
            return response()->json(['status' => 'error', 'message' => "Jumlah pemain melebihi batas maksimal ({$maxMembers} orang)."], 422);
        }

        DB::beginTransaction();
        try {
            $registration = Registration::create([
                'contingent_id'     => $contingent->id,
                'sport_id'          => $sport->id,
                'sport_category_id' => $validated['sport_category_id'] ?? null,
                'status'            => 'draft',
            ]);

            if (!empty($playerIds)) {
                $this->attachPlayers($registration, $playerIds, $contingent->id);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Draft tim berhasil dibuat.',
                'data'    => $this->formatRegistration($registration->load(['contingent', 'sport', 'sportCategory', 'players'])),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    #[OA\Post(
        path: "/api/registrations/{id}/submit",
        operationId: "submitRegistration",
        tags: ["Registrations"],
        summary: "PIC mengajukan pendaftaran tim ke panitia",
        description: "PIC mengubah status pendaftaran dari `draft` menjadi `submitted`. Setelah disubmit, daftar pemain tidak dapat diubah lagi dan pendaftaran menunggu verifikasi panitia.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"), description: "ID registrasi")]
    #[OA\Response(
        response: 200,
        description: "Pendaftaran berhasil diajukan",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Pendaftaran tim berhasil diajukan ke panitia."),
                new OA\Property(property: "data",    ref: "#/components/schemas/RegistrationObject"),
            ]
        )
    )]
    #[OA\Response(response: 422, description: "Pendaftaran tidak dalam status draft", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 403, description: "Bukan milik kontingen Anda", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function submit(Request $request, $id)
    {
        $user         = $request->user();
        $contingent   = $user->managedContingent;
        $registration = Registration::with(['contingent', 'sport', 'sportCategory', 'players'])->findOrFail($id);

        if (!$contingent || $registration->contingent_id !== $contingent->id) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses ke pendaftaran ini.'], 403);
        }

        if ($registration->status !== 'draft') {
            return response()->json(['status' => 'error', 'message' => 'Hanya pendaftaran berstatus draft yang dapat diajukan.'], 422);
        }

        $registration->update(['status' => 'submitted']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pendaftaran tim berhasil diajukan ke panitia.',
            'data'    => $this->formatRegistration($registration->fresh(['contingent', 'sport', 'sportCategory', 'players'])),
        ]);
    }

    #[OA\Post(
        path: "/api/registrations/{id}/players",
        operationId: "addPlayersToRegistration",
        tags: ["Registrations"],
        summary: "PIC menambahkan pemain ke tim",
        description: "PIC menambahkan satu atau lebih player ke pendaftaran tim. Validasi: player harus dari kontingen yang sama, tidak boleh melebihi `max_members`, dan status pendaftaran harus masih `draft`.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"), description: "ID registrasi")]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["player_ids"],
            properties: [
                new OA\Property(property: "player_ids", type: "array", items: new OA\Items(type: "integer"), example: [5, 9]),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Pemain berhasil ditambahkan",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Pemain berhasil ditambahkan ke tim."),
                new OA\Property(property: "data",    ref: "#/components/schemas/RegistrationObject"),
            ]
        )
    )]
    #[OA\Response(response: 422, description: "Kapasitas tim penuh, player bukan dari kontingen ini, atau pendaftaran sudah disubmit", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function addPlayers(Request $request, $id)
    {
        $user         = $request->user();
        $contingent   = $user->managedContingent;
        $registration = Registration::with(['sport', 'sportCategory', 'players'])->findOrFail($id);

        if (!$contingent || $registration->contingent_id !== $contingent->id) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses ke pendaftaran ini.'], 403);
        }

        if ($registration->status !== 'draft') {
            return response()->json(['status' => 'error', 'message' => 'Pemain hanya dapat diubah saat pendaftaran masih berstatus draft.'], 422);
        }

        $validated = $request->validate([
            'player_ids'   => 'required|array|min:1',
            'player_ids.*' => 'integer|exists:players,id',
        ]);

        $maxMembers   = $registration->maxMembers();
        $currentCount = $registration->players()->count();
        $incoming     = count($validated['player_ids']);

        if ($maxMembers !== null && ($currentCount + $incoming) > $maxMembers) {
            $remaining = max(0, $maxMembers - $currentCount);
            return response()->json(['status' => 'error', 'message' => "Kapasitas tim penuh. Sisa slot: {$remaining}."], 422);
        }

        DB::beginTransaction();
        try {
            $this->attachPlayers($registration, $validated['player_ids'], $contingent->id);
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Pemain berhasil ditambahkan ke tim.',
                'data'    => $this->formatRegistration($registration->fresh(['contingent', 'sport', 'sportCategory', 'players'])),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    #[OA\Delete(
        path: "/api/registrations/{id}/players/{player_id}",
        operationId: "removePlayerFromRegistration",
        tags: ["Registrations"],
        summary: "PIC mengeluarkan pemain dari tim",
        description: "PIC mengeluarkan satu player dari pendaftaran tim yang masih berstatus draft.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"), description: "ID registrasi")]
    #[OA\Parameter(name: "player_id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Pemain berhasil dikeluarkan",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Pemain berhasil dikeluarkan dari tim."),
                new OA\Property(property: "data",    ref: "#/components/schemas/RegistrationObject"),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Player tidak ditemukan dalam tim", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function removePlayer(Request $request, $id, $playerId)
    {
        $user         = $request->user();
        $contingent   = $user->managedContingent;
        $registration = Registration::findOrFail($id);

        if (!$contingent || $registration->contingent_id !== $contingent->id) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses ke pendaftaran ini.'], 403);
        }

        if ($registration->status !== 'draft') {
            return response()->json(['status' => 'error', 'message' => 'Pemain hanya dapat diubah saat pendaftaran masih berstatus draft.'], 422);
        }

        $detached = $registration->players()->detach($playerId);

        if (!$detached) {
            return response()->json(['status' => 'error', 'message' => 'Player tidak ditemukan dalam tim ini.'], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Pemain berhasil dikeluarkan dari tim.',
            'data'    => $this->formatRegistration($registration->fresh(['contingent', 'sport', 'sportCategory', 'players'])),
        ]);
    }

    #[OA\Get(
        path: "/api/registrations/my",
        operationId: "myRegistrations",
        tags: ["Registrations"],
        summary: "PIC melihat semua pendaftaran tim kontingennya",
        description: "Mengembalikan semua tim yang telah didaftarkan oleh kontingen PIC yang sedang login, lengkap dengan slot tersisa.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data",   type: "array", items: new OA\Items(ref: "#/components/schemas/RegistrationObject")),
            ]
        )
    )]
    #[OA\Response(response: 403, description: "Belum ditugaskan sebagai PIC", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function myRegistrations(Request $request)
    {
        $contingent = $request->user()->managedContingent;

        if (!$contingent) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum ditugaskan sebagai PIC kontingen manapun.'], 403);
        }

        $registrations = Registration::with(['sport', 'sportCategory', 'players'])
            ->where('contingent_id', $contingent->id)
            ->get()
            ->map(fn ($r) => $this->formatRegistration($r));

        return response()->json(['status' => 'success', 'data' => $registrations]);
    }

    #[OA\Get(
        path: "/api/registrations",
        operationId: "listRegistrations",
        tags: ["Registrations"],
        summary: "Panitia melihat semua pendaftaran tim",
        description: "Panitia/admin melihat seluruh pendaftaran tim dengan filter opsional berdasarkan sport, kontingen, dan status.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "sport_id", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "contingent_id", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "status", in: "query", required: false, schema: new OA\Schema(type: "string", enum: ["draft", "submitted", "verified", "rejected"]))]
    #[OA\Response(
        response: 200,
        description: "Berhasil (paginated)",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "data",         type: "array", items: new OA\Items(ref: "#/components/schemas/RegistrationObject")),
                        new OA\Property(property: "current_page", type: "integer", example: 1),
                        new OA\Property(property: "last_page",    type: "integer", example: 3),
                        new OA\Property(property: "per_page",     type: "integer", example: 15),
                        new OA\Property(property: "total",        type: "integer", example: 42),
                    ]
                ),
            ]
        )
    )]
    public function index(Request $request)
    {
        $query = Registration::with(['contingent', 'sport', 'sportCategory', 'players']);

        if ($request->filled('sport_id'))          $query->where('sport_id', $request->sport_id);
        if ($request->filled('sport_category_id')) $query->where('sport_category_id', $request->sport_category_id);
        if ($request->filled('contingent_id'))     $query->where('contingent_id', $request->contingent_id);
        if ($request->filled('status'))            $query->where('status', $request->status);

        $perPage = $request->input('per_page', 15);
        $registrations = $query->paginate($perPage);
        $registrations->getCollection()->transform(fn ($r) => $this->formatRegistration($r));

        return response()->json(['status' => 'success', 'data' => $registrations]);
    }

    #[OA\Get(
        path: "/api/registrations/{id}",
        operationId: "showRegistration",
        tags: ["Registrations"],
        summary: "Detail satu pendaftaran tim",
        description: "Mengembalikan detail lengkap satu pendaftaran tim termasuk daftar pemain dan slot yang tersisa.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data",   ref: "#/components/schemas/RegistrationObject"),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function show($id)
    {
        $registration = Registration::with(['contingent', 'sport', 'sportCategory', 'players'])->findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $this->formatRegistration($registration)]);
    }

    #[OA\Post(
        path: "/api/registrations/{id}/verify",
        operationId: "verifyRegistration",
        tags: ["Registrations"],
        summary: "Panitia menyetujui atau menolak pendaftaran tim",
        description: "Panitia/admin mengubah status pendaftaran tim dari `submitted` menjadi `verified` (disetujui) atau `rejected` (ditolak). Hanya tim yang sudah disubmit oleh PIC yang dapat diverifikasi.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["status"],
            properties: [
                new OA\Property(property: "status", type: "string", enum: ["verified", "rejected"]),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Status berhasil diperbarui",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Status pendaftaran berhasil diperbarui."),
                new OA\Property(property: "data",    ref: "#/components/schemas/RegistrationObject"),
            ]
        )
    )]
    #[OA\Response(response: 422, description: "Pendaftaran belum disubmit oleh PIC", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function verify(Request $request, $id)
    {
        $registration = Registration::findOrFail($id);

        if ($registration->status !== 'submitted') {
            return response()->json(['status' => 'error', 'message' => 'Hanya pendaftaran yang sudah disubmit oleh PIC yang dapat diverifikasi.'], 422);
        }

        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
        ]);

        $registration->update(['status' => $validated['status']]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Status pendaftaran berhasil diperbarui.',
            'data'    => $this->formatRegistration($registration->fresh(['contingent', 'sport', 'sportCategory', 'players'])),
        ]);
    }

    #[OA\Get(
        path: "/api/registrations/compliance",
        operationId: "registrationCompliance",
        tags: ["Registrations"],
        summary: "Panitia melihat kepatuhan pendaftaran tiap kontingen",
        description: "Mengembalikan daftar tiap cabang olahraga (dan sub-kategorinya) beserta kontingen yang sudah dan belum mendaftar. Satu kontingen wajib mengirim satu tim ke setiap sport/sub-kategori yang tersedia.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "sport_id", in: "query", required: false, schema: new OA\Schema(type: "integer"), description: "Filter ke satu cabang olahraga tertentu")]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data",   type: "array",
                    items: new OA\Items(ref: "#/components/schemas/ComplianceRow")),
            ]
        )
    )]
    public function compliance(Request $request)
    {
        $allContingents = Contingent::orderBy('name')->get();
        $totalContingents = $allContingents->count();

        $sportsQuery = Sport::with('categories')->orderBy('name');
        if ($request->filled('sport_id')) {
            $sportsQuery->where('id', $request->sport_id);
        }
        $sports = $sportsQuery->get();

        // Ambil semua registrasi sekaligus untuk menghindari N+1
        $allRegistrations = Registration::with('contingent')
            ->when($request->filled('sport_id'), fn ($q) => $q->where('sport_id', $request->sport_id))
            ->get()
            ->groupBy(fn ($r) => $r->sport_id . '_' . ($r->sport_category_id ?? 'null'));

        $result = [];

        foreach ($sports as $sport) {
            // Sport tanpa sub-kategori → satu slot wajib per kontingen
            if ($sport->categories->isEmpty()) {
                $key           = $sport->id . '_null';
                $registrations = $allRegistrations->get($key, collect());

                $result[] = $this->buildComplianceRow(
                    $sport, null, $allContingents, $registrations, $totalContingents
                );
            } else {
                // Sport dengan sub-kategori → satu slot wajib per kategori per kontingen
                foreach ($sport->categories as $category) {
                    $key           = $sport->id . '_' . $category->id;
                    $registrations = $allRegistrations->get($key, collect());

                    $result[] = $this->buildComplianceRow(
                        $sport, $category, $allContingents, $registrations, $totalContingents
                    );
                }
            }
        }

        return response()->json(['status' => 'success', 'data' => $result]);
    }

    // ──────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────

    private function buildComplianceRow(
        Sport $sport,
        ?SportCategory $category,
        $allContingents,
        $registrations,
        int $totalContingents
    ): array {
        $registeredContingentIds = $registrations->pluck('contingent_id')->toArray();

        $registered = $registrations->map(fn ($r) => [
            'registration_id' => $r->id,
            'status'          => $r->status,
            'contingent'      => $r->contingent,
        ])->values();

        $notRegistered = $allContingents
            ->whereNotIn('id', $registeredContingentIds)
            ->values();

        $registeredCount = $registered->count();
        $complianceRate  = $totalContingents > 0
            ? round($registeredCount / $totalContingents * 100, 1)
            : 0;

        return [
            'sport'                => $sport->only(['id', 'name', 'icon_path']),
            'sport_category'       => $category ? $category->only(['id', 'name']) : null,
            'total_contingents'    => $totalContingents,
            'registered_count'     => $registeredCount,
            'not_registered_count' => $totalContingents - $registeredCount,
            'compliance_rate'      => $complianceRate,
            'registered'           => $registered,
            'not_registered'       => $notRegistered,
        ];
    }

    private function attachPlayers(Registration $registration, array $playerIds, int $contingentId): void
    {
        $alreadyInTeam = $registration->players()->pluck('players.id')->toArray();

        foreach ($playerIds as $playerId) {
            if (in_array($playerId, $alreadyInTeam)) {
                throw new \RuntimeException("Player ID {$playerId} sudah ada dalam tim ini.");
            }

            $player = Player::findOrFail($playerId);

            if ($player->contingent_id !== $contingentId) {
                throw new \RuntimeException("Player \"{$player->name}\" bukan anggota kontingen Anda.");
            }
        }

        $registration->players()->attach($playerIds);
    }

    private function formatRegistration(Registration $r): array
    {
        $maxMembers = $r->maxMembers();

        return [
            'id'              => $r->id,
            'status'          => $r->status,
            'contingent'      => $r->contingent,
            'sport'           => $r->sport,
            'sport_category'  => $r->sportCategory,
            'max_members'     => $maxMembers,
            'current_members' => $r->players->count(),
            'slots_remaining' => $maxMembers !== null ? max(0, $maxMembers - $r->players->count()) : null,
            'players'         => $r->players,
            'created_at'      => $r->created_at,
            'updated_at'      => $r->updated_at,
        ];
    }
}
