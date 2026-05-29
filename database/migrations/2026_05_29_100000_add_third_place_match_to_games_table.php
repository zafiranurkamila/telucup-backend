<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->boolean('is_third_place_match')->default(false)->after('next_match_slot');
            $table->unsignedBigInteger('loser_next_match_id')->nullable()->after('next_match_slot');
            $table->enum('loser_next_match_slot', ['a', 'b'])->nullable()->after('loser_next_match_id');
        });

        Schema::table('games', function (Blueprint $table) {
            $table->foreign('loser_next_match_id')
                  ->references('id')
                  ->on('games')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropForeign(['loser_next_match_id']);
            $table->dropColumn(['is_third_place_match', 'loser_next_match_id', 'loser_next_match_slot']);
        });
    }
};
