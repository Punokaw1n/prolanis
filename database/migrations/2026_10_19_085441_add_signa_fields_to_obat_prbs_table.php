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
            $table->string('signa')->nullable()->after('kategori');
            $table->integer('jumlah_hari')->nullable()->default(30)->after('signa');
            $table->integer('jumlah_obat')->nullable()->default(30)->after('jumlah_hari');
            $table->string('aturan_minum')->nullable()->after('jumlah_obat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('obat_prbs', function (Blueprint $table) {
            $table->dropColumn(['signa', 'jumlah_hari', 'jumlah_obat', 'aturan_minum']);
        });
    }
};
