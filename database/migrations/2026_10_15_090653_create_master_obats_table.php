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
        Schema::create('master_obats', function (Blueprint $table) {
            $table->id();
            $table->string('nama_obat');
            $table->enum('kategori', ['prb', 'non_prb'])->default('prb');
            $table->string('sumber')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['nama_obat', 'kategori']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_obats');
    }
};
