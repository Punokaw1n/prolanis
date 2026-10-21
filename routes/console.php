<?php

use App\Models\LogAktivitas;
use App\Models\PasienPrb;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    // Hapus otomatis log aktivitas yang sudah lewat dari 1 tahun (365 hari)
    LogAktivitas::where('created_at', '<', Carbon::now()->subYear())->delete();
})->daily()->name('cleanup:log-aktivitas')->withoutOverlapping();

Artisan::command('prb:reset-import {--force : Lewati konfirmasi}', function () {
    $this->warn('Perintah ini akan menghapus HANYA data hasil import PRB tanpa menyentuh data Prolanis.');

    $importedPrbs = PasienPrb::where('id', '>=', 6)->get();
    if ($importedPrbs->isEmpty()) {
        $this->info('Tidak ada data hasil import PRB yang ditemukan untuk direset.');

        return;
    }

    if (! $this->option('force') && ! $this->confirm('Apakah Anda yakin ingin menghapus data hasil import PRB ini?')) {
        $this->info('Dibatalkan.');

        return;
    }

    $deletedPasienCount = 0;
    $clearedPrbCount = 0;

    DB::transaction(function () use ($importedPrbs, &$deletedPasienCount, &$clearedPrbCount) {
        foreach ($importedPrbs as $prb) {
            $pasien = $prb->pasien;
            if ($pasien) {
                $hasProlanis = $pasien->pemeriksaans()->exists() || $pasien->jadwalLayanan()->exists();
                if (! $hasProlanis) {
                    $pasien->delete(); // Cascade menghapus pasien_prb, riwayat, dan obat
                    $deletedPasienCount++;
                } else {
                    $prb->riwayatPengambilan()->where('sumber', 'import')->delete();
                    $prb->delete();
                    $clearedPrbCount++;
                }
            } else {
                $prb->delete();
                $clearedPrbCount++;
            }
        }
    });

    $this->info("Berhasil! {$deletedPasienCount} data Pasien murni PRB telah dihapus.");
    $this->info("Berhasil! {$clearedPrbCount} pendaftaran PRB pada pasien Prolanis telah dibersihkan.");
    $this->info('Semua riwayat pemeriksaan dan jadwal Prolanis tetap aman 100%.');
})->purpose('Menghapus data hasil import PRB tanpa merusak data Prolanis');
