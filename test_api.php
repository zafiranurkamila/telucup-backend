<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = Illuminate\Http\Request::create('/api/registrations', 'GET', ['sport_id' => 31, 'status' => 'verified', 'per_page' => 100]);
$res = app()->handle($req);
file_put_contents('response.json', $res->getContent());
echo "Done\n";
