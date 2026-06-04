<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find an admin or panitia user
$user = \App\Models\User::where('role', 'panitia')->orWhere('role', 'admin')->first();
if (!$user) {
    echo "No admin user found\n";
    exit;
}

// Act as the user
auth()->login($user);

$req = Illuminate\Http\Request::create('/api/registrations', 'GET', ['per_page' => 1000, 'sport_id' => 31]);
$req->headers->set('Accept', 'application/json');

$res = app()->handle($req);
file_put_contents('response.json', $res->getContent());
echo "Status: " . $res->getStatusCode() . "\n";
