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
        Schema::create('poin_template', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_persetujuan_id')->constrained('template_persetujuan')->cascadeOnDelete();
            $table->integer('urutan')->default(0);
            $table->text('teks_poin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poin_template');
    }
};
