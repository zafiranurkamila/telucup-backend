<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    /**
     * Endpoint untuk tanya jawab otomatis (Chatbot Helpdesk)
     */
    public function ask(Request $request)
    {
        $question = strtolower($request->question);
        $answer = "Maaf, saya belum mengerti pertanyaan Anda. Silakan hubungi panitia di sekretariat.";

        // Simulasi basis data tata tertib (Knowledge Base)
        if (str_contains($question, 'syarat') || str_contains($question, 'daftar')) {
            $answer = "Syarat pendaftaran adalah mahasiswa/pegawai aktif Telkom University dan mengisi form self-assessment kesehatan.";
        } elseif (str_contains($question, 'jadwal') || str_contains($question, 'kapan')) {
            $answer = "Jadwal pertandingan dapat Anda lihat pada menu 'Bracket' di halaman utama.";
        } elseif (str_contains($question, 'lokasi') || str_contains($question, 'tempat')) {
            $answer = "Pertandingan dilaksanakan di Gedung Olahraga (GOR) dan Lapangan Tenis Telkom University.";
        }

        return response()->json([
            'question' => $request->question,
            'answer' => $answer
        ]);
    }
}
