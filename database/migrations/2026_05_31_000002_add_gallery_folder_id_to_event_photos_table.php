<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_photos', function (Blueprint $table) {
            $table->foreignId('gallery_folder_id')
                  ->nullable()
                  ->after('uploaded_by')
                  ->constrained('gallery_folders')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('event_photos', function (Blueprint $table) {
            $table->dropForeign(['gallery_folder_id']);
            $table->dropColumn('gallery_folder_id');
        });
    }
};
