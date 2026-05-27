<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_player_checkins', function (Blueprint $table) {
            $table->id();

            $table->foreignId('game_id')
                  ->constrained('games')
                  ->onDelete('cascade');

            $table->foreignId('player_id')
                  ->constrained('players')
                  ->onDelete('cascade');

            // Menyimpan tim mana (registrasi A atau B) yang menjadi asal player
            $table->foreignId('registration_id')
                  ->constrained('registrations')
                  ->onDelete('cascade');

            $table->boolean('checked_in')->default(false);
            $table->timestamp('checked_in_at')->nullable();

            $table->timestamps();

            // Satu player hanya bisa punya satu record checkin per pertandingan
            $table->unique(['game_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_player_checkins');
    }
};
