<?php

namespace Database\Seeders;

use App\Models\Contingent;
use App\Models\Player;
use App\Models\Registration;
use App\Models\SelfAssessment;
use App\Models\Sport;
use App\Models\SportCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('@Pamelo04');

        $this->clearExistingData();

        $allSportCategories = $this->createSports();
        $contingentDefs     = $this->defineContingents();
        $playerNameSets     = $this->definePlayerNames();

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

        $globalPlayerIndex = 1;

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
            $players       = [];
            $isKacamataArr = [];

            foreach ($playerNameSets[$ci] as $idx => $playerName) {
                $nim        = '19' . str_pad($ci + 1, 2, '0', STR_PAD_LEFT) . str_pad($globalPlayerIndex, 6, '0', STR_PAD_LEFT);
                $isKacamata = ($idx % 4 === 0); // ~25% pakai kacamata

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

                $players[]          = $player;
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
                    'sport_branch_snapshot' => 'Basket Putra',
                    'age_snapshot'          => rand(22, 50),
                    'bmi_snapshot'          => round(rand(190, 290) / 10, 1),
                    'is_kacamata_snapshot'  => $isKacamataArr[$idx],
                    'questionnaire_version' => '1.0',
                    'algorithm_version'     => '1.0',
                    'injury_history'        => $riskLabel !== 'low'
                        ? 'Pernah cedera pada bagian ' . ['lutut', 'pergelangan kaki', 'punggung bawah'][rand(0, 2)]
                        : null,
                    'injury_location'       => $riskLabel !== 'low'
                        ? ['Lutut kiri', 'Pergelangan kaki kanan', 'Punggung bawah'][rand(0, 2)]
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

            // 5. Registrasi ke semua cabang olahraga (status: verified)
            foreach ($allSportCategories as [$sportId, $category]) {
                $registration = Registration::create([
                    'contingent_id'     => $contingent->id,
                    'sport_id'          => $sportId,
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
        }

        $totalRegistrations = count($allSportCategories) * count($contingentDefs);

        $this->command->info('');
        $this->command->info('✓ DummyDataSeeder berhasil!');
        $this->command->info('  Kontingen : 5 (FTE, FIF, FEB, FKB, FIK)');
        $this->command->info('  PIC email : pic.fte / pic.fif / pic.feb / pic.fkb / pic.fik @telucup.com');
        $this->command->info('  Player    : 75 (player001–player075 @telucup.com)');
        $this->command->info('  Assesmen  : 60 player sudah mengisi (80%), 15 belum');
        $this->command->info("  Registrasi: {$totalRegistrations} (semua verified)");
        $this->command->info('  Password  : @Pamelo04 (semua akun player & pic)');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    private function clearExistingData(): void
    {
        $this->command->info('Membersihkan data lama...');
        DB::table('game_player_checkins')->delete();
        DB::table('games')->delete();
        DB::table('registration_player')->delete();
        DB::table('registrations')->delete();
        // self_assessments & face_embeddings akan cascade dari players
        DB::table('players')->delete();
        DB::table('contingents')->delete();
        DB::table('sport_categories')->delete();
        DB::table('sports')->delete();
        User::whereIn('role', ['player', 'pic_kontingen'])->delete();
    }

    /**
     * Buat 28 cabang olahraga + kategori.
     * Return: array of [sport_id, SportCategory]
     */
    private function createSports(): array
    {
        $config = [
            ['Basket Putra',                12,   [['Reguler', 12]]],
            ['Basket Putri',                12,   [['Reguler', 12]]],
            ['Futsal Putra',                7,    [['Reguler', 7]]],
            ['Futsal Putri',                7,    [['Reguler', 7]]],
            ['Voli Putra',                  8,    [['Reguler', 8]]],
            ['Voli Putri',                  8,    [['Reguler', 8]]],
            ['Bulu Tangkis Tunggal Putra',  1,    [['Reguler', 1]]],
            ['Bulu Tangkis Tunggal Putri',  1,    [['Reguler', 1]]],
            ['Bulu Tangkis Ganda Putra',    2,    [['Reguler', 2]]],
            ['Bulu Tangkis Ganda Putri',    2,    [['Reguler', 2]]],
            ['Bulu Tangkis Ganda Campuran', 2,    [['Reguler', 2]]],
            ['Tenis Meja Tunggal Putra',    1,    [['Reguler', 1]]],
            ['Tenis Meja Tunggal Putri',    1,    [['Reguler', 1]]],
            ['Tenis Meja Ganda Putra',      2,    [['Reguler', 2]]],
            ['Tenis Meja Ganda Putri',      2,    [['Reguler', 2]]],
            ['Tenis Meja Ganda Campuran',   2,    [['Reguler', 2]]],
            ['Tenis Lapangan Putra',        1,    [['Reguler', 1]]],
            ['Tenis Lapangan Putri',        1,    [['Reguler', 1]]],
            ['Catur',                       null, [['Putra', 1], ['Putri', 1]]],
            ['E-Sport (Mobile Legends)',    5,    [['Team', 5]]],
            ['E-Sport (PUBG Mobile)',       4,    [['Team', 4]]],
            ['E-Sport (Valorant)',          5,    [['Team', 5]]],
            ['E-Sport (FC 24 / FIFA)',      1,    [['Individu', 1]]],
            ['Atletik (Lari 100m)',         null, [['Putra', 1], ['Putri', 1]]],
            ['Atletik (Lari 400m)',         null, [['Putra', 1], ['Putri', 1]]],
            ['Renang',                      null, [['Putra', 1], ['Putri', 1]]],
            ['Panahan',                     null, [['Putra', 1], ['Putri', 1]]],
            ['Bridge',                      4,    [['Reguler', 4]]],
        ];

        $result = [];

        foreach ($config as [$sportName, $sportMax, $cats]) {
            $sport = Sport::create([
                'name'        => $sportName,
                'max_members' => $sportMax,
            ]);

            foreach ($cats as [$catName, $catMax]) {
                $category = SportCategory::create([
                    'sport_id'    => $sport->id,
                    'name'        => $catName,
                    'max_members' => $catMax,
                ]);
                $result[] = [$sport->id, $category];
            }
        }

        return $result;
    }

    private function defineContingents(): array
    {
        return [
            ['name' => 'Fakultas Teknik Elektro',        'pic_name' => 'Halida Nurul Asnia',   'pic_email' => 'pic.fte@telucup.com'],
            ['name' => 'Fakultas Informatika',           'pic_name' => 'Rendra Kusuma Putra',  'pic_email' => 'pic.fif@telucup.com'],
            ['name' => 'Fakultas Ekonomi Bisnis',        'pic_name' => 'Sari Wulandari',       'pic_email' => 'pic.feb@telucup.com'],
            ['name' => 'Fakultas Komunikasi dan Bisnis', 'pic_name' => 'Dimas Prasetyo',       'pic_email' => 'pic.fkb@telucup.com'],
            ['name' => 'Fakultas Industri Kreatif',      'pic_name' => 'Nabila Zahra Dewi',    'pic_email' => 'pic.fik@telucup.com'],
        ];
    }

    private function definePlayerNames(): array
    {
        return [
            // FTE – 15 player
            [
                'Ahmad Fauzi Pratama', 'Budi Santoso', 'Candra Wijaya', 'Dodi Permana', 'Eko Nugroho',
                'Fajar Maulana', 'Galih Wicaksono', 'Hendra Saputra', 'Irfan Hidayat', 'Joko Susilo',
                'Ayu Lestari', 'Bunga Pertiwi', 'Citra Dewi Anggraini', 'Dewi Rahayu', 'Eka Putri Utami',
            ],
            // FIF – 15 player
            [
                'Lukman Hakim', 'Mahfud Sidiq', 'Nanda Putra', 'Oktav Setiawan', 'Pandu Kristianto',
                'Qadri Fauzan', 'Rizky Ramadhan', 'Satria Abadi', 'Taufiq Hidayah', 'Umar Bakrie',
                'Fitri Handayani', 'Gita Nuraini', 'Hana Safira', 'Intan Permatasari', 'Jeni Ratnasari',
            ],
            // FEB – 15 player
            [
                'Vicky Prasetya', 'Wahyu Nugroho', 'Yusuf Arifin', 'Zaki Mubarok', 'Aldi Firmansyah',
                'Bayu Kurniawan', 'Chandra Putra', 'Dani Hermawan', 'Erfan Salim', 'Fandi Ahmad',
                'Karina Oktavia', 'Lestari Wulan', 'Mega Anggraini', 'Nadia Putri', 'Opal Safitri',
            ],
            // FKB – 15 player
            [
                'Guntur Wibowo', 'Haris Setiawan', 'Imam Santoso', 'Joni Prasetyo', 'Karel Sijabat',
                'Lanang Baskoro', 'Mario Ferdiansyah', 'Niko Firmansyah', 'Oscar Wahyudi', 'Panji Nugraha',
                'Putri Maharani', 'Qori Ananda', 'Ratna Sari Dewi', 'Sinta Permata', 'Tuti Handayani',
            ],
            // FIK – 15 player
            [
                'Raka Saputra', 'Surya Dinata', 'Tri Baskoro', 'Ulil Abshar', 'Vino Kurniawan',
                'Wisnu Wardana', 'Yandi Pratama', 'Zainal Arifin', 'Arief Hidayat', 'Bagus Setiawan',
                'Ulfa Nurhayati', 'Vivi Andriani', 'Wulan Sari', 'Xenia Maharani', 'Yuliana Dewi',
            ],
        ];
    }

    /** Distribusi risiko: 50% low, 30% medium, 20% high (dari 12 player per kontingen) */
    private function pickRiskLabel(int $idx): string
    {
        // idx 0-5 → low (6), 6-9 → medium (4), 10-11 → high (2)
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
