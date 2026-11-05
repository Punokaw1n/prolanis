<?php

namespace App\Http\Controllers;

use App\Models\MasterObat;
use App\Models\Pasien;
use App\Models\PasienPrb;
use App\Models\RiwayatPengambilanObatPrb;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PasienPrbController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $status = $request->get('status', 'semua');

        $query = PasienPrb::with(['pasien', 'riwayatPengambilan' => function ($q) {
            $q->orderBy('tanggal_jatuh_tempo', 'desc');
        }]);

        if ($status && in_array($status, ['aktif', 'tidak_aktif'])) {
            $query->where('status_keaktifan', $status);
        }

        if ($search) {
            $query->whereHas('pasien', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $perPage = $request->integer('per_page', 15);
        $pasienPrb = $query->orderBy('created_at', 'desc')
            ->paginate($perPage > 0 ? $perPage : 15)
            ->withQueryString();

        return view('admin.prb.index', compact('pasienPrb', 'search', 'status'));
    }

    public function register(Request $request)
    {
        $mode = $request->get('mode', 'harian'); // 'harian', 'bulan', 'rentang'
        $tanggal = $request->get('tanggal', now()->toDateString());
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);
        $tanggalMulai = $request->get('tanggal_mulai', now()->startOfMonth()->toDateString());
        $tanggalAkhir = $request->get('tanggal_akhir', now()->toDateString());
        $search = $request->get('search');

        $query = RiwayatPengambilanObatPrb::with(['pasienPrb.pasien', 'obatPrb'])
            ->whereNotNull('tanggal_diambil');

        if ($mode === 'bulan') {
            $query->whereYear('tanggal_diambil', $tahun)
                ->whereMonth('tanggal_diambil', $bulan);
        } elseif ($mode === 'rentang') {
            if ($tanggalMulai && $tanggalAkhir) {
                $query->whereBetween('tanggal_diambil', [$tanggalMulai, $tanggalAkhir]);
            }
        } else {
            $query->whereDate('tanggal_diambil', $tanggal);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('pasienPrb.pasien', function ($qp) use ($search) {
                    $qp->where('nama', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('no_bpjs', 'like', "%{$search}%");
                })->orWhereHas('obatPrb', function ($qo) use ($search) {
                    $qo->where('nama_obat', 'like', "%{$search}%");
                });
            });
        }

        $allMatching = (clone $query)->get();
        $totalKunjungan = $allMatching->count();
        $totalPasien = $allMatching->pluck('pasien_prb_id')->unique()->count();
        $totalButirObat = $allMatching->sum(fn ($r) => $r->obatPrb->sum('jumlah_obat'));

        $perPage = $request->integer('per_page', 25);
        $riwayatList = $query->orderBy('tanggal_diambil', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($perPage > 0 ? $perPage : 25)
            ->withQueryString();

        $namaBulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if ($mode === 'bulan') {
            $periodeLabel = 'Bulan '.($namaBulanIndo[$bulan] ?? '').' '.$tahun;
        } elseif ($mode === 'rentang') {
            $periodeLabel = Carbon::parse($tanggalMulai)->translatedFormat('d M Y').' s/d '.Carbon::parse($tanggalAkhir)->translatedFormat('d M Y');
        } else {
            $periodeLabel = Carbon::parse($tanggal)->translatedFormat('l, d F Y');
        }

        return view('admin.prb.register', compact(
            'riwayatList', 'mode', 'tanggal', 'bulan', 'tahun',
            'tanggalMulai', 'tanggalAkhir', 'search', 'totalKunjungan',
            'totalPasien', 'totalButirObat', 'periodeLabel', 'namaBulanIndo'
        ));
    }

    public function cetakRegister(Request $request)
    {
        $mode = $request->get('mode', 'harian');
        $tanggal = $request->get('tanggal', now()->toDateString());
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);
        $tanggalMulai = $request->get('tanggal_mulai', now()->startOfMonth()->toDateString());
        $tanggalAkhir = $request->get('tanggal_akhir', now()->toDateString());
        $search = $request->get('search');

        $query = RiwayatPengambilanObatPrb::with(['pasienPrb.pasien', 'obatPrb'])
            ->whereNotNull('tanggal_diambil');

        if ($mode === 'bulan') {
            $query->whereYear('tanggal_diambil', $tahun)
                ->whereMonth('tanggal_diambil', $bulan);
        } elseif ($mode === 'rentang') {
            if ($tanggalMulai && $tanggalAkhir) {
                $query->whereBetween('tanggal_diambil', [$tanggalMulai, $tanggalAkhir]);
            }
        } else {
            $query->whereDate('tanggal_diambil', $tanggal);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('pasienPrb.pasien', function ($qp) use ($search) {
                    $qp->where('nama', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('no_bpjs', 'like', "%{$search}%");
                })->orWhereHas('obatPrb', function ($qo) use ($search) {
                    $qo->where('nama_obat', 'like', "%{$search}%");
                });
            });
        }

        $riwayatList = $query->orderBy('tanggal_diambil', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $namaBulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if ($mode === 'bulan') {
            $periodeJudul = 'BULAN : '.strtoupper(($namaBulanIndo[$bulan] ?? '').' '.$tahun);
        } elseif ($mode === 'rentang') {
            $periodeJudul = 'PERIODE : '.strtoupper(Carbon::parse($tanggalMulai)->translatedFormat('d M Y').' S/D '.Carbon::parse($tanggalAkhir)->translatedFormat('d M Y'));
        } else {
            $periodeJudul = 'TANGGAL : '.strtoupper(Carbon::parse($tanggal)->translatedFormat('d F Y'));
        }

        return view('admin.prb.register_cetak', compact(
            'riwayatList', 'mode', 'periodeJudul', 'tanggal', 'bulan', 'tahun'
        ));
    }

    public function pengingat(Request $request)
    {
        $search = $request->search;

        $riwayatPengingat = RiwayatPengambilanObatPrb::with(['pasienPrb.pasien'])
            ->whereNull('tanggal_diambil')
            ->where('tanggal_jatuh_tempo', '<=', now()->addDays(3)->toDateString())
            ->whereHas('pasienPrb', function ($q) {
                $q->where('status_keaktifan', 'aktif');
            })
            ->when($search, function ($query) use ($search) {
                $query->whereHas('pasienPrb.pasien', function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            })
            // Sort by earliest tanggal_jatuh_tempo (most overdue)
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return view('admin.prb.pengingat', compact('riwayatPengingat', 'search'));
    }

    public function tandaiDiingatkan($id)
    {
        $riwayat = RiwayatPengambilanObatPrb::findOrFail($id);
        $riwayat->update([
            'terakhir_diingatkan' => now(),
        ]);

        return response()->json([
            'success' => true,
            'terakhir_diingatkan' => $riwayat->terakhir_diingatkan->translatedFormat('d M Y, H:i'),
        ]);
    }

    public function create()
    {
        return view('admin.prb.create');
    }

    public function checkNik(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
        ]);

        $pasien = Pasien::where('nik', $request->nik)->first();

        if ($pasien) {
            // Check if already in PRB
            $isPrb = PasienPrb::where('pasien_id', $pasien->id)->exists();

            return response()->json([
                'found' => true,
                'is_prb' => $isPrb,
                'data' => $pasien,
            ]);
        }

        return response()->json([
            'found' => false,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|string|max:16',
            'nama' => 'required_without:pasien_id|string|max:255',
            'no_rm' => 'nullable|string|max:255',
            'no_bpjs' => 'nullable|string|max:255',
            'tanggal_lahir' => 'required_without:pasien_id|date',
            'jenis_kelamin' => 'required_without:pasien_id|in:L,P',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        // Find or create Pasien
        $pasien = Pasien::where('nik', $request->nik)->first();

        if (! $pasien) {
            $pasien = Pasien::create([
                'nik' => $request->nik,
                'nama' => $request->nama,
                'no_rm' => $request->no_rm,
                'no_bpjs' => $request->no_bpjs,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'no_hp' => $this->normalizePhoneNumber($request->no_hp),
                'alamat' => $request->alamat,
            ]);
        }

        // Check if already PRB
        if (PasienPrb::where('pasien_id', $pasien->id)->exists()) {
            return redirect()->route('admin.prb.index')->with('error', 'Pasien dengan NIK tersebut sudah terdaftar sebagai peserta PRB.');
        }

        // Register as PRB
        PasienPrb::create([
            'pasien_id' => $pasien->id,
            'status_keaktifan' => 'aktif',
            'tanggal_terdaftar' => now()->toDateString(),
        ]);

        return redirect()->route('admin.prb.index')->with('success', 'Pasien PRB berhasil ditambahkan.');
    }

    public function show($id)
    {
        $prb = PasienPrb::with(['pasien', 'riwayatPengambilan.obatPrb', 'riwayatPengambilan.pencatat'])->findOrFail($id);

        // Sorting history desc
        $riwayat = $prb->riwayatPengambilan->sortByDesc('tanggal_jatuh_tempo');

        // Kelompokkan riwayat berdasarkan diagnosa_group_key
        $diagnosaGroups = $riwayat->groupBy(function ($r) {
            return $r->diagnosa_group_key;
        })->map(function ($items, $key) {
            $first = $items->first();

            return [
                'key' => $key,
                'kode' => $first?->kode_diagnosa,
                'diagnosa' => $first?->diagnosa,
                'label' => $first?->diagnosa_lengkap ?? $key,
                'count' => $items->count(),
                'items' => $items,
            ];
        });

        return view('admin.prb.show', compact('prb', 'riwayat', 'diagnosaGroups'));
    }

    public function createRiwayat($id)
    {
        if (! auth()->user()?->canCatatPengambilan()) {
            abort(403, 'Akses pencatatan obat khusus untuk dokter.');
        }

        $prb = PasienPrb::with('pasien')->findOrFail($id);
        $masterObatPrb = MasterObat::prb()->where('is_active', true)->orderBy('nama_obat')->pluck('nama_obat');
        $masterObatNonPrb = MasterObat::nonPrb()->where('is_active', true)->orderBy('nama_obat')->pluck('nama_obat');

        $programPasien = $this->buildProgramPasien($prb);
        $presetDiagnosaPrb = $this->getPresetDiagnosaPrb();

        $lastRiwayat = $prb->riwayatPengambilan()
            ->whereHas('obatPrb')
            ->orderByDesc('tanggal_jatuh_tempo')
            ->with('obatPrb')
            ->first();

        // Ambil daftar diagnosa unik yang pernah dicatat untuk pasien ini sebagai saran
        $saranDiagnosa = $prb->riwayatPengambilan()
            ->whereNotNull('diagnosa')
            ->pluck('diagnosa', 'kode_diagnosa')
            ->filter()
            ->toArray();

        return view('admin.prb.riwayat_create', compact('prb', 'masterObatPrb', 'masterObatNonPrb', 'lastRiwayat', 'saranDiagnosa', 'programPasien', 'presetDiagnosaPrb'));
    }

    public function storeRiwayat(Request $request, $id)
    {
        if (! auth()->user()?->canCatatPengambilan()) {
            abort(403, 'Akses pencatatan obat khusus untuk dokter.');
        }

        $prb = PasienPrb::findOrFail($id);

        $request->validate([
            'tanggal_diambil' => 'required|date',
            'tanggal_jatuh_tempo' => 'required|date',
            'kode_diagnosa' => 'nullable|string|max:50',
            'diagnosa' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
            'obat' => 'nullable|array',
            'obat.*.nama' => 'required_with:obat|string|max:255',
            'obat.*.kategori' => 'required_with:obat|in:prb,non_prb',
            'obat.*.signa' => 'nullable|string|max:100',
            'obat.*.jumlah_hari' => 'nullable|integer|min:1',
            'obat.*.jumlah_obat' => 'nullable|integer|min:1',
            'obat.*.aturan_minum' => 'nullable|string|max:100',
        ]);

        return \DB::transaction(function () use ($prb, $request, $id) {
            // Proteksi race condition / double-click:
            // Cek apakah ada riwayat yang baru dibuat dalam 10 detik terakhir untuk pasien ini dengan tanggal_diambil yang sama
            $recentDuplicate = $prb->riwayatPengambilan()
                ->where('tanggal_diambil', $request->tanggal_diambil)
                ->where('created_at', '>=', now()->subSeconds(10))
                ->exists();

            if ($recentDuplicate) {
                return redirect()->route('admin.prb.show', $id)
                    ->with('success', 'Riwayat pengambilan obat berhasil dicatat.');
            }

            // 1. Cari riwayat yang belum diambil (pending) dengan diagnosa yang sesuai
            $pendingQuery = $prb->riwayatPengambilan()
                ->whereNull('tanggal_diambil');

            if ($request->filled('kode_diagnosa')) {
                $pendingQuery->where('kode_diagnosa', $request->kode_diagnosa);
            }

            $pendingRiwayat = $pendingQuery->orderBy('tanggal_jatuh_tempo', 'asc')->first();

            // Jika tidak ditemukan dengan kode diagnosa yang sama dan request tidak menentukan kode diagnosa
            if (! $pendingRiwayat && ! $request->filled('kode_diagnosa')) {
                $pendingRiwayat = $prb->riwayatPengambilan()
                    ->whereNull('tanggal_diambil')
                    ->orderBy('tanggal_jatuh_tempo', 'asc')
                    ->first();
            }

            if ($pendingRiwayat) {
                // Update riwayat yang sudah dijadwalkan sebelumnya
                $pendingRiwayat->update([
                    'tanggal_diambil' => $request->tanggal_diambil,
                    'kode_diagnosa' => $request->kode_diagnosa,
                    'diagnosa' => $request->diagnosa ?: ($prb->diagnosa ?: null),
                    'catatan' => $request->catatan,
                    'sumber' => 'manual',
                    'dicatat_oleh' => auth()->id() ?? null,
                ]);
                $riwayatToAttach = $pendingRiwayat;
            } else {
                // Jika tidak ada jadwal sebelumnya (misal baru pertama kali),
                // buat riwayat baru untuk pengambilan hari ini
                $riwayatToAttach = $prb->riwayatPengambilan()->create([
                    'tanggal_jatuh_tempo' => $request->tanggal_diambil, // Anggap tepat waktu
                    'tanggal_diambil' => $request->tanggal_diambil,
                    'kode_diagnosa' => $request->kode_diagnosa,
                    'diagnosa' => $request->diagnosa ?: ($prb->diagnosa ?: null),
                    'catatan' => $request->catatan,
                    'sumber' => 'manual',
                    'dicatat_oleh' => auth()->id() ?? null,
                ]);
            }

            // 2. Simpan daftar obat ke riwayat yang baru saja diambil
            if ($request->has('obat') && is_array($request->obat)) {
                foreach ($request->obat as $obatData) {
                    $namaObatClean = trim($obatData['nama']);
                    $riwayatToAttach->obatPrb()->create([
                        'nama_obat' => $namaObatClean,
                        'kategori' => $obatData['kategori'],
                        'signa' => $obatData['signa'] ?? '1 x 1',
                        'jumlah_hari' => $obatData['jumlah_hari'] ?? 30,
                        'jumlah_obat' => $obatData['jumlah_obat'] ?? 30,
                        'aturan_minum' => $obatData['aturan_minum'] ?? null,
                        'sumber' => 'manual',
                    ]);

                    // Auto-register ke MasterObat jika belum ada
                    MasterObat::firstOrCreate(
                        [
                            'nama_obat' => $namaObatClean,
                            'kategori' => $obatData['kategori'],
                        ],
                        [
                            'sumber' => 'manual',
                            'is_active' => true,
                        ]
                    );
                }
            }

            // 3. Buat jadwal riwayat BARU untuk jatuh tempo BERIKUTNYA
            $prb->riwayatPengambilan()->create([
                'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                'tanggal_diambil' => null, // Belum diambil
                'kode_diagnosa' => $request->kode_diagnosa,
                'diagnosa' => $request->diagnosa ?: ($prb->diagnosa ?: null),
                'sumber' => 'manual',
                'dicatat_oleh' => auth()->id() ?? null,
            ]);

            return redirect()->route('admin.prb.show', $id)->with('success', 'Riwayat pengambilan obat berhasil dicatat.');
        });
    }

    public function editRiwayat($riwayat_id)
    {
        if (! auth()->user()?->canCatatPengambilan()) {
            abort(403, 'Akses riwayat obat khusus untuk dokter.');
        }

        $riwayat = RiwayatPengambilanObatPrb::with(['pasienPrb.pasien', 'obatPrb'])->findOrFail($riwayat_id);

        if ($riwayat->isFarmasiSudah()) {
            return redirect()->route('admin.prb.show', $riwayat->pasien_prb_id)
                ->with('error', 'Data riwayat obat tidak dapat diubah karena status obat di menu farmasi sudah selesai.');
        }

        $prb = $riwayat->pasienPrb;
        $masterObatPrb = MasterObat::prb()->where('is_active', true)->orderBy('nama_obat')->pluck('nama_obat');
        $masterObatNonPrb = MasterObat::nonPrb()->where('is_active', true)->orderBy('nama_obat')->pluck('nama_obat');

        $programPasien = $this->buildProgramPasien($prb, $riwayat->id);
        $presetDiagnosaPrb = $this->getPresetDiagnosaPrb();

        $lastRiwayat = $prb->riwayatPengambilan()
            ->where('id', '!=', $riwayat->id)
            ->whereHas('obatPrb')
            ->orderByDesc('tanggal_jatuh_tempo')
            ->with('obatPrb')
            ->first();

        // Ambil daftar diagnosa unik yang pernah dicatat untuk pasien ini sebagai saran
        $saranDiagnosa = $prb->riwayatPengambilan()
            ->whereNotNull('diagnosa')
            ->pluck('diagnosa', 'kode_diagnosa')
            ->filter()
            ->toArray();

        return view('admin.prb.riwayat_edit', compact('prb', 'riwayat', 'masterObatPrb', 'masterObatNonPrb', 'lastRiwayat', 'saranDiagnosa', 'programPasien', 'presetDiagnosaPrb'));
    }

    public function updateRiwayat(Request $request, $riwayat_id)
    {
        if (! auth()->user()?->canCatatPengambilan()) {
            abort(403, 'Akses riwayat obat khusus untuk dokter.');
        }

        $riwayat = RiwayatPengambilanObatPrb::findOrFail($riwayat_id);

        if ($riwayat->isFarmasiSudah()) {
            return redirect()->route('admin.prb.show', $riwayat->pasien_prb_id)
                ->with('error', 'Data riwayat obat tidak dapat diubah karena status obat di menu farmasi sudah selesai.');
        }

        $request->validate([
            'tanggal_jatuh_tempo' => 'required|date',
            'tanggal_diambil' => 'nullable|date',
            'kode_diagnosa' => 'nullable|string|max:50',
            'diagnosa' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
            'obat' => 'nullable|array',
            'obat.*.nama' => 'required_with:obat|string|max:255',
            'obat.*.kategori' => 'required_with:obat|in:prb,non_prb',
            'obat.*.signa' => 'nullable|string|max:100',
            'obat.*.jumlah_hari' => 'nullable|integer|min:1',
            'obat.*.jumlah_obat' => 'nullable|integer|min:1',
            'obat.*.aturan_minum' => 'nullable|string|max:100',
        ]);

        $riwayat->update([
            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
            'tanggal_diambil' => $request->tanggal_diambil,
            'kode_diagnosa' => $request->kode_diagnosa,
            'diagnosa' => $request->diagnosa ?: ($riwayat->diagnosa ?: null),
            'catatan' => $request->catatan,
        ]);

        // Sync Obat (Delete all and recreate)
        $riwayat->obatPrb()->delete();

        if ($request->has('obat') && is_array($request->obat)) {
            foreach ($request->obat as $obatData) {
                $namaObatClean = trim($obatData['nama']);
                $riwayat->obatPrb()->create([
                    'nama_obat' => $namaObatClean,
                    'kategori' => $obatData['kategori'],
                    'signa' => $obatData['signa'] ?? '1 x 1',
                    'jumlah_hari' => $obatData['jumlah_hari'] ?? 30,
                    'jumlah_obat' => $obatData['jumlah_obat'] ?? 30,
                    'aturan_minum' => $obatData['aturan_minum'] ?? null,
                    'sumber' => 'manual',
                ]);

                // Auto-register ke MasterObat jika belum ada
                MasterObat::firstOrCreate(
                    [
                        'nama_obat' => $namaObatClean,
                        'kategori' => $obatData['kategori'],
                    ],
                    [
                        'sumber' => 'manual',
                        'is_active' => true,
                    ]
                );
            }
        }

        return redirect()->route('admin.prb.show', $riwayat->pasien_prb_id)->with('success', 'Data riwayat pengambilan obat berhasil diperbarui.');
    }

    public function destroyRiwayat($riwayat_id)
    {
        if (! auth()->user()?->canCatatPengambilan()) {
            abort(403, 'Akses riwayat obat khusus untuk dokter.');
        }

        $riwayat = RiwayatPengambilanObatPrb::findOrFail($riwayat_id);

        if ($riwayat->isFarmasiSudah()) {
            return redirect()->route('admin.prb.show', $riwayat->pasien_prb_id)
                ->with('error', 'Data riwayat obat tidak dapat dihapus karena status obat di menu farmasi sudah selesai.');
        }

        $pasien_prb_id = $riwayat->pasien_prb_id;

        $riwayat->obatPrb()->delete();
        $riwayat->delete();

        return redirect()->route('admin.prb.show', $pasien_prb_id)->with('success', 'Riwayat pengambilan obat berhasil dihapus.');
    }

    private function normalizePhoneNumber($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Remove spaces, dashes, parentheses, plus
        $phone = preg_replace('/[\s\-\(\)\+]+/', '', $phone);

        // Replace leading 0 with 62
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        return $phone;
    }

    public function toggleStatus($id)
    {
        if (! auth()->user()?->canKelolaPasien()) {
            abort(403, 'Anda tidak memiliki hak untuk mengubah status keaktifan pasien.');
        }

        $prb = PasienPrb::findOrFail($id);
        $prb->status_keaktifan = $prb->status_keaktifan === 'aktif' ? 'tidak_aktif' : 'aktif';
        $prb->save();

        return redirect()->back()->with('success', 'Status keaktifan pasien berhasil diubah menjadi: '.($prb->status_keaktifan == 'aktif' ? 'Aktif' : 'Tidak Aktif'));
    }

    /**
     * @return array<int, array{kode: string, nama: string}>
     */
    private function getPresetDiagnosaPrb(): array
    {
        return [
            ['kode' => 'E11', 'nama' => 'Diabetes Melitus'],
            ['kode' => 'I10', 'nama' => 'Hipertensi'],
            ['kode' => 'I25', 'nama' => 'Penyakit Jantung'],
            ['kode' => 'J45', 'nama' => 'Asma Bronkial'],
            ['kode' => 'J44', 'nama' => 'PPOK'],
            ['kode' => 'I69', 'nama' => 'Stroke / Pasca Stroke'],
            ['kode' => 'F20', 'nama' => 'Skizofrenia'],
            ['kode' => 'G40', 'nama' => 'Epilepsi'],
            ['kode' => 'M32', 'nama' => 'SLE'],
        ];
    }

    private function buildProgramPasien(PasienPrb $prb, ?int $excludeRiwayatId = null)
    {
        $riwayatDenganObat = $prb->riwayatPengambilan()
            ->when($excludeRiwayatId, function ($q) use ($excludeRiwayatId) {
                return $q->where('id', '!=', $excludeRiwayatId);
            })
            ->whereHas('obatPrb')
            ->with('obatPrb')
            ->orderByDesc('tanggal_jatuh_tempo')
            ->get();

        $programPasien = $riwayatDenganObat->groupBy(function ($r) {
            return $r->diagnosa_group_key;
        })->map(function ($items, $key) {
            $last = $items->first();

            return [
                'key' => $key,
                'kode' => $last->kode_diagnosa ?: '',
                'diagnosa' => $last->diagnosa ?: $key,
                'label' => $last->diagnosa_lengkap,
                'count_riwayat' => $items->count(),
                'last_date' => $last->tanggal_diambil ? $last->tanggal_diambil->format('d/m/Y') : ($last->tanggal_jatuh_tempo ? $last->tanggal_jatuh_tempo->format('d/m/Y') : '-'),
                'obat_list' => $last->obatPrb->map(function ($o) {
                    return [
                        'nama' => $o->nama_obat,
                        'kategori' => $o->kategori,
                        'signa' => $o->signa ?? '1 x 1',
                        'jumlah_hari' => $o->jumlah_hari ?? 30,
                        'jumlah_obat' => $o->jumlah_obat ?? 30,
                        'aturan_minum' => $o->aturan_minum ?? 'Sesudah Makan',
                    ];
                })->values()->toArray(),
            ];
        })->values();

        if ($programPasien->isEmpty() && ! empty($prb->diagnosa)) {
            $programPasien->push([
                'key' => strtoupper(trim($prb->diagnosa)),
                'kode' => '',
                'diagnosa' => $prb->diagnosa,
                'label' => $prb->diagnosa,
                'count_riwayat' => 0,
                'last_date' => '-',
                'obat_list' => [],
            ]);
        }

        return $programPasien;
    }
}
