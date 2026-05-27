<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SelfAssessmentController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\EventPhotoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\ContingentController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\BracketController;

// Auth Routes (API)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ====================================================================
// SPORTS — Publik (baca)
// ====================================================================
Route::get('/sports', [SportController::class, 'index'])->name('sports.index');
Route::get('/sports/{id}', [SportController::class, 'show'])->name('sports.show');

// ====================================================================
// CONTINGENTS — Publik (baca)
// ====================================================================
Route::get('/contingents', [ContingentController::class, 'index'])->name('contingents.index');
Route::get('/contingents/{id}', [ContingentController::class, 'show'])->whereNumber('id')->name('contingents.show');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// FR-03: Campaign Routes
Route::get('/campaign', [CampaignController::class, 'show'])->name('campaign.show');
Route::post('/campaign/confirm', [CampaignController::class, 'confirm'])->name('campaign.confirm');

Route::middleware(['auth:sanctum'])->group(function () {

    // ====================================================================
    // SELF-ASSESSMENT — Fitur deteksi risiko peserta
    // ====================================================================
    //
    // Akses:
    //   - Semua user login: ambil struktur kuesioner & submit untuk dirinya
    //   - Player & pic_kontingen: submit jawaban
    //   - Panitia & admin: list, summary, review medis
    //
    Route::prefix('self-assessment')->group(function () {
        // GET kuesioner — semua user login bisa akses
        Route::get('/questionnaire', [SelfAssessmentController::class, 'questionnaire'])
            ->name('self-assessment.questionnaire');

        // GET assessment terakhir milik user yang login
        Route::get('/me', [SelfAssessmentController::class, 'myLatest'])
            ->name('self-assessment.me');

        // Submit assessment — player atau pic_kontingen
        Route::middleware(['role:pic_kontingen,player,admin,panitia'])->group(function () {
            Route::post('/', [SelfAssessmentController::class, 'store'])
                ->name('self-assessment.store');
        });

        // Panitia & admin: list, detail, summary
        Route::middleware(['role:admin,panitia'])->group(function () {
            Route::get('/', [SelfAssessmentController::class, 'index'])
                ->name('self-assessment.index');
            Route::get('/summary/contingent', [SelfAssessmentController::class, 'summaryByContingent'])
                ->name('self-assessment.summary.contingent');
            Route::get('/summary/sport', [SelfAssessmentController::class, 'summaryBySport'])
                ->name('self-assessment.summary.sport');
            Route::post('/review/{id}', [SelfAssessmentController::class, 'submitReview'])
                ->name('self-assessment.review');
        });

        // Detail (boleh diakses peserta untuk lihat hasilnya sendiri,
        // authorization lebih lanjut di-handle di controller).
        Route::get('/{id}', [SelfAssessmentController::class, 'show'])
            ->whereNumber('id')
            ->name('self-assessment.show');
    });

    // ====================================================================
    // PIC Kontingen / Panitia
    // ====================================================================
    Route::middleware(['role:pic_kontingen,admin,panitia'])->group(function () {
        Route::post('/players', [PlayerController::class, 'store'])->name('players.store');
    });

    // ====================================================================
    // SPORTS — CRUD hanya untuk admin/panitia
    // ====================================================================
    Route::middleware(['role:admin,panitia'])->group(function () {
        Route::post('/sports', [SportController::class, 'store'])->name('sports.store');
        Route::put('/sports/{id}', [SportController::class, 'update'])->name('sports.update');
        Route::delete('/sports/{id}', [SportController::class, 'destroy'])->name('sports.destroy');
    });

    // ====================================================================
    // CONTINGENTS — CRUD & assign PIC (admin/panitia)
    // ====================================================================
    Route::middleware(['role:admin,panitia'])->group(function () {
        Route::post('/contingents', [ContingentController::class, 'store'])->name('contingents.store');
        Route::put('/contingents/{id}', [ContingentController::class, 'update'])->name('contingents.update');
        Route::delete('/contingents/{id}', [ContingentController::class, 'destroy'])->name('contingents.destroy');
        Route::put('/contingents/{id}/assign-pic', [ContingentController::class, 'assignPic'])->name('contingents.assign-pic');
    });

    // ====================================================================
    // CONTINGENTS — Kelola anggota (PIC kontingen)
    // ====================================================================
    Route::middleware(['role:pic_kontingen'])->group(function () {
        Route::get('/contingents/my', [ContingentController::class, 'myContingent'])->name('contingents.my');
        Route::post('/contingents/my/players', [ContingentController::class, 'addPlayer'])->name('contingents.my.players.add');
        Route::delete('/contingents/my/players/{player_id}', [ContingentController::class, 'removePlayer'])->name('contingents.my.players.remove');
    });

    // ====================================================================
    // REGISTRATIONS (TIM) — PIC mendaftarkan tim ke cabang olahraga
    // ====================================================================
    Route::middleware(['role:pic_kontingen'])->group(function () {
        Route::get('/registrations/my', [RegistrationController::class, 'myRegistrations'])->name('registrations.my');
        Route::post('/registrations', [RegistrationController::class, 'store'])->name('registrations.store');
        Route::post('/registrations/{id}/players', [RegistrationController::class, 'addPlayers'])->name('registrations.players.add');
        Route::delete('/registrations/{id}/players/{player_id}', [RegistrationController::class, 'removePlayer'])->name('registrations.players.remove');
    });

    // ====================================================================
    // Panitia (Gabungan Admin & Panitia Lapangan/Medis)
    // ====================================================================
    Route::middleware(['role:panitia'])->group(function () {
        Route::get('/field/verification', [VerificationController::class, 'index'])->name('field.index');
        Route::post('/field/checkin/{id}', [VerificationController::class, 'checkIn'])->name('field.checkin');

        Route::get('/admin/templates', [\App\Http\Controllers\AdminController::class, 'templates']);
        Route::get('/admin/schedules', [\App\Http\Controllers\AdminController::class, 'schedules']);

        // Registrasi tim — pantau & verifikasi
        Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
        Route::get('/registrations/{id}', [RegistrationController::class, 'show'])->name('registrations.show');
        Route::post('/registrations/{id}/verify', [RegistrationController::class, 'verify'])->name('registrations.verify');

        // Bracket — generate & reset (admin/panitia)
        Route::post('/bracket/generate', [BracketController::class, 'generate'])->name('bracket.generate');
        Route::delete('/bracket/reset', [BracketController::class, 'reset'])->name('bracket.reset');

        // Matches — update (admin/panitia)
        Route::patch('/matches/{id}/score', [BracketController::class, 'updateScore'])->name('matches.score');
        Route::patch('/matches/{id}/schedule', [BracketController::class, 'updateSchedule'])->name('matches.schedule');
        Route::patch('/matches/{id}/teams', [BracketController::class, 'setTeams'])->name('matches.teams');
        Route::patch('/matches/{id}/swap', [BracketController::class, 'swapTeams'])->name('matches.swap');
        Route::patch('/matches/{id}/status', [BracketController::class, 'setStatus'])->name('matches.status');

        // Checkin peserta pertandingan (admin/panitia)
        Route::get('/matches/{id}/checkin', [BracketController::class, 'getCheckin'])->name('matches.checkin.index');
        Route::post('/matches/{id}/checkin/{player_id}', [BracketController::class, 'checkinPlayer'])->name('matches.checkin.store');
        Route::delete('/matches/{id}/checkin/{player_id}', [BracketController::class, 'undoCheckin'])->name('matches.checkin.destroy');

        // Endpoint untuk update role Player -> PIC Kontingen
        Route::put('/admin/users/{id}/promote-to-pic', [\App\Http\Controllers\AdminController::class, 'promoteToPic']);

        // Panitia: Upload foto event untuk AI processing
        Route::post('/event-photos', [EventPhotoController::class, 'store']);
    });

    // ====================================================================
    // Phase 1: Face Enrollment (Pemain mengunggah foto profil untuk AI)
    // ====================================================================
    Route::patch('/player/profile', [PlayerController::class, 'updateProfile'])->name('player.profile.update');
    Route::post('/players/enroll-face', [PlayerController::class, 'enrollFace'])->name('players.enroll-face');

    // ====================================================================
    // Phase 2: Gallery API (Frontend mengonsumsi hasil deteksi AI)
    // ====================================================================
    Route::get('/my-gallery', [GalleryController::class, 'index'])->name('gallery.my');
    Route::patch('/my-gallery/{photo_face_id}/validate', [GalleryController::class, 'validate'])->name('gallery.validate');
});

// Bracket & match detail — publik
Route::get('/bracket', [BracketController::class, 'bracket'])->name('bracket.view');
Route::get('/matches/{id}', [BracketController::class, 'matchDetail'])->name('matches.detail');

// Publik / Semua User yang Login
Route::get('/summary/contingent', [PlayerController::class, 'contingentSummary'])->name('summary.contingent');
Route::post('/chatbot/ask', [ChatbotController::class, 'ask'])->name('chatbot.ask');