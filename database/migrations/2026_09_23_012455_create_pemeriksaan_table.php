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
        Schema::create('pemeriksaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained('pasien')->onDelete('cascade');
            $table->foreignId('jadwal_layanan_id')->nullable()->constrained('jadwal_layanan')->onDelete('set null');
            $table->date('tanggal_periksa');
            $table->text('keluhan')->nullable();
            $table->text('kode_icd')->nullable();
            $table->string('nama_diagnosa')->nullable();
            $table->float('berat_badan')->nullable();
            $table->float('tinggi_badan')->nullable();
            $table->integer('tensi_sistol')->nullable();
            $table->integer('tensi_diastol')->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->integer('heart_rate')->nullable();
            $table->float('temperature')->nullable();
            $table->foreignId('nakes_id')->nullable()->constrained('pegawai')->onDelete('set null');
            $table->timestamps();

            $table->unique(['pasien_id', 'tanggal_periksa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan');
    }
};
