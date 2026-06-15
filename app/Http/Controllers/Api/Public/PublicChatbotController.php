<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PublicChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $startedAt = microtime(true);

        try {
            $validated = $request->validate([
                'question' => 'required|string|min:3|max:500',
            ], [
                'question.required' => 'Pertanyaan wajib diisi.',
                'question.string' => 'Pertanyaan harus berupa teks.',
                'question.min' => 'Pertanyaan minimal terdiri dari 3 karakter.',
                'question.max' => 'Pertanyaan maksimal 500 karakter.',
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-Internal-Secret' => config('services.chatbot.internal_secret'),
                    'Accept' => 'application/json',
                ])
                ->post(rtrim(config('services.chatbot.ai_service_url'), '/') . '/api/chatbot/ask', [
                    'question' => $validated['question'],
                    'ip' => $request->ip(),
                ]);

            $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

            Log::info('Public chatbot request proxied', [
                'ip' => $request->ip(),
                'status_code' => $response->status(),
                'latency_ms' => $latencyMs,
                'question' => str($validated['question'])->limit(160)->toString(),
            ]);

            if ($response->successful()) {
                return response()->json($response->json(), $response->status());
            }
        } catch (\Throwable $exception) {
            $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);

            Log::warning('Public chatbot request failed', [
                'ip' => $request->ip(),
                'latency_ms' => $latencyMs,
                'question' => str($validated['question'])->limit(160)->toString(),
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'AI Assistant sedang tidak tersedia. Silakan coba beberapa saat lagi.',
        ], 503);
    }
}
