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
            $table->string('round_name')->nullable(); // Quarter Finals, Semi Finals, etc.
            $table->string('team_a')->nullable();
            $table->string('team_b')->nullable();
            $table->integer('score_a')->default(0);
            $table->integer('score_b')->default(0);
            $table->string('winner')->nullable();
            $table->enum('status', ['scheduled', 'live', 'finished'])->default('scheduled');
            $table->date('match_date')->nullable();
            $table->string('match_time')->nullable();
            $table->string('referee_name')->nullable();
            $table->json('stats')->nullable(); // Untuk Top Performer
            $table->integer('round')->default(1);
            $table->integer('match_number');
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
