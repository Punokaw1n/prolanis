<?php

namespace App\Http\Controllers;

use App\Models\MasterObat;
use App\Models\Pasien;
use App\Models\PasienPrb;
use App\Models\RiwayatPengambilanObatPrb;
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

        $pasienPrb = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.prb.index', compact('pasienPrb', 'search', 'status'));
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
            ->paginate(15)
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
        $prb = PasienPrb::with(['pasien', 'riwayatPengambilan.obatPrb'])->findOrFail($id);

        // Sorting history desc
        $riwayat = $prb->riwayatPengambilan->sortByDesc('tanggal_jatuh_tempo');

        return view('admin.prb.show', compact('prb', 'riwayat'));
    }

    public function createRiwayat($id)
    {
        $prb = PasienPrb::with('pasien')->findOrFail($id);
        $masterObatPrb = MasterObat::prb()->where('is_active', true)->orderBy('nama_obat')->pluck('nama_obat');
        $masterObatNonPrb = MasterObat::nonPrb()->where('is_active', true)->orderBy('nama_obat')->pluck('nama_obat');

        return view('admin.prb.riwayat_create', compact('prb', 'masterObatPrb', 'masterObatNonPrb'));
    }

    public function storeRiwayat(Request $request, $id)
    {
        $prb = PasienPrb::findOrFail($id);

        $request->validate([
            'tanggal_diambil' => 'required|date',
            'tanggal_jatuh_tempo' => 'required|date',
            'obat' => 'nullable|array',
            'obat.*.nama' => 'required_with:obat|string|max:255',
            'obat.*.kategori' => 'required_with:obat|in:prb,non_prb',
        ]);

        // 1. Cari riwayat yang belum diambil (pending)
        $pendingRiwayat = $prb->riwayatPengambilan()
            ->whereNull('tanggal_diambil')
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->first();

        if ($pendingRiwayat) {
            // Update riwayat yang sudah dijadwalkan sebelumnya
            $pendingRiwayat->update([
                'tanggal_diambil' => $request->tanggal_diambil,
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
                'sumber' => 'manual',
                'dicatat_oleh' => auth()->id() ?? null,
            ]);
        }

        // 2. Simpan daftar obat ke riwayat yang baru saja diambil
        if ($request->has('obat') && is_array($request->obat)) {
            foreach ($request->obat as $obatData) {
                $riwayatToAttach->obatPrb()->create([
                    'nama_obat' => $obatData['nama'],
                    'kategori' => $obatData['kategori'],
                    'sumber' => 'manual',
                ]);
            }
        }

        // 3. Buat jadwal riwayat BARU untuk jatuh tempo BERIKUTNYA
        $prb->riwayatPengambilan()->create([
            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
            'tanggal_diambil' => null, // Belum diambil
            'sumber' => 'manual',
            'dicatat_oleh' => auth()->id() ?? null,
        ]);

        return redirect()->route('admin.prb.show', $id)->with('success', 'Riwayat pengambilan obat berhasil dicatat.');
    }

    public function editRiwayat($riwayat_id)
    {
        $riwayat = RiwayatPengambilanObatPrb::with(['pasienPrb.pasien', 'obatPrb'])->findOrFail($riwayat_id);
        $prb = $riwayat->pasienPrb;
        $masterObatPrb = MasterObat::prb()->where('is_active', true)->orderBy('nama_obat')->pluck('nama_obat');
        $masterObatNonPrb = MasterObat::nonPrb()->where('is_active', true)->orderBy('nama_obat')->pluck('nama_obat');

        return view('admin.prb.riwayat_edit', compact('prb', 'riwayat', 'masterObatPrb', 'masterObatNonPrb'));
    }

    public function updateRiwayat(Request $request, $riwayat_id)
    {
        $riwayat = RiwayatPengambilanObatPrb::findOrFail($riwayat_id);

        $request->validate([
            'tanggal_jatuh_tempo' => 'required|date',
            'tanggal_diambil' => 'nullable|date',
            'obat' => 'nullable|array',
            'obat.*.nama' => 'required_with:obat|string|max:255',
            'obat.*.kategori' => 'required_with:obat|in:prb,non_prb',
        ]);

        $riwayat->update([
            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
            'tanggal_diambil' => $request->tanggal_diambil,
        ]);

        // Sync Obat (Delete all and recreate)
        $riwayat->obatPrb()->delete();

        if ($request->has('obat') && is_array($request->obat)) {
            foreach ($request->obat as $obatData) {
                $riwayat->obatPrb()->create([
                    'nama_obat' => $obatData['nama'],
                    'kategori' => $obatData['kategori'],
                    'sumber' => 'manual',
                ]);
            }
        }

        return redirect()->route('admin.prb.show', $riwayat->pasien_prb_id)->with('success', 'Data riwayat pengambilan obat berhasil diperbarui.');
    }

    public function destroyRiwayat($riwayat_id)
    {
        $riwayat = RiwayatPengambilanObatPrb::findOrFail($riwayat_id);
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
        $prb = PasienPrb::findOrFail($id);
        $prb->status_keaktifan = $prb->status_keaktifan === 'aktif' ? 'tidak_aktif' : 'aktif';
        $prb->save();

        return redirect()->back()->with('success', 'Status keaktifan pasien berhasil diubah menjadi: '.($prb->status_keaktifan == 'aktif' ? 'Aktif' : 'Tidak Aktif'));
    }
}
