<?php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::create([
    'name' => 'PIC Kontingen C',
    'email' => 'pic_baru@telucup.com',
    'password' => Hash::make('password123'),
    'role' => 'pic',
]);

echo "Akun PIC baru berhasil dibuat:\n";
echo "Nama: " . $user->name . "\n";
echo "Email: " . $user->email . "\n";
echo "Password: password123\n";
