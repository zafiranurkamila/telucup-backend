<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_photos', function (Blueprint $table) {
            $table->string('ai_status')->default('pending')->after('gallery_folder_id');
            $table->unsignedInteger('faces_detected')->nullable()->after('ai_status');
            $table->timestamp('ai_processed_at')->nullable()->after('faces_detected');
        });
    }

    public function down(): void
    {
        Schema::table('event_photos', function (Blueprint $table) {
            $table->dropColumn([
                'ai_status',
                'faces_detected',
                'ai_processed_at',
            ]);
        });
    }
};
