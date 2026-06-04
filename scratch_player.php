<?php
$user = App\Models\User::where('email', 'halidanrl@gmail.com')->first();
if ($user) {
    $player = App\Models\Player::where('user_id', $user->id)->first();
    if (!$player) {
        $player = App\Models\Player::create([
            'user_id' => $user->id,
            'name' => 'Halida Nurul Asnia',
            'nim_nip' => '1201210089',
            'employee_status' => 'MAHASISWA',
            'work_location' => 'FAKULTAS REKAYASA INDUSTRI'
        ]);
        echo "Created player for " . $user->email . "\n";
    } else {
        echo "Player already exists for " . $user->email . "\n";
    }
} else {
    echo "User not found\n";
}
