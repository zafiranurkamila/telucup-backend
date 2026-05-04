<?php

namespace App\Http\Controllers;

use App\Models\SelfAssessment;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /**
     * FR-03.1 & FR-03.2: Menampilkan konten kampanye Safety & HEI
     */
    public function show()
    {
        return response()->json([
            'title' => 'Kampanye Safety Sport & Budaya HEI',
            'content' => [
                'safety' => 'Selalu gunakan perlengkapan pelindung dan lakukan pemanasan.',
                'fairness' => 'Junjung tinggi sportivitas dan kejujuran dalam pertandingan.',
                'hei_values' => 'Harmony, Excellence, Integrity (HEI) adalah jiwa dari Tel-U Cup.',
            ],
            'action' => 'User wajib menekan tombol konfirmasi untuk melanjutkan.'
        ]);
    }

    /**
     * FR-03.3: Konfirmasi baca kampanye
     */
    public function confirm(Request $request)
    {
        // Logika sederhana: simpan ke session atau database bahwa user sudah baca
        session(['campaign_read' => true]);

        return response()->json(['message' => 'Terima kasih telah membaca kampanye keselamatan!']);
    }
}
