<?php

use App\Models\LogAktivitas;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    // Hapus otomatis log aktivitas yang sudah lewat dari 1 tahun (365 hari)
    LogAktivitas::where('created_at', '<', Carbon::now()->subYear())->delete();
})->daily()->name('cleanup:log-aktivitas')->withoutOverlapping();
