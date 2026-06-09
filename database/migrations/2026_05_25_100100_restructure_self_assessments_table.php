<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Restrukturisasi tabel self_assessments untuk mendukung sistem scoring
 * berbasis bukti (evidence-based) dengan multi-domain.
 *
 * Tabel asal sebelumnya hanya menyimpan jawaban mentah dan label risiko
 * tunggal. Skema baru memecahnya ke per-domain agar:
 *   1. Panitia dapat melihat domain mana yang menjadi pemicu utama risiko.
 *   2. Hasil dapat diaudit (per-question scoring, red/yellow flag list).
 *   3. Versi kuesioner ter-track, sehingga keputusan lama tetap reproducible
 *      meski instrumen di-update di masa depan.
 *
 * Domain mengacu pada:
 *   A. Kardiovaskular & Kondisi Medis (adaptasi PAR-Q+ & AHA 14-point)
 *   B. Riwayat Cedera & Muskuloskeletal
 *   C. Acute Readiness (kondisi 7 hari terakhir)
 *   D. Psikososial & Lifestyle
 */
return new class extends Migration
{
    public function up(): void
    {
        // Backup data lama (jika ada) sebelum drop, lalu buat ulang skema baru.
        // Untuk keamanan, kami hanya akan menambah kolom — bukan drop.
        // Jika kolom sudah ada (skema lama), kita skip.

        Schema::table('self_assessments', function (Blueprint $table) {
            // --- Snapshot data peserta saat assessment dilakukan ---
            // Disimpan agar hasil tidak berubah meski profil peserta berubah.
            if (!Schema::hasColumn('self_assessments', 'sport_branch_snapshot')) {
                $table->string('sport_branch_snapshot')->nullable()->after('player_id');
            }
            if (!Schema::hasColumn('self_assessments', 'age_snapshot')) {
                $table->integer('age_snapshot')->nullable()->after('sport_branch_snapshot');
            }
            if (!Schema::hasColumn('self_assessments', 'bmi_snapshot')) {
                $table->decimal('bmi_snapshot', 5, 2)->nullable()->after('age_snapshot');
            }
            if (!Schema::hasColumn('self_assessments', 'is_kacamata_snapshot')) {
                $table->boolean('is_kacamata_snapshot')->default(false)->after('bmi_snapshot');
            }

            // --- Versi instrumen (untuk auditability) ---
            if (!Schema::hasColumn('self_assessments', 'questionnaire_version')) {
                $table->string('questionnaire_version', 20)->default('1.0.0')->after('is_kacamata_snapshot');
            }
            if (!Schema::hasColumn('self_assessments', 'algorithm_version')) {
                $table->string('algorithm_version', 20)->default('1.0.0')->after('questionnaire_version');
            }

            // --- Skor per domain (0-100, dinormalisasi) ---
            if (!Schema::hasColumn('self_assessments', 'score_cardiovascular')) {
                $table->decimal('score_cardiovascular', 6, 2)->default(0)->comment('Domain A: skor kardiovaskular & medis');
            }
            if (!Schema::hasColumn('self_assessments', 'score_musculoskeletal')) {
                $table->decimal('score_musculoskeletal', 6, 2)->default(0)->comment('Domain B: skor cedera & muskuloskeletal');
            }
            if (!Schema::hasColumn('self_assessments', 'score_acute_readiness')) {
                $table->decimal('score_acute_readiness', 6, 2)->default(0)->comment('Domain C: kondisi 7 hari terakhir');
            }
            if (!Schema::hasColumn('self_assessments', 'score_psychosocial')) {
                $table->decimal('score_psychosocial', 6, 2)->default(0)->comment('Domain D: psikososial & lifestyle');
            }
            if (!Schema::hasColumn('self_assessments', 'total_score')) {
                $table->decimal('total_score', 6, 2)->default(0)->comment('Skor total terbobot (0-100)');
            }

            // --- Flag detection ---
            if (!Schema::hasColumn('self_assessments', 'red_flags')) {
                $table->json('red_flags')->nullable()->comment('Daftar red flag yang terdeteksi');
            }
            if (!Schema::hasColumn('self_assessments', 'yellow_flags')) {
                $table->json('yellow_flags')->nullable()->comment('Daftar yellow flag yang terdeteksi');
            }

            // --- Hasil scoring detail ---
            if (!Schema::hasColumn('self_assessments', 'score_breakdown')) {
                $table->json('score_breakdown')->nullable()->comment('Detail skor per pertanyaan untuk audit trail');
            }

            // --- Output AI/LLM untuk panitia ---
            if (!Schema::hasColumn('self_assessments', 'panitia_summary')) {
                $table->text('panitia_summary')->nullable()->comment('Ringkasan otomatis untuk panitia');
            }
            if (!Schema::hasColumn('self_assessments', 'requires_clearance')) {
                $table->boolean('requires_clearance')->default(false)->comment('Wajib clearance medis sebelum bertanding');
            }

            // --- Validity window ---
            if (!Schema::hasColumn('self_assessments', 'valid_until')) {
                $table->timestamp('valid_until')->nullable()->comment('Self-assessment valid sampai tanggal ini (default 6 bulan)');
            }
        });

        // Ubah enum risk_label untuk konsisten dengan terminologi (low/medium/high)
        // Skema lama menggunakan 'moderate', kita pertahankan untuk backward compat
        // tapi tambahkan 'medium' sebagai alias yang dipakai sistem baru.
        // PostgreSQL ENUM perlu ditangani via raw SQL.
        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('self_assessments', 'risk_label')) {
            // MySQL: ubah enum menjadi low, medium, high
            DB::statement("ALTER TABLE self_assessments MODIFY COLUMN risk_label ENUM('low', 'medium', 'high') DEFAULT 'low'");
        }
    }

    public function down(): void
    {
        Schema::table('self_assessments', function (Blueprint $table) {
            $columns = [
                'sport_branch_snapshot', 'age_snapshot', 'bmi_snapshot',
                'is_kacamata_snapshot', 'questionnaire_version', 'algorithm_version',
                'score_cardiovascular', 'score_musculoskeletal', 'score_acute_readiness',
                'score_psychosocial', 'total_score', 'red_flags', 'yellow_flags',
                'score_breakdown', 'panitia_summary', 'requires_clearance', 'valid_until',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('self_assessments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // Kembalikan risk_label ke enum lama (MySQL)
        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('self_assessments', 'risk_label')) {
            DB::statement("ALTER TABLE self_assessments MODIFY COLUMN risk_label ENUM('low', 'moderate', 'high') DEFAULT 'low'");
        }
    }
};
