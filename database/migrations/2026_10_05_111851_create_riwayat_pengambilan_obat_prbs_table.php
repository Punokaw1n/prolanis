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
        Schema::create('riwayat_pengambilan_obat_prbs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_prb_id')->constrained('pasien_prbs')->onDelete('cascade');
            $table->date('tanggal_jatuh_tempo')->nullable(); // Can be nullable if not yet set (during import)
            $table->date('tanggal_diambil')->nullable();
            $table->enum('sumber', ['import', 'manual']);
            $table->foreignId('dicatat_oleh')->nullable()->constrained('pengguna')->onDelete('set null');
            $table->timestamp('terakhir_diingatkan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pengambilan_obat_prbs');
    }
};
