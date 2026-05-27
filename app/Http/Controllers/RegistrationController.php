<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Player;
use App\Models\Sport;
use App\Models\SportCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    // ----------------------------------------------------------------
    // PIC KONTINGEN — Daftar & kelola tim
    // ----------------------------------------------------------------

    /**
     * POST /registrations
     * PIC mendaftarkan tim kontingennya ke satu cabang olahraga.
     *
     * Body:
     * {
     *   "sport_id": 1,
     *   "sport_category_id": 2,   // opsional, hanya jika sport punya kategori
     *   "player_ids": [3, 7, 11]  // opsional, bisa ditambah belakangan
     * }
     */
    public function store(Request $request)
    {
        $user       = $request->user();
        $contingent = $user->managedContingent;

        if (!$contingent) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda belum ditugaskan sebagai PIC kontingen manapun.',
            ], 403);
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

        // Validasi: kategori harus milik sport yang dipilih
        if ($category && $category->sport_id !== $sport->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kategori yang dipilih tidak termasuk dalam cabang olahraga tersebut.',
            ], 422);
        }

        // Validasi: sport yang punya kategori harus memilih kategori
        if ($sport->categories()->exists() && !$category) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cabang olahraga ini memiliki sub-kategori. Harap pilih salah satu kategori.',
            ], 422);
        }

        // Cek apakah kontingen sudah punya tim di sport+kategori ini
        $exists = Registration::where('contingent_id', $contingent->id)
            ->where('sport_id', $sport->id)
            ->where('sport_category_id', $validated['sport_category_id'] ?? null)
            ->exists();

        if ($exists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kontingen Anda sudah mendaftarkan tim untuk cabang olahraga ini.',
            ], 422);
        }

        // Tentukan max_members
        $maxMembers = $category ? $category->max_members : $sport->max_members;
        $playerIds  = $validated['player_ids'] ?? [];

        if ($maxMembers !== null && count($playerIds) > $maxMembers) {
            return response()->json([
                'status'  => 'error',
                'message' => "Jumlah pemain melebihi batas maksimal tim ({$maxMembers} orang).",
            ], 422);
        }

        DB::beginTransaction();
        try {
            $registration = Registration::create([
                'contingent_id'    => $contingent->id,
                'sport_id'         => $sport->id,
                'sport_category_id'=> $validated['sport_category_id'] ?? null,
                'status'           => 'pending',
            ]);

            if (!empty($playerIds)) {
                $this->attachPlayers($registration, $playerIds, $contingent->id);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Tim berhasil didaftarkan.',
                'data'    => $registration->load(['contingent', 'sport', 'sportCategory', 'players']),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /registrations/{id}/players
     * PIC menambahkan satu atau beberapa player ke tim yang sudah terdaftar.
     *
     * Body: { "player_ids": [5, 9] }
     */
    public function addPlayers(Request $request, $id)
    {
        $user         = $request->user();
        $contingent   = $user->managedContingent;
        $registration = Registration::with(['sport', 'sportCategory', 'players'])->findOrFail($id);

        if (!$contingent || $registration->contingent_id !== $contingent->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki akses ke pendaftaran ini.',
            ], 403);
        }

        if ($registration->status !== 'pending') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tim yang sudah diverifikasi tidak dapat diubah.',
            ], 422);
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
            return response()->json([
                'status'  => 'error',
                'message' => "Kapasitas tim penuh. Maksimal {$maxMembers} pemain, sisa slot: {$remaining}.",
            ], 422);
        }

        DB::beginTransaction();
        try {
            $this->attachPlayers($registration, $validated['player_ids'], $contingent->id);
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Pemain berhasil ditambahkan ke tim.',
                'data'    => $registration->fresh(['contingent', 'sport', 'sportCategory', 'players']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * DELETE /registrations/{id}/players/{player_id}
     * PIC mengeluarkan satu player dari tim.
     */
    public function removePlayer(Request $request, $id, $playerId)
    {
        $user         = $request->user();
        $contingent   = $user->managedContingent;
        $registration = Registration::findOrFail($id);

        if (!$contingent || $registration->contingent_id !== $contingent->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki akses ke pendaftaran ini.',
            ], 403);
        }

        if ($registration->status !== 'pending') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tim yang sudah diverifikasi tidak dapat diubah.',
            ], 422);
        }

        $detached = $registration->players()->detach($playerId);

        if (!$detached) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Player tidak ditemukan dalam tim ini.',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Pemain berhasil dikeluarkan dari tim.',
            'data'    => $registration->fresh(['contingent', 'sport', 'sportCategory', 'players']),
        ]);
    }

    /**
     * GET /registrations/my
     * PIC melihat semua pendaftaran tim kontingennya.
     */
    public function myRegistrations(Request $request)
    {
        $contingent = $request->user()->managedContingent;

        if (!$contingent) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda belum ditugaskan sebagai PIC kontingen manapun.',
            ], 403);
        }

        $registrations = Registration::with(['sport', 'sportCategory', 'players'])
            ->where('contingent_id', $contingent->id)
            ->get()
            ->map(fn ($r) => $this->formatRegistration($r));

        return response()->json([
            'status' => 'success',
            'data'   => $registrations,
        ]);
    }

    // ----------------------------------------------------------------
    // PANITIA / ADMIN — Pantau & verifikasi
    // ----------------------------------------------------------------

    /**
     * GET /registrations
     * Panitia melihat semua pendaftaran tim dengan filter opsional.
     */
    public function index(Request $request)
    {
        $query = Registration::with(['contingent', 'sport', 'sportCategory', 'players']);

        if ($request->filled('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        if ($request->filled('contingent_id')) {
            $query->where('contingent_id', $request->contingent_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $registrations = $query->paginate(15);

        $registrations->getCollection()->transform(
            fn ($r) => $this->formatRegistration($r)
        );

        return response()->json([
            'status' => 'success',
            'data'   => $registrations,
        ]);
    }

    /**
     * GET /registrations/{id}
     * Detail satu pendaftaran tim.
     */
    public function show($id)
    {
        $registration = Registration::with(['contingent', 'sport', 'sportCategory', 'players'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $this->formatRegistration($registration),
        ]);
    }

    /**
     * POST /registrations/{id}/verify
     * Panitia menyetujui atau menolak pendaftaran tim.
     * Body: { "status": "verified" | "rejected" }
     */
    public function verify(Request $request, $id)
    {
        $registration = Registration::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:verified,rejected',
        ]);

        $registration->update(['status' => $validated['status']]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Status pendaftaran berhasil diperbarui.',
            'data'    => $registration->fresh(['contingent', 'sport', 'sportCategory', 'players']),
        ]);
    }

    // ----------------------------------------------------------------
    // Helper
    // ----------------------------------------------------------------

    private function attachPlayers(Registration $registration, array $playerIds, int $contingentId): void
    {
        $alreadyInTeam = $registration->players()->pluck('players.id')->toArray();

        foreach ($playerIds as $playerId) {
            if (in_array($playerId, $alreadyInTeam)) {
                throw new \RuntimeException("Player ID {$playerId} sudah ada dalam tim ini.");
            }

            $player = Player::findOrFail($playerId);

            if ($player->contingent_id !== $contingentId) {
                throw new \RuntimeException(
                    "Player \"{$player->name}\" bukan anggota kontingen Anda."
                );
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
