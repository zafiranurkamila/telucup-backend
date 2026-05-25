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
        Schema::table('players', function (Blueprint $table) {
            $table->string('nim_nip')->nullable()->change();
            $table->string('sport_branch')->nullable()->change();
            $table->string('contingent')->nullable()->change();
            
            // Add unique constraint to user_id
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->string('nim_nip')->nullable(false)->change();
            $table->string('sport_branch')->nullable(false)->change();
            $table->string('contingent')->nullable(false)->change();
            
            $table->dropUnique(['user_id']);
        });
    }
};
