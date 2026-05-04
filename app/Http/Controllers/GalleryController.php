<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * Menampilkan semua foto atau mencari berdasarkan tag (Nomor Punggung/Nama)
     * Ini disiapkan untuk integrasi AI Face Recognition
     */
    public function index(Request $request)
    {
        $tag = $request->query('search'); // Misal: search=nomor_10 atau search=Budi

        // Simulasi pencarian foto yang sudah di-tag oleh AI
        return response()->json([
            'message' => $tag ? "Menampilkan foto untuk: $tag" : "Menampilkan semua foto",
            'photos' => [
                ['url' => '/storage/gallery/photo1.jpg', 'tags' => ['Budi', 'nomor_10']],
                ['url' => '/storage/gallery/photo2.jpg', 'tags' => ['Ani', 'nomor_05']],
            ]
        ]);
    }

    /**
     * Endpoint untuk teman AI Anda mengunggah hasil tagging
     */
    public function updateTags(Request $request, $photoId)
    {
        // Logika untuk menyimpan hasil deteksi AI ke database
        return response()->json(['message' => 'Tags berhasil diperbarui oleh AI!']);
    }
}
