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
        Schema::table('jadwal_layanan_pasien', function (Blueprint $table) {
            $table->string('konfirmasi_kehadiran')->default('Menunggu')->after('pasien_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_layanan_pasien', function (Blueprint $table) {
            //
        });
    }
};
