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
        Schema::table('contingents', function (Blueprint $table) {
            $table->string('cloudinary_public_id')->nullable()->after('pic_user_id');
            $table->string('image_url')->nullable()->after('cloudinary_public_id');
        });
    }

    public function down(): void
    {
        Schema::table('contingents', function (Blueprint $table) {
            $table->dropColumn(['cloudinary_public_id', 'image_url']);
        });
    }
};
