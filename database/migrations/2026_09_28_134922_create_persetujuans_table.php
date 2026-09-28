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
        Schema::create('persetujuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained('pasien')->cascadeOnDelete();
            $table->foreignId('template_persetujuan_id')->nullable()->constrained('template_persetujuan')->nullOnDelete();
            $table->foreignId('petugas_id')->nullable()->constrained('pengguna')->nullOnDelete();
            
            // Salinan Isi Dokumen (agar tidak berubah jika template diubah)
            $table->string('salinan_judul');
            $table->text('salinan_teks_pembuka');
            $table->text('salinan_teks_penutup');
            $table->json('salinan_poin'); // Simpan poin-poin dalam bentuk array JSON
            
            // Sistem Token & Tanda Tangan
            $table->string('token_qr', 64)->unique();
            $table->enum('status', ['menunggu_tanda_tangan', 'ditandatangani', 'kedaluwarsa', 'dibatalkan'])->default('menunggu_tanda_tangan');
            $table->timestamp('kedaluwarsa_pada');
            $table->timestamp('ditandatangani_pada')->nullable();
            
            // Jejak Keamanan (Metadata Tanda Tangan)
            $table->string('tanda_tangan_path')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('hash_dokumen')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persetujuan');
    }
};
