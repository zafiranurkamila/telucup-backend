<?php

use App\Http\Controllers\SelfAssessmentController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\GameController;

// FR-03: Campaign Routes
Route::get('/campaign', [CampaignController::class, 'show'])->name('campaign.show');
Route::post('/campaign/confirm', [CampaignController::class, 'confirm'])->name('campaign.confirm');

Route::middleware(['auth'])->group(function () {
    // PIC Kontingen
    Route::middleware(['role:pic_kontingen'])->group(function () {
        Route::post('/players', [PlayerController::class, 'store'])->name('players.store');
        Route::post('/self-assessment', [SelfAssessmentController::class, 'store'])->name('self-assessment.store');
    });

    // Fitur Peninjauan Medis (Bisa diakses Admin/Panitia Medis)
    Route::post('/self-assessment/review/{id}', [SelfAssessmentController::class, 'submitReview'])->name('self-assessment.review');

    // Panitia Lapangan
    Route::middleware(['role:panitia'])->group(function () {
        Route::get('/field/verification', [VerificationController::class, 'index'])->name('field.index');
        Route::post('/field/checkin/{id}', [VerificationController::class, 'checkIn'])->name('field.checkin');
    });

    // Admin SDM (Super Admin)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/templates', [\App\Http\Controllers\AdminController::class, 'templates']);
        Route::get('/admin/registrations', [\App\Http\Controllers\AdminController::class, 'registrations']);
        Route::post('/admin/registrations/{id}/verify', [\App\Http\Controllers\AdminController::class, 'verifyRegistration']);
        Route::get('/admin/schedules', [\App\Http\Controllers\AdminController::class, 'schedules']);
        
        Route::post('/bracket/generate', [GameController::class, 'generateBracket'])->name('bracket.generate');
        Route::post('/bracket/update-score/{id}', [GameController::class, 'updateScore'])->name('bracket.update');
    });
});

Route::get('/bracket/detail/{id}', [GameController::class, 'show'])->name('bracket.detail');

// Publik / Semua User yang Login
Route::get('/summary/contingent', [PlayerController::class, 'contingentSummary'])->name('summary.contingent');
Route::get('/campaign', [CampaignController::class, 'show'])->name('campaign.show');
Route::post('/campaign/confirm', [CampaignController::class, 'confirm'])->name('campaign.confirm');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::post('/chatbot/ask', [ChatbotController::class, 'ask'])->name('chatbot.ask');

require __DIR__.'/auth.php';
