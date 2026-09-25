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
        Schema::create('jadwal_layanan_pasien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_layanan_id')->constrained('jadwal_layanan')->onDelete('cascade');
            $table->foreignId('pasien_id')->constrained('pasien')->onDelete('cascade');
            $table->timestamps();
            
            // Ensure a patient isn't added twice to the exact same schedule
            $table->unique(['jadwal_layanan_id', 'pasien_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_layanan_pasien');
    }
};
