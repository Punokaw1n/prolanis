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
        Schema::table('obat_prbs', function (Blueprint $table) {
            $table->string('status_pemberian')->default('belum')->after('aturan_minum'); // belum, sudah, kosong
            $table->string('catatan_farmasi')->nullable()->after('status_pemberian');
        });

        Schema::table('riwayat_pengambilan_obat_prbs', function (Blueprint $table) {
            $table->string('status_farmasi')->default('belum')->after('catatan'); // belum, sudah, tidak_lengkap
            $table->foreignId('petugas_farmasi_id')->nullable()->after('status_farmasi')->constrained('pengguna')->onDelete('set null');
            $table->timestamp('waktu_diberikan')->nullable()->after('petugas_farmasi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_pengambilan_obat_prbs', function (Blueprint $table) {
            $table->dropForeign(['petugas_farmasi_id']);
            $table->dropColumn(['status_farmasi', 'petugas_farmasi_id', 'waktu_diberikan']);
        });

        Schema::table('obat_prbs', function (Blueprint $table) {
            $table->dropColumn(['status_pemberian', 'catatan_farmasi']);
        });
    }
};
