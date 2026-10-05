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
        Schema::create('obat_prbs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('riwayat_pengambilan_obat_prb_id')->constrained('riwayat_pengambilan_obat_prbs', 'id', 'fk_obat_prb_riwayat_id')->onDelete('cascade');
            $table->string('nama_obat');
            $table->enum('sumber', ['import', 'manual']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obat_prbs');
    }
};
