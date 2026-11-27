<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasien_srk', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('no_hp');
            $table->timestamp('terakhir_dihubungi')->nullable();
            $table->timestamps();
        });

        Schema::create('pasien_srk_templates', function (Blueprint $table): void {
            $table->id();
            $table->text('isi_pesan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasien_srk_templates');
        Schema::dropIfExists('pasien_srk');
    }
};
