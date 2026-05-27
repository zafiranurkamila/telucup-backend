<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            // Hapus kolom string lama
            $table->dropColumn(['sport_branch', 'team_a', 'team_b', 'winner']);

            // FK ke sport
            $table->foreignId('sport_id')
                  ->after('id')
                  ->constrained('sports')
                  ->onDelete('cascade');

            $table->foreignId('sport_category_id')
                  ->nullable()
                  ->after('sport_id')
                  ->constrained('sport_categories')
                  ->onDelete('cascade');

            // FK ke registrations (tim peserta)
            $table->foreignId('registration_a_id')
                  ->nullable()
                  ->after('sport_category_id')
                  ->constrained('registrations')
                  ->onDelete('set null');

            $table->foreignId('registration_b_id')
                  ->nullable()
                  ->after('registration_a_id')
                  ->constrained('registrations')
                  ->onDelete('set null');

            // Pemenang: FK ke registrations
            $table->foreignId('winner_registration_id')
                  ->nullable()
                  ->after('registration_b_id')
                  ->constrained('registrations')
                  ->onDelete('set null');

            // Self-referential: pertandingan berikutnya di bracket
            $table->unsignedBigInteger('next_match_id')
                  ->nullable()
                  ->after('winner_registration_id');

            // Slot yang akan diisi pemenang: 'a' atau 'b'
            $table->enum('next_match_slot', ['a', 'b'])
                  ->nullable()
                  ->after('next_match_id');

            // Tambahkan status 'bye' untuk slot otomatis
            $table->dropColumn('status');
            $table->enum('status', ['scheduled', 'live', 'finished', 'bye'])
                  ->default('scheduled')
                  ->after('next_match_slot');

            // Catatan opsional
            $table->text('notes')->nullable()->after('referee_name');
        });

        // FK self-referential ditambah terpisah (tabel harus ada dulu)
        Schema::table('games', function (Blueprint $table) {
            $table->foreign('next_match_id')
                  ->references('id')
                  ->on('games')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropForeign(['next_match_id']);
            $table->dropForeign(['winner_registration_id']);
            $table->dropForeign(['registration_b_id']);
            $table->dropForeign(['registration_a_id']);
            $table->dropForeign(['sport_category_id']);
            $table->dropForeign(['sport_id']);

            $table->dropColumn([
                'sport_id', 'sport_category_id',
                'registration_a_id', 'registration_b_id',
                'winner_registration_id',
                'next_match_id', 'next_match_slot',
                'notes',
            ]);

            $table->dropColumn('status');
            $table->enum('status', ['scheduled', 'live', 'finished'])->default('scheduled');

            $table->string('sport_branch');
            $table->string('team_a')->nullable();
            $table->string('team_b')->nullable();
            $table->string('winner')->nullable();
        });
    }
};
