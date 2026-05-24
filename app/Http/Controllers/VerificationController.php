<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * @OA\Get(
     *      path="/field/verification",
     *      operationId="getVerifications",
     *      tags={"Verification"},
     *      summary="Menampilkan daftar pemain dengan indikator warna",
     *      description="Mengambil daftar pemain beserta status self-assessment mereka untuk diverifikasi di lapangan.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Berhasil mengambil data pemain",
     *          @OA\JsonContent(
     *              @OA\Property(property="panitia_note", type="string"),
     *              @OA\Property(property="players", type="array", @OA\Items(type="object"))
     *          )
     *      )
     * )
     * FR-04.1 & FR-04.2: Menampilkan daftar pemain dengan indikator warna
     */
    public function index()
    {
        $players = Player::with('selfAssessment')->get()->map(function ($player) {
            return [
                'id' => $player->id,
                'name' => $player->name,
                'nim_nip' => $player->nim_nip,
                'sport' => $player->sport_branch,
                'risk_color' => $player->selfAssessment->risk_label ?? 'grey', // Merah, Kuning, Hijau
                'status' => $player->verification_status,
                'checked_in' => $player->checked_in_at ? true : false,
            ];
        });

        return response()->json([
            'panitia_note' => 'Warna Merah (High) wajib cek tim medis!',
            'players' => $players
        ]);
    }

    /**
     * @OA\Post(
     *      path="/field/checkin/{id}",
     *      operationId="checkInPlayer",
     *      tags={"Verification"},
     *      summary="Melakukan check-in pemain di lapangan",
     *      description="Menandai pemain sebagai sudah check-in dan terverifikasi di lapangan.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Pemain berhasil check-in",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string")
     *          )
     *      ),
     *      @OA\Response(response=404, description="Player not found")
     * )
     * FR-04.4: Melakukan check-in pemain di lapangan
     */
    public function checkIn($id)
    {
        $player = Player::findOrFail($id);
        $player->update([
            'checked_in_at' => now(),
            'verification_status' => 'verified'
        ]);

        return response()->json(['message' => "Pemain {$player->name} berhasil check-in!"]);
    }
}
