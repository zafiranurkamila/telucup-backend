<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$regs = \App\Models\Registration::all();
echo "Total: " . $regs->count() . "\n";
foreach($regs->pluck('status')->unique() as $status) {
    echo $status . ": " . \App\Models\Registration::where('status', $status)->count() . "\n";
}
