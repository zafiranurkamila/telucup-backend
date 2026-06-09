<?php

namespace App\Console\Commands;

use App\Jobs\ProcessEventPhoto;
use App\Models\EventPhoto;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ReprocessPhotos extends Command
{
    protected $signature = 'photos:reprocess
        {--from-id= : Reprocess photos with ID greater than or equal to this value}
        {--to-id= : Reprocess photos with ID less than or equal to this value}
        {--after= : Reprocess photos created on or after this date (YYYY-MM-DD)}';

    protected $description = 'Delete old face detections and dispatch AI processing jobs for event photos';

    public function handle(): int
    {
        $query = EventPhoto::query()->orderBy('id');

        if ($fromId = $this->option('from-id')) {
            $query->where('id', '>=', (int) $fromId);
        }

        if ($toId = $this->option('to-id')) {
            $query->where('id', '<=', (int) $toId);
        }

        if ($after = $this->option('after')) {
            try {
                $query->where('created_at', '>=', Carbon::parse($after)->startOfDay());
            } catch (\Throwable) {
                $this->error('Option --after harus berisi tanggal yang valid, misalnya 2026-06-01.');

                return self::FAILURE;
            }
        }

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('Tidak ada foto yang cocok untuk diproses ulang.');

            return self::SUCCESS;
        }

        $this->info("Menjadwalkan reprocess untuk {$total} foto event...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunkById(100, function ($photos) use ($bar): void {
            foreach ($photos as $photo) {
                $photo->photoFaces()->delete();
                $photo->update([
                    'ai_status'       => 'pending',
                    'faces_detected'  => null,
                    'ai_processed_at' => null,
                ]);

                ProcessEventPhoto::dispatch($photo);

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info('Semua job reprocess sudah dikirim ke queue.');

        return self::SUCCESS;
    }
}
