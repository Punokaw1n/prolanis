<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pemberian_obat', function (Blueprint $table) {
            $table->date('tanggal_diberikan')->nullable()->after('nama_obat');
        });

        // Update existing records in a cross-database compatible way
        $pemberians = DB::table('pemberian_obat')->get();
        foreach ($pemberians as $pemberian) {
            $pemeriksaan = DB::table('pemeriksaan')->find($pemberian->pemeriksaan_id);
            if ($pemeriksaan) {
                DB::table('pemberian_obat')
                    ->where('id', $pemberian->id)
                    ->update(['tanggal_diberikan' => $pemeriksaan->tanggal_periksa]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemberian_obat', function (Blueprint $table) {
            $table->dropColumn('tanggal_diberikan');
        });
    }
};
