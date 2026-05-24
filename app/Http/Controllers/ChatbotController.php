<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ChatbotController extends Controller
{
    #[OA\Post(
        path: "/chatbot/ask",
        operationId: "chatbotAsk",
        tags: ["Chatbot"],
        summary: "Chatbot Helpdesk",
        description: "Endpoint untuk tanya jawab otomatis terkait Tel-U Cup."
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["question"],
            properties: [
                new OA\Property(property: "question", type: "string", example: "bagaimana aturan pertandingan?"),
                new OA\Property(property: "user_risk", type: "string", example: "low")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil mendapatkan jawaban",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "assistant_name", type: "string"),
                new OA\Property(property: "support_type", type: "string"),
                new OA\Property(property: "answer", type: "string"),
                new OA\Property(property: "options", type: "array", items: new OA\Items(type: "string"))
            ]
        )
    )]
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
