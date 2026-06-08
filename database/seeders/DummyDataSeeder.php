<?php

namespace Database\Seeders;

use App\Models\Contingent;
use App\Models\Game;
use App\Models\Player;
use App\Models\Registration;
use App\Models\SelfAssessment;
use App\Models\Sport;
use App\Models\SportCategory;
use App\Models\Template;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DummyDataSeeder extends Seeder
{
    private const PASSWORD = 'rahasia123';
    private const PLAYERS_PER_CONTINGENT = 22;

    public function run(): void
    {
        $this->clearExistingData();

        Template::create(['year' => '2026', 'is_active' => true]);
        Template::create(['year' => '2025', 'is_active' => false]);

        $this->createCommitteeUsers();

        $sportCategories = $this->createSports();
        $contingents = $this->defineContingents();
        $registrationMap = [];
        $globalPlayerIndex = 1;

        foreach ($contingents as $contingentIndex => $contingentDef) {
            $picUser = User::create([
                'name' => $contingentDef['pic_name'],
                'email' => $contingentDef['pic_email'],
                'password' => Hash::make(self::PASSWORD),
                'role' => 'pic_kontingen',
                'is_kacamata' => false,
            ]);

            $contingent = Contingent::create([
                'name' => $contingentDef['name'],
                'pic_user_id' => $picUser->id,
                'cloudinary_public_id' => null,
                'image_url' => null,
            ]);

            $players = [];
            for ($playerOffset = 0; $playerOffset < self::PLAYERS_PER_CONTINGENT; $playerOffset++) {
                $player = $this->createPlayer($contingent, $contingentIndex, $playerOffset, $globalPlayerIndex);
                $assessment = $this->buildAssessmentPayload($player, $contingentDef, $playerOffset, $globalPlayerIndex);

                SelfAssessment::create($assessment);
                $player->update(['risk_lvl' => $assessment['risk_label']]);

                $players[] = $player;
                $globalPlayerIndex++;
            }

            foreach ($sportCategories as $entry) {
                $registration = Registration::create([
                    'contingent_id' => $contingent->id,
                    'sport_id' => $entry['sport']->id,
                    'sport_category_id' => $entry['category']->id,
                    'status' => 'verified',
                ]);

                $registration->players()->sync(
                    $this->pickPlayersForRegistration($players, $entry['category']->max_members, $entry['sequence'])
                );

                $registrationMap[$entry['key']][] = $registration;
            }
        }

        $this->createOpeningRoundGames($sportCategories, $registrationMap);

        $this->command->info('');
        $this->command->info('DummyDataSeeder selesai.');
        $this->command->info('Kontingen   : 14');
        $this->command->info('Player      : ' . (count($contingents) * self::PLAYERS_PER_CONTINGENT));
        $this->command->info('Self assess : lengkap untuk semua player');
        $this->command->info('Registrasi  : ' . (count($sportCategories) * count($contingents)) . ' verified');
        $this->command->info('Pertandingan: ' . (count($sportCategories) * 7) . ' jadwal ronde awal');
        $this->command->info('Password    : ' . self::PASSWORD . ' untuk semua akun');
    }

    private function clearExistingData(): void
    {
        $this->command->info('Membersihkan data lama...');

        Schema::disableForeignKeyConstraints();

        foreach ([
            'game_player_checkins',
            'games',
            'registration_player',
            'registrations',
            'self_assessments',
            'face_embeddings',
            'photo_faces',
            'event_photos',
            'gallery_folders',
            'sportsmanship_posters',
            'photos',
            'players',
            'contingents',
            'sport_categories',
            'sports',
            'templates',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        User::whereIn('role', ['panitia', 'pic_kontingen', 'player'])->delete();

        Schema::enableForeignKeyConstraints();
    }

    private function createCommitteeUsers(): void
    {
        foreach ([
            ['Panitia Utama Telucup', 'panitia@telucup.com'],
            ['Admin Medis Telucup', 'medis@telucup.com'],
            ['Operator Pertandingan', 'operator@telucup.com'],
        ] as [$name, $email]) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(self::PASSWORD),
                'role' => 'panitia',
                'is_kacamata' => false,
            ]);
        }
    }

    private function createSports(): array
    {
        $config = [
            ['Basket Putra', 12, [['Reguler', 12]], 'public/assets/home_original/logo_Basket.png'],
            ['Basket Putri', 12, [['Reguler', 12]], 'public/assets/home_original/logo_Basket.png'],
            ['Futsal Putra', 7, [['Reguler', 7]], 'public/assets/home_original/logo_Futsal.png'],
            ['Futsal Putri', 7, [['Reguler', 7]], 'public/assets/home_original/logo_Futsal.png'],
            ['Voli Putra', 8, [['Reguler', 8]], 'public/assets/home_original/logo_Voli.png'],
            ['Voli Putri', 8, [['Reguler', 8]], 'public/assets/home_original/logo_Voli.png'],
            ['Bulu Tangkis Tunggal Putra', null, [['Reguler', 1]], 'public/assets/home_original/logo_Bulutangkis.png'],
            ['Bulu Tangkis Tunggal Putri', null, [['Reguler', 1]], 'public/assets/home_original/logo_Bulutangkis.png'],
            ['Bulu Tangkis Ganda Putra', null, [['Reguler', 2]], 'public/assets/home_original/logo_Bulutangkis.png'],
            ['Bulu Tangkis Ganda Putri', null, [['Reguler', 2]], 'public/assets/home_original/logo_Bulutangkis.png'],
            ['Bulu Tangkis Ganda Campuran', null, [['Reguler', 2]], 'public/assets/home_original/logo_Bulutangkis.png'],
            ['Tenis Meja Tunggal Putra', null, [['Reguler', 1]], 'public/assets/home_original/logo_Tenis Meja.png'],
            ['Tenis Meja Tunggal Putri', null, [['Reguler', 1]], 'public/assets/home_original/logo_Tenis Meja.png'],
            ['Tenis Meja Ganda Putra', null, [['Reguler', 2]], 'public/assets/home_original/logo_Tenis Meja.png'],
            ['Tenis Meja Ganda Putri', null, [['Reguler', 2]], 'public/assets/home_original/logo_Tenis Meja.png'],
            ['Tenis Meja Ganda Campuran', null, [['Reguler', 2]], 'public/assets/home_original/logo_Tenis Meja.png'],
            ['Tenis Lapangan Putra', null, [['Reguler', 1]], 'public/assets/home_original/logo_Tenis Lapangan.png'],
            ['Tenis Lapangan Putri', null, [['Reguler', 1]], 'public/assets/home_original/logo_Tenis Lapangan.png'],
            ['Catur', null, [['Putra', 1], ['Putri', 1]], 'public/assets/home_original/logo_Catur.png'],
            ['E-Sport Mobile Legends', 5, [['Team', 5]], 'public/assets/home_original/logo_E-Sport.png'],
            ['E-Sport PUBG Mobile', 4, [['Team', 4]], 'public/assets/home_original/logo_E-Sport.png'],
            ['E-Sport Valorant', 5, [['Team', 5]], 'public/assets/home_original/logo_E-Sport.png'],
            ['E-Sport FC 24', null, [['Individu', 1]], 'public/assets/home_original/logo_E-Sport.png'],
            ['Atletik Lari 100m', null, [['Putra', 1], ['Putri', 1]], 'public/assets/home_original/logo_Lari.png'],
            ['Atletik Lari 400m', null, [['Putra', 1], ['Putri', 1]], 'public/assets/home_original/logo_Lari.png'],
            ['Renang', null, [['Putra', 1], ['Putri', 1]], null],
            ['Panahan', null, [['Putra', 1], ['Putri', 1]], null],
            ['Bridge', 4, [['Reguler', 4]], null],
        ];

        $result = [];
        $sequence = 0;

        foreach ($config as [$sportName, $sportMax, $categories, $iconPath]) {
            $sport = Sport::create([
                'name' => $sportName,
                'icon_path' => $iconPath,
                'max_members' => $sportMax,
            ]);

            foreach ($categories as [$categoryName, $categoryMax]) {
                $category = SportCategory::create([
                    'sport_id' => $sport->id,
                    'name' => $categoryName,
                    'max_members' => $categoryMax,
                ]);

                $result[] = [
                    'key' => $sportName . '|' . $categoryName,
                    'sport' => $sport,
                    'category' => $category,
                    'sequence' => $sequence++,
                ];
            }
        }

        return $result;
    }

    private function defineContingents(): array
    {
        return [
            ['name' => 'Fakultas Teknik Elektro', 'code' => 'fte', 'pic_name' => 'Halida Nurul Asnia', 'pic_email' => 'pic.fte@telucup.com'],
            ['name' => 'Fakultas Informatika', 'code' => 'fif', 'pic_name' => 'Rendra Kusuma Putra', 'pic_email' => 'pic.fif@telucup.com'],
            ['name' => 'Fakultas Rekayasa Industri', 'code' => 'fri', 'pic_name' => 'Sari Wulandari', 'pic_email' => 'pic.fri@telucup.com'],
            ['name' => 'Fakultas Ekonomi dan Bisnis', 'code' => 'feb', 'pic_name' => 'Dimas Prasetyo', 'pic_email' => 'pic.feb@telucup.com'],
            ['name' => 'Fakultas Komunikasi dan Bisnis', 'code' => 'fkb', 'pic_name' => 'Nabila Zahra Dewi', 'pic_email' => 'pic.fkb@telucup.com'],
            ['name' => 'Fakultas Industri Kreatif', 'code' => 'fik', 'pic_name' => 'Arman Nugraha', 'pic_email' => 'pic.fik@telucup.com'],
            ['name' => 'Fakultas Ilmu Terapan', 'code' => 'fit', 'pic_name' => 'Maya Ayuningtyas', 'pic_email' => 'pic.fit@telucup.com'],
            ['name' => 'Direktorat Pusat Administrasi', 'code' => 'pam', 'pic_name' => 'Rizal Mahendra', 'pic_email' => 'pic.pam@telucup.com'],
            ['name' => 'Rektorat', 'code' => 'rektorat', 'pic_name' => 'Anindya Pramesti', 'pic_email' => 'pic.rektorat@telucup.com'],
            ['name' => 'Telkom University Purwokerto', 'code' => 'tup', 'pic_name' => 'Yoga Firmansyah', 'pic_email' => 'pic.tup@telucup.com'],
            ['name' => 'Telkom University Surabaya', 'code' => 'tus', 'pic_name' => 'Amelia Putri Lestari', 'pic_email' => 'pic.tus@telucup.com'],
            ['name' => 'Telkom University Jakarta', 'code' => 'tuj', 'pic_name' => 'Farhan Aditya Saputra', 'pic_email' => 'pic.tuj@telucup.com'],
            ['name' => 'Direktorat Sumber Daya Manusia', 'code' => 'sdm', 'pic_name' => 'Larasati Dewi', 'pic_email' => 'pic.sdm@telucup.com'],
            ['name' => 'Direktorat Kemahasiswaan', 'code' => 'kemahasiswaan', 'pic_name' => 'Bagas Wicaksana', 'pic_email' => 'pic.kemahasiswaan@telucup.com'],
        ];
    }

    private function createPlayer(Contingent $contingent, int $contingentIndex, int $playerOffset, int $globalIndex): Player
    {
        $firstNames = ['Aditya', 'Bagus', 'Candra', 'Dewi', 'Eka', 'Fajar', 'Gita', 'Hendra', 'Intan', 'Joko', 'Karina', 'Lukman', 'Maya', 'Nanda', 'Oktav', 'Putri', 'Raka', 'Sinta', 'Taufiq', 'Ulfa', 'Vino', 'Wulan'];
        $middleNames = ['Pratama', 'Kusuma', 'Mahendra', 'Nur', 'Setia', 'Arif', 'Permata', 'Dwi', 'Rahma', 'Surya', 'Kurnia', 'Aulia', 'Baskara', 'Wijaya'];
        $lastNames = ['Santoso', 'Saputra', 'Wulandari', 'Nugroho', 'Hidayat', 'Lestari', 'Ramadhan', 'Firmansyah', 'Anggraini', 'Prasetyo', 'Maulana', 'Safitri', 'Kurniawan', 'Utami'];
        $employeeStatuses = ['Dosen Tetap', 'TPA Pegawai Tetap', 'TPA Pegawai Kontrak', 'Tenaga Kependidikan', 'Asisten Laboratorium', 'Staff Profesional'];
        $workLocations = ['Bagian Akademik', 'Bagian Keuangan', 'Laboratorium Fakultas', 'Urusan Kemahasiswaan', 'Bagian SDM', 'Bagian Sistem Informasi', 'Urusan Kerja Sama', 'Layanan Administrasi'];

        $name = sprintf(
            '%s %s %s',
            $firstNames[($playerOffset + $contingentIndex) % count($firstNames)],
            $middleNames[($playerOffset + ($contingentIndex * 2)) % count($middleNames)],
            $lastNames[($playerOffset + ($contingentIndex * 3)) % count($lastNames)]
        );

        $user = User::create([
            'name' => $name,
            'email' => sprintf('player%03d@telucup.com', $globalIndex),
            'password' => Hash::make(self::PASSWORD),
            'role' => 'player',
            'is_kacamata' => ($playerOffset % 5 === 0),
        ]);

        return Player::create([
            'user_id' => $user->id,
            'name' => $name,
            'nim_nip' => sprintf('20%02d%05d', $contingentIndex + 1, $globalIndex),
            'contingent_id' => $contingent->id,
            'risk_lvl' => 'not_yet',
            'photo_path' => null,
            'employee_status' => $employeeStatuses[($playerOffset + $contingentIndex) % count($employeeStatuses)],
            'work_location' => $workLocations[($playerOffset + $contingentIndex) % count($workLocations)] . ' - ' . $contingent->name,
        ]);
    }

    private function pickPlayersForRegistration(array $players, ?int $maxMembers, int $sequence): array
    {
        $count = min($maxMembers ?? 1, count($players));
        $start = ($sequence * 3) % count($players);
        $selected = [];

        for ($i = 0; $i < $count; $i++) {
            $selected[] = $players[($start + $i) % count($players)]->id;
        }

        return $selected;
    }

    private function buildAssessmentPayload(Player $player, array $contingentDef, int $playerOffset, int $globalIndex): array
    {
        $risk = match ($playerOffset % 10) {
            0, 1, 2, 3, 4 => 'low',
            5, 6, 7 => 'medium',
            default => 'high',
        };

        $age = 20 + (($globalIndex + $playerOffset) % 28);
        $height = 158 + (($globalIndex + $playerOffset) % 30);
        $weight = 52 + (($globalIndex * 2 + $playerOffset) % 34);
        $bmi = round($weight / (($height / 100) ** 2), 2);
        $usesGlasses = (bool) $player->user->is_kacamata;

        $answers = $this->answersForRisk($risk, $age, $height, $weight, $usesGlasses, $playerOffset);
        $scores = $this->scoresForRisk($risk, $answers);
        $flags = $this->flagsForRisk($risk, $answers);

        return [
            'user_id' => $player->user_id,
            'player_id' => $player->id,
            'sport_branch_snapshot' => $this->snapshotSport($playerOffset),
            'age_snapshot' => $age,
            'bmi_snapshot' => $bmi,
            'is_kacamata_snapshot' => $usesGlasses,
            'questionnaire_version' => '1.0.0',
            'algorithm_version' => '1.0.0-dummy',
            'injury_history' => $answers['B7_injury_history_description'],
            'injury_location' => $answers['B6_recurring_injury_area'],
            'current_condition' => $answers['D4_additional_notes'] ?: 'Kondisi umum stabil, siap mengikuti arahan panitia.',
            'pain_score' => $answers['B3_pain_score'],
            'form_responses' => $answers,
            'score_breakdown' => $scores['breakdown'],
            'score_cardiovascular' => $scores['cardiovascular'],
            'score_musculoskeletal' => $scores['musculoskeletal'],
            'score_acute_readiness' => $scores['acute_readiness'],
            'score_psychosocial' => $scores['psychosocial'],
            'total_score' => $scores['total'],
            'red_flags' => $flags['red'],
            'yellow_flags' => $flags['yellow'],
            'risk_label' => $risk,
            'recommendation' => $this->recommendationFor($risk),
            'panitia_summary' => sprintf(
                'Peserta %s dari %s berada pada risiko %s dengan skor %.1f/100. Domain utama: kardio %.1f, muskuloskeletal %.1f, kesiapan %.1f, psikososial %.1f.',
                $player->name,
                $contingentDef['name'],
                strtoupper($risk),
                $scores['total'],
                $scores['cardiovascular'],
                $scores['musculoskeletal'],
                $scores['acute_readiness'],
                $scores['psychosocial']
            ),
            'requires_clearance' => $risk === 'high',
            'confidence_score' => 96.0,
            'valid_until' => Carbon::parse('2026-12-31'),
            'medical_notes' => $this->medicalNotesFor($risk),
            'is_allowed_to_play' => $risk !== 'high',
            'reviewed_at' => Carbon::parse('2026-06-01')->addDays($globalIndex % 6),
            'pic_confirmed' => true,
        ];
    }

    private function answersForRisk(string $risk, int $age, int $height, int $weight, bool $usesGlasses, int $playerOffset): array
    {
        $medium = $risk === 'medium';
        $high = $risk === 'high';

        return [
            'demo_age' => $age,
            'demo_height_cm' => $height,
            'demo_weight_kg' => $weight,
            'demo_activity_level' => $high ? 'light' : ($medium ? 'moderate' : 'active'),
            'A1_heart_condition_diagnosed' => false,
            'A2_chest_pain_during_exercise' => $high && $playerOffset % 2 === 0,
            'A3_unexplained_fainting' => false,
            'A4_serious_medical_condition' => $high && $playerOffset % 2 === 1,
            'A5_family_cardiac_death' => false,
            'A6_breathing_palpitations' => $medium || $high,
            'A7_current_medication' => $high ? 'inhaler asma saat kambuh' : ($medium ? 'vitamin dan obat maag bila perlu' : 'tidak'),
            'A8_severe_allergy' => $medium && $playerOffset % 2 === 0,
            'B1_currently_recovering' => $high && $playerOffset % 2 === 1,
            'B2_current_pain_worsens' => $high,
            'B3_pain_score' => $high ? 7 + ($playerOffset % 2) : ($medium ? 4 + ($playerOffset % 2) : ($playerOffset % 3)),
            'B4_injury_count_12months' => $high ? '3+' : ($medium ? '1' : '0'),
            'B5_orthopedic_surgery' => $high && $playerOffset % 2 === 0,
            'B6_recurring_injury_area' => $high ? 'lutut kanan terasa tidak stabil saat sprint' : ($medium ? 'pergelangan kaki kiri kadang nyeri setelah latihan' : 'tidak'),
            'B7_injury_history_description' => $high ? 'Riwayat cedera ligamen lutut dan masih perlu pemantauan.' : ($medium ? 'Pernah terkilir ringan, sudah membaik.' : 'tidak ada cedera signifikan'),
            'C1_acute_symptoms' => $high ? ['new_injury', 'sleep_short'] : ($medium ? ['sleep_short'] : ['none']),
            'C2_subjective_fitness' => $high ? 5 : ($medium ? 7 : 9),
            'C3_preparation_level' => $high ? 'general_fitness' : ($medium ? 'sporadic' : 'well_prepared'),
            'C4_using_glasses' => $usesGlasses,
            'D1_stress_level' => $high ? 4 : ($medium ? 3 : 2),
            'D2_sleep_hours' => $high ? '<5' : ($medium ? '5-6' : '7-8'),
            'D3_smoking_alcohol' => $high ? 'social' : 'none',
            'D4_additional_notes' => $high ? 'Peserta merasa belum sepenuhnya fit dan akan mengikuti arahan medis.' : ($medium ? 'Perlu pemanasan lebih lama sebelum bertanding.' : 'Tidak ada catatan tambahan.'),
        ];
    }

    private function scoresForRisk(string $risk, array $answers): array
    {
        $domain = match ($risk) {
            'low' => [8.0, 10.0, 12.0, 8.0],
            'medium' => [26.0, 34.0, 30.0, 24.0],
            default => [62.0, 72.0, 55.0, 48.0],
        };

        if ($answers['C4_using_glasses'] && $risk === 'low') {
            $domain[2] += 5.0;
        }

        $total = round(($domain[0] * 0.35) + ($domain[1] * 0.30) + ($domain[2] * 0.20) + ($domain[3] * 0.15), 2);

        return [
            'cardiovascular' => $domain[0],
            'musculoskeletal' => $domain[1],
            'acute_readiness' => $domain[2],
            'psychosocial' => $domain[3],
            'total' => $risk === 'high' ? max($total, 58.0) : $total,
            'breakdown' => collect($answers)->map(fn ($answer, $code) => [
                'answer' => $answer,
                'source' => 'dummy_seed',
                'complete' => true,
            ])->all(),
        ];
    }

    private function flagsForRisk(string $risk, array $answers): array
    {
        $yellow = [];
        $red = [];

        if ($answers['C4_using_glasses']) {
            $yellow[] = ['code' => 'MITRA_KACAMATA', 'reason' => 'Peserta menggunakan kacamata dan perlu pemantauan saat cabor kontak.'];
        }

        if ($risk === 'medium') {
            $yellow[] = ['code' => 'B3_pain_score', 'reason' => 'Nyeri ringan-sedang, disarankan pemanasan dan monitoring.'];
            $yellow[] = ['code' => 'C3_preparation_level', 'reason' => 'Persiapan latihan belum sepenuhnya rutin.'];
        }

        if ($risk === 'high') {
            $red[] = ['code' => $answers['A2_chest_pain_during_exercise'] ? 'A2_chest_pain_during_exercise' : 'B2_current_pain_worsens', 'reason' => 'Red flag terdeteksi, wajib clearance medis.'];
            $yellow[] = ['code' => 'B4_injury_count_12months', 'reason' => 'Riwayat cedera berulang dalam 12 bulan terakhir.'];
            $yellow[] = ['code' => 'D2_sleep_hours', 'reason' => 'Jam tidur kurang dari 5 jam dalam 2 minggu terakhir.'];
        }

        return ['red' => $red, 'yellow' => $yellow];
    }

    private function createOpeningRoundGames(array $sportCategories, array $registrationMap): void
    {
        $locations = ['Tel-U Sport Center', 'Lapangan Futsal Tel-U', 'GOR Serbaguna', 'Auditorium FIT', 'Lapangan Outdoor Tel-U', 'Gedung Serba Guna'];
        $referees = ['Rafi Maulana', 'Niken Prameswari', 'Agus Santoso', 'Dina Kusumawati', 'Bima Adiputra', 'Tiara Larasati'];
        $matchNumber = 1001;

        foreach ($sportCategories as $categoryIndex => $entry) {
            $registrations = $registrationMap[$entry['key']];
            $pairs = [[0, 13], [1, 12], [2, 11], [3, 10], [4, 9], [5, 8], [6, 7]];

            foreach ($pairs as $pairIndex => [$aIndex, $bIndex]) {
                $status = $pairIndex < 2 ? 'finished' : ($pairIndex === 2 ? 'live' : 'scheduled');
                $scoreA = $status === 'scheduled' ? 0 : 1 + (($categoryIndex + $pairIndex) % 3);
                $scoreB = $status === 'scheduled' ? 0 : (($categoryIndex + ($pairIndex * 2)) % 3);
                if ($status === 'finished' && $scoreA === $scoreB) {
                    $scoreA++;
                }

                Game::create([
                    'sport_id' => $entry['sport']->id,
                    'sport_category_id' => $entry['category']->id,
                    'registration_a_id' => $registrations[$aIndex]->id,
                    'registration_b_id' => $registrations[$bIndex]->id,
                    'winner_registration_id' => $status === 'finished'
                        ? ($scoreA > $scoreB ? $registrations[$aIndex]->id : $registrations[$bIndex]->id)
                        : null,
                    'score_a' => $scoreA,
                    'score_b' => $scoreB,
                    'status' => $status,
                    'round' => 1,
                    'round_name' => 'Ronde 14 Besar',
                    'match_number' => $matchNumber++,
                    'match_date' => Carbon::parse('2026-06-10')->addDays(($categoryIndex + $pairIndex) % 12)->toDateString(),
                    'match_time' => sprintf('%02d:00', 8 + (($pairIndex * 2) % 10)),
                    'location' => $locations[($categoryIndex + $pairIndex) % count($locations)],
                    'referee_name' => $referees[($categoryIndex + $pairIndex) % count($referees)],
                    'notes' => 'Jadwal dummy untuk kebutuhan demo Telucup 2026.',
                    'stats' => [
                        'attendance' => $status === 'scheduled' ? 'pending' : 'checked',
                        'source' => 'DummyDataSeeder',
                    ],
                ]);
            }
        }
    }

    private function snapshotSport(int $playerOffset): string
    {
        $sports = ['Basket Putra', 'Futsal Putra', 'Voli Putri', 'Bulu Tangkis Ganda Campuran', 'Tenis Meja Tunggal Putra', 'Catur Putri', 'E-Sport Mobile Legends', 'Atletik Lari 100m Putra'];

        return $sports[$playerOffset % count($sports)];
    }

    private function recommendationFor(string $risk): string
    {
        return match ($risk) {
            'low' => 'Peserta dapat bertanding dengan pemantauan standar. Tetap lakukan pemanasan dan pendinginan.',
            'medium' => 'Peserta boleh bertanding dengan pemantauan panitia, pemanasan ekstra, dan segera melapor jika keluhan meningkat.',
            default => 'Peserta wajib mendapatkan clearance medis sebelum bertanding dan belum boleh diturunkan sampai dinyatakan aman.',
        };
    }

    private function medicalNotesFor(string $risk): string
    {
        return match ($risk) {
            'low' => 'Tidak ditemukan hambatan medis mayor. Edukasi pemanasan standar diberikan.',
            'medium' => 'Perlu monitoring saat pemanasan dan setelah pertandingan. Disarankan membawa taping/support bila diperlukan.',
            default => 'Wajib evaluasi tenaga medis sebelum pertandingan. PIC diminta tidak menurunkan peserta sampai clearance selesai.',
        };
    }
}
