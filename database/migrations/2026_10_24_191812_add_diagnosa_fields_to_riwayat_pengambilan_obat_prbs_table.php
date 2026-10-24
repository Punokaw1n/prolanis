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
        Schema::table('riwayat_pengambilan_obat_prbs', function (Blueprint $table) {
            $table->string('kode_diagnosa', 50)->nullable()->after('pasien_prb_id');
            $table->string('diagnosa')->nullable()->after('kode_diagnosa');
        });

        // Backfill data awal dari tabel pasien_prbs jika ada
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                UPDATE riwayat_pengambilan_obat_prbs r 
                JOIN pasien_prbs p ON r.pasien_prb_id = p.id 
                SET r.diagnosa = p.diagnosa 
                WHERE r.diagnosa IS NULL AND p.diagnosa IS NOT NULL AND p.diagnosa != ''
            ");
        } else {
            DB::table('riwayat_pengambilan_obat_prbs')
                ->whereNull('diagnosa')
                ->orderBy('id')
                ->chunk(200, function ($rows) {
                    foreach ($rows as $row) {
                        $p = DB::table('pasien_prbs')->where('id', $row->pasien_prb_id)->first();
                        if ($p && ! empty($p->diagnosa)) {
                            DB::table('riwayat_pengambilan_obat_prbs')
                                ->where('id', $row->id)
                                ->update(['diagnosa' => $p->diagnosa]);
                        }
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_pengambilan_obat_prbs', function (Blueprint $table) {
            $table->dropColumn(['kode_diagnosa', 'diagnosa']);
        });
    }
};
