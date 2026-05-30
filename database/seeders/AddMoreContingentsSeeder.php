<?php

namespace Database\Seeders;

use App\Models\Contingent;
use App\Models\Player;
use App\Models\Registration;
use App\Models\SelfAssessment;
use App\Models\SportCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder tambahan — tidak menghapus data lama.
 * Menambahkan 4 kontingen baru beserta player, self-assessment, dan registrasi.
 */
class AddMoreContingentsSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('@Pamelo04');

        // Deteksi indeks player terakhir dari pola email player###@telucup.com
        $lastIndex = User::where('email', 'like', 'player%@telucup.com')
            ->pluck('email')
            ->map(fn ($e) => (int) filter_var($e, FILTER_SANITIZE_NUMBER_INT))
            ->max();

        $globalPlayerIndex = $lastIndex + 1;

        // Ambil semua sport categories yang sudah ada (buat referensi registrasi)
        $allSportCategories = SportCategory::with('sport')->get();

        if ($allSportCategories->isEmpty()) {
            $this->command->error('Tidak ada sport categories! Jalankan DummyDataSeeder terlebih dahulu.');
            return;
        }

        $contingentDefs  = $this->defineNewContingents();
        $playerNameSets  = $this->definePlayerNames();

        $employeeStatuses = [
            'TPA PEGAWAI TETAP',
            'TPA PEGAWAI KONTRAK',
            'DOSEN TETAP',
            'TENAGA KEPENDIDIKAN',
        ];
        $workLocations = [
            'URUSAN PENCATATAN DAN PENGELOLAAN ASET',
            'BAGIAN KEUANGAN DAN PERENCANAAN',
            'BAGIAN SUMBER DAYA MANUSIA',
            'BAGIAN AKADEMIK DAN KEMAHASISWAAN',
            'BAGIAN UMUM DAN KERUMAHTANGGAAN',
            'URUSAN KERJASAMA DAN HUMAS',
            'BAGIAN TEKNOLOGI INFORMASI',
        ];

        $createdSummary = [];

        foreach ($contingentDefs as $ci => $contingentDef) {
            // 1. User PIC Kontingen
            $picUser = User::create([
                'name'        => $contingentDef['pic_name'],
                'email'       => $contingentDef['pic_email'],
                'password'    => $password,
                'role'        => 'pic_kontingen',
                'is_kacamata' => false,
            ]);

            // 2. Kontingen
            $contingent = Contingent::create([
                'name'        => $contingentDef['name'],
                'pic_user_id' => $picUser->id,
            ]);

            // 3. Buat 15 player per kontingen
            $players        = [];
            $isKacamataArr  = [];

            foreach ($playerNameSets[$ci] as $idx => $playerName) {
                $nim        = '20' . str_pad($ci + 1, 2, '0', STR_PAD_LEFT) . str_pad($globalPlayerIndex, 6, '0', STR_PAD_LEFT);
                $isKacamata = ($idx % 4 === 0);

                $playerUser = User::create([
                    'name'        => $playerName,
                    'email'       => 'player' . str_pad($globalPlayerIndex, 3, '0', STR_PAD_LEFT) . '@telucup.com',
                    'password'    => $password,
                    'role'        => 'player',
                    'is_kacamata' => $isKacamata,
                ]);

                $player = Player::create([
                    'user_id'         => $playerUser->id,
                    'name'            => $playerName,
                    'nim_nip'         => $nim,
                    'contingent_id'   => $contingent->id,
                    'risk_lvl'        => 'not_yet',
                    'employee_status' => $employeeStatuses[$idx % count($employeeStatuses)],
                    'work_location'   => $workLocations[$idx % count($workLocations)],
                ]);

                $players[]           = $player;
                $isKacamataArr[$idx] = $isKacamata;
                $globalPlayerIndex++;
            }

            // 4. Self-assessment untuk 80% player (12 dari 15)
            foreach (array_slice($players, 0, 12) as $idx => $player) {
                $riskLabel  = $this->pickRiskLabel($idx);
                $totalScore = $this->scoreForRisk($riskLabel);
                $cvScore    = round($totalScore * 0.25, 1);
                $mskScore   = round($totalScore * 0.30, 1);
                $arScore    = round($totalScore * 0.25, 1);
                $psyScore   = round($totalScore - $cvScore - $mskScore - $arScore, 1);

                SelfAssessment::create([
                    'player_id'             => $player->id,
                    'sport_branch_snapshot' => 'Futsal Putra',
                    'age_snapshot'          => rand(22, 50),
                    'bmi_snapshot'          => round(rand(190, 290) / 10, 1),
                    'is_kacamata_snapshot'  => $isKacamataArr[$idx],
                    'questionnaire_version' => '1.0',
                    'algorithm_version'     => '1.0',
                    'injury_history'        => $riskLabel !== 'low'
                        ? 'Pernah cedera pada bagian ' . ['bahu', 'siku', 'pergelangan tangan'][rand(0, 2)]
                        : null,
                    'injury_location'       => $riskLabel !== 'low'
                        ? ['Bahu kanan', 'Siku kiri', 'Pergelangan tangan kanan'][rand(0, 2)]
                        : null,
                    'current_condition'     => $riskLabel === 'low'
                        ? ['Baik', 'Cukup baik'][rand(0, 1)]
                        : ['Sedikit tidak nyaman', 'Agak sakit', 'Kurang fit'][rand(0, 2)],
                    'pain_score'            => match ($riskLabel) {
                        'low'    => rand(0, 2),
                        'medium' => rand(2, 5),
                        'high'   => rand(5, 8),
                    },
                    'form_responses'        => $this->fakeFormResponses($riskLabel),
                    'score_breakdown'       => [
                        'cardiovascular'  => $cvScore,
                        'musculoskeletal' => $mskScore,
                        'acute_readiness' => $arScore,
                        'psychosocial'    => $psyScore,
                    ],
                    'score_cardiovascular'  => $cvScore,
                    'score_musculoskeletal' => $mskScore,
                    'score_acute_readiness' => $arScore,
                    'score_psychosocial'    => $psyScore,
                    'total_score'           => $totalScore,
                    'red_flags'             => $riskLabel === 'high'
                        ? ['Riwayat cedera serius dalam 3 bulan terakhir']
                        : [],
                    'yellow_flags'          => $riskLabel !== 'low'
                        ? $this->pickYellowFlags()
                        : [],
                    'risk_label'            => $riskLabel,
                    'recommendation'        => $this->recommendationFor($riskLabel),
                    'panitia_summary'       => 'Telah diverifikasi oleh tim medis Telucup 2026.',
                    'requires_clearance'    => $riskLabel === 'high',
                    'confidence_score'      => round(rand(72, 98) / 100, 2),
                    'valid_until'           => Carbon::now()->addMonths(3),
                    'is_allowed_to_play'    => $riskLabel !== 'high',
                    'reviewed_at'           => Carbon::now()->subDays(rand(1, 20)),
                    'pic_confirmed'         => true,
                ]);

                $player->update(['risk_lvl' => $riskLabel]);
            }

            // 5. Registrasi ke semua sport categories (verified)
            foreach ($allSportCategories as $category) {
                $registration = Registration::create([
                    'contingent_id'     => $contingent->id,
                    'sport_id'          => $category->sport_id,
                    'sport_category_id' => $category->id,
                    'status'            => 'verified',
                ]);

                $maxMembers  = $category->max_members ?? 1;
                $assignCount = min($maxMembers, count($players));
                $pivotRows   = [];

                foreach (array_slice($players, 0, $assignCount) as $player) {
                    $pivotRows[] = [
                        'registration_id' => $registration->id,
                        'player_id'       => $player->id,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }

                DB::table('registration_player')->insert($pivotRows);
            }

            $createdSummary[] = [
                'contingent' => $contingent->name,
                'pic'        => $contingentDef['pic_email'],
                'players'    => count($players),
            ];
        }

        $endIndex = $globalPlayerIndex - 1;

        $this->command->info('');
        $this->command->info('✓ AddMoreContingentsSeeder berhasil!');
        foreach ($createdSummary as $s) {
            $this->command->info("  + {$s['contingent']} | PIC: {$s['pic']} | {$s['players']} players");
        }
        $this->command->info("  Player baru: player" . str_pad($lastIndex + 1, 3, '0', STR_PAD_LEFT) . "–player" . str_pad($endIndex, 3, '0', STR_PAD_LEFT) . " @telucup.com");
        $this->command->info('  Masing-masing: 12 sudah self-assessment, 3 belum');
        $this->command->info('  Semua registrasi status: verified');
        $this->command->info('  Password: @Pamelo04');
    }

    // ──────────────────────────────────────────────
    // Definisi data
    // ──────────────────────────────────────────────

    private function defineNewContingents(): array
    {
        return [
            ['name' => 'Fakultas Ilmu Terapan',            'pic_name' => 'Hendri Kusnandar',     'pic_email' => 'pic.fit@telucup.com'],
            ['name' => 'Sekolah Tinggi Manajemen Bisnis',  'pic_name' => 'Astrid Permatasari',   'pic_email' => 'pic.stmb@telucup.com'],
            ['name' => 'Direktorat Sumber Daya',           'pic_name' => 'Bambang Suryanto',     'pic_email' => 'pic.dsd@telucup.com'],
            ['name' => 'Unit Pelaksana Teknis Olahraga',   'pic_name' => 'Rina Marliana',        'pic_email' => 'pic.upto@telucup.com'],
        ];
    }

    private function definePlayerNames(): array
    {
        return [
            // FIT – 15 player
            [
                'Agus Setiawan', 'Bagas Nugroho', 'Catur Wibowo', 'Dwi Prasetyo', 'Endra Cahyono',
                'Ferdy Gunawan', 'Gilang Ramadhan', 'Hafizh Maulana', 'Iqbal Fauzan', 'Jaya Kusuma',
                'Annisa Rahmawati', 'Bella Fitriani', 'Clara Magdalena', 'Dita Puspita', 'Evita Sari',
            ],
            // STMB – 15 player
            [
                'Kevin Pratama', 'Leo Santoso', 'Miko Hernawan', 'Novan Saputra', 'Oki Firmansyah',
                'Prima Haryadi', 'Qobil Anwar', 'Ryan Hidayat', 'Setyo Budi', 'Tedy Kurniawan',
                'Farah Aulia', 'Gina Permata', 'Hesti Wulandari', 'Ika Nurhaliza', 'Jasmine Putri',
            ],
            // DSD – 15 player
            [
                'Ujang Rohman', 'Viktor Silaen', 'Wahid Hakim', 'Xaverius Nanda', 'Yogi Prakoso',
                'Zulfahmi Akbar', 'Anton Subekti', 'Benny Susanto', 'Ciko Pradipta', 'Deny Wijaksono',
                'Kartika Sari', 'Layla Nabila', 'Mirna Yuliana', 'Nova Anggraeni', 'Oci Susanti',
            ],
            // UPTO – 15 player
            [
                'Edi Priyanto', 'Feri Handoko', 'Ganda Saputra', 'Heru Wibowo', 'Iwan Setiadi',
                'Jefri Lubis', 'Kukuh Prabowo', 'Lutfi Hamdani', 'Mamat Riyadi', 'Nanang Cahyadi',
                'Pipit Rahayu', 'Reni Oktavia', 'Susi Lestari', 'Titin Sumarni', 'Uun Ratnawati',
            ],
        ];
    }

    // ──────────────────────────────────────────────
    // Helpers (sama dengan DummyDataSeeder)
    // ──────────────────────────────────────────────

    private function pickRiskLabel(int $idx): string
    {
        if ($idx < 6)  return 'low';
        if ($idx < 10) return 'medium';
        return 'high';
    }

    private function scoreForRisk(string $risk): float
    {
        return (float) match ($risk) {
            'low'    => rand(10, 30),
            'medium' => rand(31, 60),
            'high'   => rand(61, 90),
        };
    }

    private function fakeFormResponses(string $risk): array
    {
        $base = match ($risk) {
            'low'    => 0,
            'medium' => 1,
            'high'   => 2,
        };

        return [
            'q1_aktivitas_fisik'  => rand($base, min($base + 1, 3)),
            'q2_riwayat_cedera'   => rand($base, min($base + 1, 3)),
            'q3_kondisi_saat_ini' => rand(0, min($base + 1, 3)),
            'q4_nyeri'            => rand(0, min($base + 2, 3)),
            'q5_kardiovaskular'   => rand(0, min($base + 1, 3)),
            'q6_kebugaran'        => rand(0, min($base + 1, 3)),
            'q7_tidur'            => rand(0, min($base + 1, 3)),
            'q8_stress'           => rand(0, min($base + 1, 3)),
        ];
    }

    private function pickYellowFlags(): array
    {
        $options = [
            'Nyeri saat aktivitas berat',
            'Pernah konsultasi dokter dalam 6 bulan terakhir',
        ];
        return rand(0, 1) ? $options : [$options[0]];
    }

    private function recommendationFor(string $risk): string
    {
        return match ($risk) {
            'low'    => 'Peserta dapat mengikuti seluruh pertandingan tanpa pembatasan khusus. Tetap lakukan pemanasan yang cukup sebelum bertanding.',
            'medium' => 'Peserta direkomendasikan melakukan pemanasan ekstra minimal 15 menit dan segera melapor ke tim medis jika merasakan ketidaknyamanan.',
            'high'   => 'Peserta memerlukan medical clearance dari tim dokter Telucup sebelum diperbolehkan bertanding. Harap menghubungi panitia medis.',
        };
    }
}
