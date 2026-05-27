<?php

namespace App\Http\Controllers;

use App\Models\SelfAssessment;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CampaignController extends Controller
{
    #[OA\Get(
        path: "/campaign",
        operationId: "getCampaign",
        tags: ["Campaign"],
        summary: "Menampilkan konten kampanye Safety & HEI",
        description: "Mengambil konten kampanye terkait budaya dan keselamatan."
    )]
    #[OA\Response(
        response: 200,
        description: "Sukses mengambil data kampanye",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "title",    type: "string", example: "Utamakan Keselamatan & Sportivitas"),
                new OA\Property(property: "subtitle", type: "string", example: "Kampanye Budaya & Keselamatan"),
                new OA\Property(property: "values",   type: "object",
                    properties: [
                        new OA\Property(property: "harmony", type: "object",
                            properties: [
                                new OA\Property(property: "title",       type: "string"),
                                new OA\Property(property: "description", type: "string"),
                            ]
                        ),
                    ]
                ),
                new OA\Property(property: "checkpoints", type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "icon", type: "string"),
                            new OA\Property(property: "text", type: "string"),
                        ]
                    )
                ),
                new OA\Property(property: "show_again_option", type: "boolean", example: true),
            ]
        )
    )]
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

    #[OA\Post(
        path: "/campaign/confirm",
        operationId: "confirmCampaign",
        tags: ["Campaign"],
        summary: "Konfirmasi baca kampanye & Sesi",
        description: "Menyimpan konfirmasi bahwa pengguna telah membaca kampanye."
    )]
    #[OA\RequestBody(
        required: false,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "dont_show_again", type: "boolean", example: true)
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Konfirmasi diterima",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "Konfirmasi diterima, lanjut ke dashboard."),
            ]
        )
    )]
    public function confirm(Request $request)
    {
        if ($request->dont_show_again) {
            session(['hide_campaign' => true]);
        }

        return response()->json(['message' => 'Konfirmasi diterima, lanjut ke dashboard.']);
    }
}
