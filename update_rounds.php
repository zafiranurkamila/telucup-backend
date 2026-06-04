<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$games = App\Models\Game::all();
foreach($games as $g) {
    $max = App\Models\Game::where('sport_id', $g->sport_id)->max('round');
    $fromEnd = $max - $g->round;
    if ($fromEnd > 2) { // not Final, Semi, or Quarter
        $g->round_name = 'Putaran ' . $g->round;
        $g->save();
    }
}
echo "Rounds updated!\n";
