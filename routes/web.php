<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\Panitia\DashboardController as PanitiaDashboard;
use App\Http\Controllers\Web\Panitia\BracketController;
use App\Http\Controllers\Web\Player\DashboardController as PlayerDashboard;
use App\Http\Controllers\Web\PicKontingen\DashboardController as PicDashboard;

// ====================================================================
// PUBLIC ROUTES
// ====================================================================

Route::get('/', [App\Http\Controllers\Web\HomeController::class, 'index'])->name('home');
Route::get('/bagan', [App\Http\Controllers\Web\HomeController::class, 'bagan'])->name('bagan');

// ====================================================================
// AUTH ROUTES (dari Breeze)
// ====================================================================

require __DIR__.'/auth.php';

// ====================================================================
// DASHBOARD — PANITIA / ADMIN
// ====================================================================

Route::prefix('dashboard/panitia')
    ->middleware(['auth', 'role:panitia'])
    ->name('dashboard.panitia.')
    ->group(function () {
        Route::get('/', [PanitiaDashboard::class, 'index'])->name('index');
        Route::get('/kelola-bagan', [App\Http\Controllers\Web\Panitia\BracketController::class, 'index'])->name('kelola-bagan');
        Route::get('/verifikasi', [App\Http\Controllers\Web\Panitia\VerifikasiController::class, 'index'])->name('verifikasi');

        // Bracket API Endpoints (menggunakan web auth agar tidak kena blokir Sanctum)
        Route::post('/bracket/generate', [\App\Http\Controllers\BracketController::class, 'generate'])->name('bracket.generate');
        Route::delete('/bracket/reset', [\App\Http\Controllers\BracketController::class, 'reset'])->name('bracket.reset');
        Route::patch('/matches/{id}/score', [\App\Http\Controllers\BracketController::class, 'updateScore'])->name('matches.score');
        Route::patch('/matches/{id}/schedule', [\App\Http\Controllers\BracketController::class, 'updateSchedule'])->name('matches.schedule');
        Route::patch('/matches/{id}/teams', [\App\Http\Controllers\BracketController::class, 'setTeams'])->name('matches.teams');
        Route::patch('/matches/{id}/swap', [\App\Http\Controllers\BracketController::class, 'swapTeams'])->name('matches.swap');
        Route::patch('/matches/{id}/status', [\App\Http\Controllers\BracketController::class, 'setStatus'])->name('matches.status');

        // Match Check-in API Endpoints (web auth)
        Route::get('/matches/{id}/checkin', [\App\Http\Controllers\BracketController::class, 'getCheckin'])->name('matches.checkin.index');
        Route::post('/matches/{id}/checkin/{player_id}', [\App\Http\Controllers\BracketController::class, 'checkinPlayer'])->name('matches.checkin.store');
        Route::delete('/matches/{id}/checkin/{player_id}', [\App\Http\Controllers\BracketController::class, 'undoCheckin'])->name('matches.checkin.destroy');

        // Tahap 5+: halaman fitur panitia akan ditambahkan di sini
        // Route::get('/kontingen', [KontingenController::class, 'index'])->name('kontingen.index');
        // Route::get('/kelola-bagan', [BracketController::class, 'index'])->name('bracket.index');
        // Route::get('/galeri', [GalleryController::class, 'index'])->name('galeri.index');
        // Route::get('/medis', [MedicalController::class, 'index'])->name('medis.index');
        // Route::get('/sports', [SportController::class, 'index'])->name('sports.index');
        // Route::get('/verifikasi-tim', [VerificationController::class, 'index'])->name('verifikasi.index');
        // Route::get('/poster-sportifitas', [PosterController::class, 'index'])->name('poster.index');
    });

// ====================================================================
// DASHBOARD — PLAYER
// ====================================================================

Route::prefix('dashboard/player')
    ->middleware(['auth', 'role:player'])
    ->name('dashboard.player.')
    ->group(function () {
        Route::get('/', [PlayerDashboard::class, 'index'])->name('index');

        // Tahap 5+: halaman fitur player akan ditambahkan di sini
        // Route::get('/profil', [ProfileController::class, 'show'])->name('profil.show');
        // Route::get('/edit-profil', [ProfileController::class, 'edit'])->name('profil.edit');
        // Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');
        // Route::get('/galeri', [GalleryController::class, 'index'])->name('galeri.index');
    });

// ====================================================================
// DASHBOARD — PIC KONTINGEN
// ====================================================================

Route::prefix('dashboard/pic-kontingen')
    ->middleware(['auth', 'role:pic_kontingen'])
    ->name('dashboard.pic.')
    ->group(function () {
        Route::get('/', [PicDashboard::class, 'index'])->name('index');

        // Tahap 5+: halaman fitur PIC akan ditambahkan di sini
        // Route::get('/anggota', [AnggotaController::class, 'index'])->name('anggota.index');
        // Route::get('/registrasi', [RegistrasiController::class, 'index'])->name('registrasi.index');
        // Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
        // Route::get('/dokumentasi', [DokumentasiController::class, 'index'])->name('dokumentasi.index');
        // Route::get('/profil-saya', [ProfileController::class, 'show'])->name('profil.show');
        // Route::get('/profil-kontingen', [KontingenController::class, 'show'])->name('kontingen.show');
    });
