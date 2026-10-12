<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login');
});

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

use App\Http\Controllers\ConsentPubController;
use App\Http\Controllers\EvaluasiKehadiranController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\JadwalLayananController;
use App\Http\Controllers\LogAktivitasController;
use App\Http\Controllers\PasienAuthController;
use App\Http\Controllers\PasienController;
use App\Http\Controllers\PasienPrbController;
use App\Http\Controllers\PersetujuanController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\PortalPasienController;
use App\Http\Controllers\TemplatePersetujuanController;
use App\Models\JadwalLayanan;
use App\Models\LogAktivitas;
use App\Models\Pasien;
use App\Models\Persetujuan;
use App\Models\User;
use Carbon\Carbon;

// Admin & Petugas Routes
Route::middleware(['auth', 'role:admin,petugas'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        $totalPasien = Pasien::count();
        $totalJadwal = JadwalLayanan::count();
        $totalPetugas = User::where('is_active', true)->count();
        $pendingConsent = Persetujuan::where('status', 'menunggu')->count();

        $upcomingJadwal = JadwalLayanan::where('tanggal', '>=', Carbon::today())
            ->orderBy('tanggal', 'asc')
            ->take(4)
            ->get();

        $recentLogs = LogAktivitas::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalPasien', 'totalJadwal', 'totalPetugas', 'pendingConsent',
            'upcomingJadwal', 'recentLogs'
        ));
    })->name('admin.dashboard');

    Route::get('/peserta-terjadwal', [JadwalLayananController::class, 'peserta'])->name('admin.jadwal.peserta');

    Route::get('/jadwal/search-pasien', [JadwalLayananController::class, 'searchPasien'])->name('admin.jadwal.searchPasien');

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
    Route::put('/pasien/{id}/toggle-status', [PasienController::class, 'toggleStatus'])->name('admin.pasien.toggle-status');

    Route::resource('petugas', PetugasController::class, [
        'names' => [
            'index' => 'admin.petugas.index',
            'create' => 'admin.petugas.create',
            'store' => 'admin.petugas.store',
            'edit' => 'admin.petugas.edit',
            'update' => 'admin.petugas.update',
        ],
    ])->except(['destroy']);

    Route::patch('/petugas/{petuga}/toggle-status', [PetugasController::class, 'toggleStatus'])->name('admin.petugas.toggle-status');

    // Evaluasi Kehadiran
    Route::get('/evaluasi-kehadiran', [EvaluasiKehadiranController::class, 'index'])->name('admin.evaluasi.index');
    Route::post('/evaluasi-kehadiran/{pasien}/konfirmasi', [EvaluasiKehadiranController::class, 'updateKonfirmasi'])->name('admin.evaluasi.konfirmasi');
    // Template Persetujuan (Hanya Admin yang bisa kelola)
    Route::middleware('role:admin')->group(function () {
        Route::get('/template-persetujuan', [TemplatePersetujuanController::class, 'index'])->name('admin.template.index');
        Route::post('/template-persetujuan', [TemplatePersetujuanController::class, 'store'])->name('admin.template.store');
    });

    // Penerbitan & Kelola Informed Consent
    Route::get('/persetujuan/buat', [PersetujuanController::class, 'create'])->name('admin.persetujuan.create');
    Route::post('/persetujuan/buat', [PersetujuanController::class, 'store'])->name('admin.persetujuan.store');
    Route::get('/persetujuan/{id}', [PersetujuanController::class, 'show'])->name('admin.persetujuan.show');
    Route::get('/persetujuan/{id}/status', [PersetujuanController::class, 'checkStatus'])->name('admin.persetujuan.status');
    Route::get('/persetujuan/{id}/pdf', [PersetujuanController::class, 'exportPdf'])->name('admin.persetujuan.pdf');
    Route::get('/persetujuan', [PersetujuanController::class, 'index'])->name('admin.persetujuan.index'); // Arsip

    // Import Routes
    Route::get('/import/poliklinik', [ImportController::class, 'showPoliklinik'])->name('admin.import.poliklinik');
    Route::post('/import/poliklinik', [ImportController::class, 'importPoliklinik'])->name('admin.import.poliklinik.process');

    Route::get('/import/apotek', [ImportController::class, 'showApotek'])->name('admin.import.apotek');
    Route::post('/import/apotek', [ImportController::class, 'importApotek'])->name('admin.import.apotek.process');

    Route::get('/import/prb', [ImportController::class, 'showPrb'])->name('admin.import.prb');
    Route::post('/import/prb', [ImportController::class, 'importPrb'])->name('admin.import.prb.process');

    // Program Rujuk Balik (PRB)
    Route::prefix('prb')->group(function () {
        Route::get('/', [PasienPrbController::class, 'index'])->name('admin.prb.index');
        Route::get('/pengingat', [PasienPrbController::class, 'pengingat'])->name('admin.prb.pengingat');
        Route::post('/pengingat/{id}/tandai', [PasienPrbController::class, 'tandaiDiingatkan'])->name('admin.prb.pengingat.tandai');
        Route::get('/create', [PasienPrbController::class, 'create'])->name('admin.prb.create');
        Route::post('/check-nik', [PasienPrbController::class, 'checkNik'])->name('admin.prb.check-nik');
        Route::post('/', [PasienPrbController::class, 'store'])->name('admin.prb.store');
        Route::get('/{id}', [PasienPrbController::class, 'show'])->name('admin.prb.show');
        Route::put('/{id}/toggle-status', [PasienPrbController::class, 'toggleStatus'])->name('admin.prb.toggle-status');
        // Riwayat Pengambilan Obat
        Route::get('/{id}/riwayat/create', [PasienPrbController::class, 'createRiwayat'])->name('admin.prb.riwayat.create');
        Route::post('/{id}/riwayat', [PasienPrbController::class, 'storeRiwayat'])->name('admin.prb.riwayat.store');
        Route::get('/riwayat/{riwayat_id}/edit', [PasienPrbController::class, 'editRiwayat'])->name('admin.prb.riwayat.edit');
        Route::put('/riwayat/{riwayat_id}', [PasienPrbController::class, 'updateRiwayat'])->name('admin.prb.riwayat.update');
        Route::delete('/riwayat/{riwayat_id}', [PasienPrbController::class, 'destroyRiwayat'])->name('admin.prb.riwayat.destroy');
    });

    Route::get('/log-aktivitas', [LogAktivitasController::class, 'index'])->name('admin.log.index');
});

// Portal Pasien Routes
Route::prefix('portal')->group(function () {
    Route::get('/login', [PasienAuthController::class, 'showLoginForm'])->name('pasien.login');
    Route::post('/login', [PasienAuthController::class, 'login'])->name('pasien.login.process');
    Route::post('/logout', [PasienAuthController::class, 'logout'])->name('pasien.logout');

    Route::middleware('auth:pasien')->group(function () {
        Route::get('/dashboard', [PortalPasienController::class, 'dashboard'])->name('pasien.dashboard');
    });

    // Halaman Tanda Tangan Publik (diakses via QR Code, tanpa login)
    Route::get('/consent/{token}', [ConsentPubController::class, 'show'])->name('consent.show');
    Route::post('/consent/{token}/sign', [ConsentPubController::class, 'sign'])->name('consent.sign');
    Route::get('/consent/{token}/selesai', [ConsentPubController::class, 'selesai'])->name('consent.selesai');
});
