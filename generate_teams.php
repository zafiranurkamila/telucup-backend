<?php

$sportId = 1;
$categoryId = 1;

for ($i = 3; $i <= 8; $i++) {
    // Buat Kontingen
    $contingent = App\Models\Contingent::firstOrCreate(
        ['name' => 'Kontingen Tambahan ' . $i]
    );

    // Buat pendaftaran tim
    App\Models\Registration::firstOrCreate(
        [
            'contingent_id' => $contingent->id,
            'sport_id' => $sportId,
            'sport_category_id' => $categoryId,
        ],
        [
            'status' => 'verified',
        ]
    );
}

echo "Berhasil menambahkan 6 tim tambahan untuk Basket Putra! Total tim sekarang menjadi 8.";
