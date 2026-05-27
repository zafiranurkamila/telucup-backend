<?php

namespace App\Http\Controllers;

use App\Models\Contingent;
use App\Models\Player;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContingentController extends Controller
{
    // ----------------------------------------------------------------
    // PUBLIC / SEMUA USER LOGIN
    // ----------------------------------------------------------------

    /**
     * GET /contingents
     * Daftar semua kontingen beserta info PIC dan jumlah anggota.
     */
    public function index()
    {
        $contingents = Contingent::with(['pic:id,name,email'])
            ->withCount('players')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $contingents,
        ]);
    }

    /**
     * GET /contingents/{id}
     * Detail satu kontingen beserta daftar player-nya.
     */
    public function show($id)
    {
        $contingent = Contingent::with([
            'pic:id,name,email',
            'players:id,contingent_id,name,nim_nip,sport_id,sport_category_id,photo_path,verification_status',
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $contingent,
        ]);
    }

    // ----------------------------------------------------------------
    // PANITIA / ADMIN — CRUD KONTINGEN
    // ----------------------------------------------------------------

    /**
     * POST /contingents
     * Buat kontingen baru. Body: { "name": "Fakultas Informatika" }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:contingents,name',
        ]);

        $contingent = Contingent::create(['name' => $validated['name']]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kontingen berhasil dibuat.',
            'data'    => $contingent,
        ], 201);
    }

    /**
     * PUT /contingents/{id}
     * Update nama kontingen.
     */
    public function update(Request $request, $id)
    {
        $contingent = Contingent::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:contingents,name,' . $contingent->id,
        ]);

        $contingent->update(['name' => $validated['name']]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kontingen berhasil diperbarui.',
            'data'    => $contingent->fresh()->load('pic:id,name,email'),
        ]);
    }

    /**
     * DELETE /contingents/{id}
     * Hapus kontingen. Player yang terhubung akan memiliki contingent_id = null.
     */
    public function destroy($id)
    {
        $contingent = Contingent::findOrFail($id);
        $contingent->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Kontingen berhasil dihapus.',
        ]);
    }

    /**
     * PUT /contingents/{id}/assign-pic
     * Panitia menugaskan user sebagai PIC dari kontingen ini.
     * Body: { "user_id": 5 }
     *
     * Sekaligus mengubah role user menjadi pic_kontingen (jika belum)
     * dan memasukkan player-nya ke dalam kontingen ini.
     */
    public function assignPic(Request $request, $id)
    {
        $contingent = Contingent::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($user->role === 'panitia' || $user->role === 'admin') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak dapat menugaskan panitia atau admin sebagai PIC kontingen.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Ubah role menjadi pic_kontingen
            $user->update(['role' => 'pic_kontingen']);

            // Daftarkan sebagai PIC di kontingen
            $contingent->update(['pic_user_id' => $user->id]);

            // Masukkan player-nya ke kontingen ini (jika punya record player)
            if ($user->player) {
                $user->player->update(['contingent_id' => $contingent->id]);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "{$user->name} berhasil ditugaskan sebagai PIC kontingen {$contingent->name}.",
                'data'    => $contingent->fresh()->load('pic:id,name,email'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menugaskan PIC: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ----------------------------------------------------------------
    // PIC KONTINGEN — KELOLA ANGGOTA KONTINGENNYA SENDIRI
    // ----------------------------------------------------------------

    /**
     * GET /contingents/my
     * PIC melihat detail kontingennya sendiri beserta semua anggota.
     */
    public function myContingent(Request $request)
    {
        $contingent = $request->user()->managedContingent;

        if (!$contingent) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda belum ditugaskan sebagai PIC kontingen manapun.',
            ], 404);
        }

        $contingent->load([
            'players:id,contingent_id,name,nim_nip,sport_id,sport_category_id,photo_path,verification_status',
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => $contingent,
        ]);
    }

    /**
     * POST /contingents/my/players
     * PIC menambahkan player ke kontingennya.
     * Body: { "player_id": 7 }
     */
    public function addPlayer(Request $request)
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
            'player_id' => 'required|integer|exists:players,id',
        ]);

        $player = Player::findOrFail($validated['player_id']);

        if ($player->contingent_id && $player->contingent_id !== $contingent->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Player ini sudah terdaftar di kontingen lain.',
            ], 422);
        }

        $player->update(['contingent_id' => $contingent->id]);

        return response()->json([
            'status'  => 'success',
            'message' => "{$player->name} berhasil ditambahkan ke kontingen {$contingent->name}.",
            'data'    => $player->fresh(['contingent']),
        ]);
    }

    /**
     * DELETE /contingents/my/players/{player_id}
     * PIC mengeluarkan player dari kontingennya.
     */
    public function removePlayer(Request $request, $playerId)
    {
        $user       = $request->user();
        $contingent = $user->managedContingent;

        if (!$contingent) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda belum ditugaskan sebagai PIC kontingen manapun.',
            ], 403);
        }

        $player = Player::findOrFail($playerId);

        if ($player->contingent_id !== $contingent->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Player ini bukan anggota kontingen Anda.',
            ], 403);
        }

        // Jangan keluarkan PIC dari kontingennya sendiri
        if ($player->user_id === $user->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'PIC tidak dapat dikeluarkan dari kontingen melalui endpoint ini.',
            ], 422);
        }

        $player->update(['contingent_id' => null]);

        return response()->json([
            'status'  => 'success',
            'message' => "{$player->name} berhasil dikeluarkan dari kontingen {$contingent->name}.",
        ]);
    }
}
