<?php
$users = App\Models\User::where('role', 'pic')->get(['id', 'name', 'email', 'role']);
foreach ($users as $u) {
    echo $u->id . ' | ' . $u->name . ' | ' . $u->email . PHP_EOL;
}
echo "Total: " . $users->count() . " akun PIC kontingen" . PHP_EOL;
