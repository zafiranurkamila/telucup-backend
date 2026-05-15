<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'TelU Cup Backend API is running!']);
});

require __DIR__.'/auth.php';
