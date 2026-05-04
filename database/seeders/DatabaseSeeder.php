<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Player;
use App\Models\SelfAssessment;
use App\Models\Game;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat User (Roles)
        User::create([
            'name' => 'Admin Tel-U Cup',
            'email' => 'admin@telucup.com',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        User::create([
            'name' => 'Dr. Andi (Medis)',
            'email' => 'medis@telucup.com',
            'password' => Hash::make('password'),
            'role' => 'panitia'
        ]);

        // 2. Buat Pemain (Contoh: Bagus Setiawan dari UI)
        $bagus = Player::create([
            'name' => 'Bagus Setiawan',
            'nim_nip' => '1201210088',
            'sport_branch' => 'Futsal',
            'contingent' => 'Fakultas Industri Kreatif',
            'checked_in_at' => now(),
            'verification_status' => 'verified'
        ]);

        // 3. Buat Assessment untuk Bagus (High Risk sesuai UI)
        SelfAssessment::create([
            'player_id' => $bagus->id,
            'injury_history' => 'ACL (2023), Lutut Kiri Operasi',
            'injury_location' => 'Lutut Kiri',
            'current_condition' => 'Nyeri saat ditekuk, mobilitas terbatas.',
            'pain_score' => 7,
            'form_responses' => ['nyeri' => 'Ya', 'keterbatasan_gerak' => 'Ya', 'pernah_acl' => 'Ya'],
            'risk_label' => 'high',
            'confidence_score' => 87.4,
            'recommendation' => 'Pemain tidak direkomendasikan untuk aktivitas intensitas tinggi.',
            'medical_notes' => 'Menunggu observasi klinis lanjutan.',
            'is_allowed_to_play' => false
        ]);

        // 4. Buat Pertandingan (Bagan sesuai UI)
        Game::create([
            'sport_branch' => 'Bola Basket (Putra)',
            'round_name' => 'Round 1',
            'team_a' => 'FIT Warrior',
            'team_b' => 'FRI Titans',
            'score_a' => 78,
            'score_b' => 62,
            'winner' => 'FIT Warrior',
            'status' => 'finished',
            'match_date' => '2024-10-23',
            'match_time' => '10:00',
            'referee_name' => 'Andi Wijaya',
            'round' => 1,
            'match_number' => 1
        ]);

        Game::create([
            'sport_branch' => 'Bola Basket (Putra)',
            'round_name' => 'Round 1',
            'team_a' => 'FEB Eagles',
            'team_b' => 'Bidang II',
            'score_a' => 82,
            'score_b' => 80,
            'winner' => 'FEB Eagles',
            'status' => 'finished',
            'match_date' => '2024-10-23',
            'match_time' => '13:00',
            'round' => 1,
            'match_number' => 2
        ]);

        // Match di Quarter Finals (Lanjutan dari UI)
        Game::create([
            'sport_branch' => 'Bola Basket (Putra)',
            'round_name' => 'Quarter Finals',
            'team_a' => 'FIT Warrior',
            'team_b' => 'FEB Eagles',
            'status' => 'scheduled',
            'match_date' => '2024-10-25',
            'match_time' => '10:00',
            'round' => 2,
            'match_number' => 1
        ]);
    }
}
