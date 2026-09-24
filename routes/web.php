<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

use App\Http\Controllers\ImportController;
use App\Http\Controllers\JadwalLayananController;
use App\Http\Controllers\EvaluasiKehadiranController;
use App\Http\Controllers\PasienAuthController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\PortalPasienController;

// Admin & Petugas Routes
Route::middleware(['auth', 'role:admin,petugas'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        $totalPasien = \App\Models\Pasien::count();
        $totalJadwal = \App\Models\JadwalLayanan::count();

        return view('admin.dashboard', compact('totalPasien', 'totalJadwal'));
    })->name('admin.dashboard');

    Route::resource('jadwal', JadwalLayananController::class, [
        'names' => [
            'index' => 'admin.jadwal.index',
            'create' => 'admin.jadwal.create',
            'store' => 'admin.jadwal.store',
            'edit' => 'admin.jadwal.edit',
            'update' => 'admin.jadwal.update',
            'destroy' => 'admin.jadwal.destroy',
        ],
    ])->except(['show']);

    Route::get('/pasien', [PasienController::class, 'index'])->name('admin.pasien.index');
    Route::get('/pasien/{id}', [PasienController::class, 'show'])->name('admin.pasien.show');

    // Evaluasi Kehadiran
    Route::get('/evaluasi-kehadiran', [EvaluasiKehadiranController::class, 'index'])->name('admin.evaluasi.index');

    // Import Routes
    Route::get('/import/poliklinik', [ImportController::class, 'showPoliklinik'])->name('admin.import.poliklinik');
    Route::post('/import/poliklinik', [ImportController::class, 'importPoliklinik'])->name('admin.import.poliklinik.process');

    Route::get('/import/apotek', [ImportController::class, 'showApotek'])->name('admin.import.apotek');
    Route::post('/import/apotek', [ImportController::class, 'importApotek'])->name('admin.import.apotek.process');
});

// Portal Pasien Routes
Route::prefix('portal')->group(function () {
    Route::get('/login', [PasienAuthController::class, 'showLoginForm'])->name('pasien.login');
    Route::post('/login', [PasienAuthController::class, 'login'])->name('pasien.login.process');
    Route::post('/logout', [PasienAuthController::class, 'logout'])->name('pasien.logout');

    Route::middleware('auth:pasien')->group(function () {
        Route::get('/dashboard', [PortalPasienController::class, 'dashboard'])->name('pasien.dashboard');
    });
});
