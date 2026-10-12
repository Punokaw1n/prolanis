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
        Schema::table('pasien', function (Blueprint $table) {
            $table->enum('status_keaktifan', ['aktif', 'tidak_aktif'])->default('aktif')->after('tipe_pasien');
            $table->string('alasan_tidak_aktif')->nullable()->after('status_keaktifan');
            $table->date('tanggal_tidak_aktif')->nullable()->after('alasan_tidak_aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->dropColumn(['status_keaktifan', 'alasan_tidak_aktif', 'tanggal_tidak_aktif']);
        });
    }
};
