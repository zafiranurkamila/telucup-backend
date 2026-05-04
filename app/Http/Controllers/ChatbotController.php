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
        $risk = $request->user_risk ?? 'low'; // Konteks risiko user
        $answer = "Halo! Saya Smart Assistant Tel-U Cup. Ada yang bisa saya bantu?";

        // Logika Berdasarkan Menu di Gambar
        if (str_contains($question, 'hasil')) {
            $answer = "Hasil assessment Anda menunjukkan risiko $risk. Silakan cek menu 'Langkah Selanjutnya' di dashboard.";
        } elseif (str_contains($question, 'aturan') || str_contains($question, 'jadwal')) {
            $answer = "Aturan resmi dan jadwal pertandingan dapat diakses di menu 'Bagan'. Pastikan Anda hadir 30 menit sebelum jadwal.";
        } elseif (str_contains($question, 'medis') || str_contains($question, 'konsultasi')) {
            $answer = "Anda dapat melakukan konsultasi medis di booth kesehatan GOR Tel-U setiap jam operasional pertandingan.";
        } elseif (str_contains($question, 'teknis')) {
            $answer = "Untuk kendala teknis aplikasi, silakan hubungi tim PUTI melalui extension 1234.";
        }

        return response()->json([
            'assistant_name' => 'Smart Assistant',
            'support_type' => 'Tel-U Cup Dashboard Support',
            'answer' => $answer,
            'options' => [
                'Penjelasan hasil', 'Bantuan Form', 'Jadwal & Aturan', 'Pendaftaran', 'Dukungan Teknis', 'Konsultasi Medis', 'FAQ'
            ]
        ]);
    }
}
