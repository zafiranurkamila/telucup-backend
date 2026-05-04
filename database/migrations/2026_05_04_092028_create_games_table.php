<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('sport_branch');
            $table->string('team_a')->nullable();
            $table->string('team_b')->nullable();
            $table->integer('score_a')->default(0);
            $table->integer('score_b')->default(0);
            $table->string('winner')->nullable();
            $table->integer('round')->default(1);
            $table->integer('match_number'); // Tetap match_number tidak apa-apa
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
