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
    Schema::create('self_assessments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('player_id')->constrained()->onDelete('cascade');
        $table->text('injury_history');      // Riwayat Cedera
        $table->string('injury_location');   // Lokasi Cedera
        $table->text('current_condition');   // Kondisi Terkini
        $table->enum('risk_label', ['low', 'moderate', 'high'])->default('low');
        $table->text('recommendation')->nullable(); // FR-01.5
        $table->boolean('pic_confirmed')->default(false); // FR-01.6
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('self_assessments');
    }
};
