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
            $table->enum('kategori', ['prb', 'non_prb'])->default('prb')->after('nama_obat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('obat_prbs', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};
