<?php

namespace App\Jobs;

use App\Models\EventPhoto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ProcessEventPhoto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $eventPhoto;
    public $tries = 5;
    public $backoff = [10, 30, 60, 120, 300];
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(EventPhoto $eventPhoto)
    {
        $this->eventPhoto = $eventPhoto;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fastApiBaseUrl = rtrim(config('services.fastapi.url', 'http://127.0.0.1:8001'), '/');
        $fastApiUrl = $fastApiBaseUrl . '/api/process-photo';

        $this->eventPhoto->update([
            'ai_status' => 'processing',
        ]);

        $optimizedUrl = $this->optimizedImageUrl($this->eventPhoto->image_url);

        $response = Http::timeout($this->timeout)->post($fastApiUrl, [
            'event_photo_id' => $this->eventPhoto->id,
            'image_url'      => $optimizedUrl,
        ]);

        if (!$response->successful()) {
            Log::warning("Gagal memproses EventPhoto ID {$this->eventPhoto->id} di FastAPI. Status: {$response->status()}. Body: {$response->body()}");

            throw new RuntimeException("FastAPI process-photo gagal dengan status {$response->status()}.");
        }

        $responseData = $response->json();

        $this->eventPhoto->update([
            'ai_status'       => 'completed',
            'faces_detected'  => $this->facesDetectedFromResponse(is_array($responseData) ? $responseData : []),
            'ai_processed_at' => now(),
        ]);

        Log::info("Berhasil memproses EventPhoto ID {$this->eventPhoto->id} melalui FastAPI.");
    }

    public function failed(Throwable $exception): void
    {
        EventPhoto::whereKey($this->eventPhoto->id)->update([
            'ai_status' => 'failed',
        ]);

        Log::error("ProcessEventPhoto FINAL FAIL untuk ID {$this->eventPhoto->id}: " . $exception->getMessage());
    }

    private function optimizedImageUrl(string $imageUrl): string
    {
        if (!str_contains($imageUrl, '/upload/')) {
            return $imageUrl;
        }

        if (str_contains($imageUrl, '/upload/w_2048,c_limit,q_95,f_jpg/')) {
            return $imageUrl;
        }

        return str_replace('/upload/', '/upload/w_2048,c_limit,q_95,f_jpg/', $imageUrl);
    }

    private function facesDetectedFromResponse(?array $responseData): int
    {
        $responseData ??= [];

        if (isset($responseData['data']) && is_array($responseData['data'])) {
            $nestedCount = $this->facesDetectedFromResponse($responseData['data']);

            if ($nestedCount > 0) {
                return $nestedCount;
            }
        }

        foreach (['faces_detected', 'face_count', 'faces_count', 'detected_faces_count'] as $key) {
            if (isset($responseData[$key]) && is_numeric($responseData[$key])) {
                return (int) $responseData[$key];
            }
        }

        if (isset($responseData['faces']) && is_array($responseData['faces'])) {
            return count($responseData['faces']);
        }

        return $this->eventPhoto->photoFaces()->count();
    }
}
