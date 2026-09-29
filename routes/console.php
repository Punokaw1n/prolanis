<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::call(function () {
    // Hapus otomatis log aktivitas yang sudah lewat dari 1 tahun (365 hari)
    \App\Models\LogAktivitas::where('created_at', '<', \Carbon\Carbon::now()->subYear())->delete();
})->daily()->name('cleanup:log-aktivitas')->withoutOverlapping();
