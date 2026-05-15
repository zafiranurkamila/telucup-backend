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

class ProcessEventPhoto implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $eventPhoto;

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
        // Asumsi: URL FastAPI Anda berjalan di port 8000
        $fastApiUrl = env('FASTAPI_URL', 'http://127.0.0.1:8000/api/process-photo');

        try {
            // Mengirim request ke FastAPI
            // Kita mengirimkan ID foto dan URL Cloudinary-nya
            $response = Http::timeout(60)->post($fastApiUrl, [
                'event_photo_id' => $this->eventPhoto->id,
                'image_url'      => $this->eventPhoto->image_url,
            ]);

            if ($response->successful()) {
                Log::info("Berhasil mengirim EventPhoto ID {$this->eventPhoto->id} ke FastAPI.");
            } else {
                Log::error("Gagal mengirim EventPhoto ID {$this->eventPhoto->id} ke FastAPI. Status: " . $response->status());
                // Anda bisa mengaktifkan mekanisme retry di sini jika diperlukan
                $this->release(10); // Coba lagi dalam 10 detik
            }
        } catch (\Exception $e) {
            Log::error("Koneksi ke FastAPI terputus: " . $e->getMessage());
            $this->release(10); 
        }
    }
}