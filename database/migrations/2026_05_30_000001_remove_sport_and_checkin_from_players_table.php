<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropForeign(['sport_id']);
            $table->dropForeign(['sport_category_id']);
            $table->dropColumn(['sport_id', 'sport_category_id', 'checked_in_at']);
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->foreignId('sport_id')->nullable()->constrained('sports')->onDelete('set null');
            $table->foreignId('sport_category_id')->nullable()->constrained('sport_categories')->onDelete('set null');
            $table->timestamp('checked_in_at')->nullable();
        });
    }
};
