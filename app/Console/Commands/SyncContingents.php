<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Contingent;

class SyncContingents extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sync:contingents';

    /**
     * The console command description.
     */
    protected $description = 'Sync contingent data from official Tel-U API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sync from Telkom University API...');

        $url = 'https://telucup.telkomuniversity.ac.id/contingents/U2FsdGVkX1/JEp7sFTcwx5Vs2C8qtkEAaheQv8Q/Pa4=';
        
        try {
            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                // Asumsi data berupa array of objects
                // Misal: [{"id": 1, "name": "Fakultas Informatika"}, ...]
                foreach ($data as $item) {
                    Contingent::updateOrCreate(
                        ['external_id' => $item['id'] ?? $item['name']],
                        [
                            'name' => $item['name'],
                            'faculty_name' => $item['faculty'] ?? null
                        ]
                    );
                }

                $this->info('Sync complete! Database updated.');
            } else {
                $this->error('Failed to fetch data from API.');
            }
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }
}
