<?php
$pic1 = App\Models\User::firstOrCreate(
    ['email' => 'pic@telucup.com'],
    ['name' => 'PIC Kontingen 1', 'password' => bcrypt('password'), 'role' => 'pic_kontingen']
);
$pic2 = App\Models\User::firstOrCreate(
    ['email' => 'pic2@telucup.com'],
    ['name' => 'PIC Kontingen 2', 'password' => bcrypt('password'), 'role' => 'pic_kontingen']
);
$panitia = App\Models\User::firstOrCreate(
    ['email' => 'panitia@telucup.com'],
    ['name' => 'Panitia', 'password' => bcrypt('password'), 'role' => 'panitia']
);

App\Models\Contingent::where('id', 1)->update(['pic_user_id' => $pic1->id]);
App\Models\Contingent::where('id', 2)->update(['pic_user_id' => $pic2->id]);
echo "Setup complete!";
