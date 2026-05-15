<?php

namespace App\Http\Controllers;

use App\Models\SelfAssessment;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /**
     * @OA\Get(
     *      path="/campaign",
     *      operationId="getCampaign",
     *      tags={"Campaign"},
     *      summary="Menampilkan konten kampanye Safety & HEI",
     *      description="Mengambil konten kampanye terkait budaya dan keselamatan.",
     *      @OA\Response(
     *          response=200,
     *          description="Sukses mengambil data kampanye"
     *      )
     * )
     * FR-03.1 & FR-03.2: Menampilkan konten kampanye Safety & HEI
     */
    public function show()
    {
        return response()->json([
            'title' => 'Utamakan Keselamatan & Sportivitas',
            'subtitle' => 'Kampanye Budaya & Keselamatan',
            'values' => [
                'harmony' => [
                    'title' => 'HARMONY: KOLABORASI TANPA BATAS',
                    'description' => 'Membangun lingkungan yang inklusif dan saling menghargai untuk mencapai tujuan bersama.'
                ],
            ],
            'checkpoints' => [
                ['icon' => 'medical', 'text' => 'Cek Kesiapan Medis'],
                ['icon' => 'sports', 'text' => 'Prioritaskan Sportivitas'],
                ['icon' => 'injury', 'text' => 'Laporkan Cedera'],
            ],
            'show_again_option' => true
        ]);
    }

    /**
     * @OA\Post(
     *      path="/campaign/confirm",
     *      operationId="confirmCampaign",
     *      tags={"Campaign"},
     *      summary="Konfirmasi baca kampanye & Sesi",
     *      description="Menyimpan konfirmasi bahwa pengguna telah membaca kampanye.",
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\JsonContent(
     *              @OA\Property(property="dont_show_again", type="boolean", example=true)
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Konfirmasi diterima"
     *      )
     * )
     * FR-03.3: Konfirmasi baca kampanye & Sesi
     */
    public function confirm(Request $request)
    {
        if ($request->dont_show_again) {
            session(['hide_campaign' => true]);
        }

        return response()->json(['message' => 'Konfirmasi diterima, lanjut ke dashboard.']);
    }
}
