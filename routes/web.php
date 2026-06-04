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

Route::get('/', [HomeController::class, 'index'])->name('home');

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
