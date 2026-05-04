<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Player;
use App\Models\Sport;
use App\Models\Template;
use App\Models\Registration;
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
        // 1. Buat 28 Cabang Olahraga (Lengkap)
        $sports = [
            ['name' => 'Basket Putra', 'categories' => ['Reguler']],
            ['name' => 'Basket Putri', 'categories' => ['Reguler']],
            ['name' => 'Futsal Putra', 'categories' => ['Reguler']],
            ['name' => 'Futsal Putri', 'categories' => ['Reguler']],
            ['name' => 'Voli Putra', 'categories' => ['Reguler']],
            ['name' => 'Voli Putri', 'categories' => ['Reguler']],
            ['name' => 'Bulu Tangkis Tunggal Putra', 'categories' => ['Reguler']],
            ['name' => 'Bulu Tangkis Tunggal Putri', 'categories' => ['Reguler']],
            ['name' => 'Bulu Tangkis Ganda Putra', 'categories' => ['Reguler']],
            ['name' => 'Bulu Tangkis Ganda Putri', 'categories' => ['Reguler']],
            ['name' => 'Bulu Tangkis Ganda Campuran', 'categories' => ['Reguler']],
            ['name' => 'Tenis Meja Tunggal Putra', 'categories' => ['Reguler']],
            ['name' => 'Tenis Meja Tunggal Putri', 'categories' => ['Reguler']],
            ['name' => 'Tenis Meja Ganda Putra', 'categories' => ['Reguler']],
            ['name' => 'Tenis Meja Ganda Putri', 'categories' => ['Reguler']],
            ['name' => 'Tenis Meja Ganda Campuran', 'categories' => ['Reguler']],
            ['name' => 'Tenis Lapangan Putra', 'categories' => ['Reguler']],
            ['name' => 'Tenis Lapangan Putri', 'categories' => ['Reguler']],
            ['name' => 'Catur', 'categories' => ['Putra', 'Putri']],
            ['name' => 'E-Sport (Mobile Legends)', 'categories' => ['Team']],
            ['name' => 'E-Sport (PUBG Mobile)', 'categories' => ['Team']],
            ['name' => 'E-Sport (Valorant)', 'categories' => ['Team']],
            ['name' => 'E-Sport (FC 24 / FIFA)', 'categories' => ['Individu']],
            ['name' => 'Atletik (Lari 100m)', 'categories' => ['Putra', 'Putri']],
            ['name' => 'Atletik (Lari 400m)', 'categories' => ['Putra', 'Putri']],
            ['name' => 'Renang', 'categories' => ['Putra', 'Putri']],
            ['name' => 'Panahan', 'categories' => ['Putra', 'Putri']],
            ['name' => 'Bridge', 'categories' => ['Reguler']],
        ];

        foreach ($sports as $s) {
            Sport::create($s);
        }

        // 2. Buat Template Tahun (Gambar 1)
        Template::create(['year' => '2026', 'is_active' => true]);
        Template::create(['year' => '2025', 'is_active' => false]);

        // 2. Buat User (Roles)
        User::create([
            'name' => 'Arief Kurniawan',
            'email' => 'arief@telucup.com',
            'password' => Hash::make('password'),
            'role' => 'admin' // Super Admin sesuai gambar
        ]);

        // 3. Buat Registrasi (Gambar 2)
        Registration::create([
            'sport_branch' => 'Bulutangkis Ganda Putra',
            'contingent' => 'Bidang 2',
            'pic_name' => 'HALIDA NURUL ASNIA',
            'pic_email' => 'halidanrl@gmail.com',
            'pic_whatsapp' => '08123456789',
            'status' => 'verified'
        ]);

        // 4. Buat Pemain (Gambar 3 - Detail Lengkap)
        $bagus = Player::create([
            'name' => 'Bagus Setiawan',
            'nim_nip' => '1201210088',
            'sport_branch' => 'Futsal',
            'contingent' => 'Fakultas Industri Kreatif',
            'employee_status' => 'TPA PEGAWAI TETAP',
            'work_location' => 'URUSAN PENCATATAN DAN PENGELOLAAN ASET',
            'verification_status' => 'verified'
        ]);

        // 5. Buat Pertandingan (Gambar 4 & 5 - Dengan Lokasi)
        Game::create([
            'sport_branch' => 'Basket Putra',
            'round_name' => 'Quarter Finals',
            'team_a' => 'Bidang 2',
            'team_b' => 'Bidang 1',
            'score_a' => 2,
            'score_b' => 3,
            'status' => 'finished',
            'match_date' => '2026-04-07',
            'match_time' => '02:30',
            'location' => 'Sport Center',
            'round' => 2,
            'match_number' => 104
        ]);
    }
}
