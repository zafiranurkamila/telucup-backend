<?php
use App\Models\Contingent;
use App\Models\Registration;
use App\Models\Sport;
use App\Models\User;
use App\Models\Player;

$basket = Sport::where('name', 'like', '%Basket%')->first();

if (!$basket) {
    echo "Sport Basket tidak ditemukan.\n";
    exit;
}

$category = $basket->categories()->first();
if (!$category) {
    echo "Kategori untuk Basket tidak ditemukan.\n";
    exit;
}

$i = 9; // Menambahkan tim ke-9

// 1. Buat PIC
$pic = User::firstOrCreate(
    ['email' => 'pic_basket_' . $i . '@telucup.com'],
    [
        'name' => 'PIC Basket ' . $i,
        'password' => bcrypt('password'),
        'role' => 'pic_kontingen'
    ]
);

// 2. Buat Kontingen
$contingent = Contingent::firstOrCreate(
    ['name' => 'Tim Basket ' . $i],
    ['pic_user_id' => $pic->id]
);

if ($contingent->pic_user_id !== $pic->id) {
    $contingent->update(['pic_user_id' => $pic->id]);
}

// 3. Buat 5 Player per Kontingen
for ($p = 1; $p <= 5; $p++) {
    Player::firstOrCreate(
        ['nim_nip' => '130120' . $i . '00' . $p],
        [
            'contingent_id' => $contingent->id,
            'sport_id' => $basket->id,
            'sport_category_id' => $category->id,
            'name' => 'Pemain ' . $p . ' Tim ' . $i,
            'employee_status' => 'MAHASISWA',
            'work_location' => 'Informatika',
            'verification_status' => 'verified'
        ]
    );
}

// 4. Registrasi ke Basket
Registration::firstOrCreate(
    [
        'contingent_id' => $contingent->id,
        'sport_id' => $basket->id,
        'sport_category_id' => $category->id,
    ],
    [
        'status' => 'verified'
    ]
);

echo "Berhasil menambahkan Tim Basket ke-9 beserta data pelengkapnya!\n";
