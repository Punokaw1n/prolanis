<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use App\Models\PasienPrb;
use Illuminate\Http\Request;

class PasienPrbController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        
        $pasienPrb = PasienPrb::with(['pasien', 'riwayatPengambilan' => function($q) {
            $q->orderBy('tanggal_jatuh_tempo', 'desc');
        }])
        ->when($search, function($query) use ($search) {
            $query->whereHas('pasien', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15)
        ->withQueryString();

        return view('admin.prb.index', compact('pasienPrb'));
    }

    public function create()
    {
        return view('admin.prb.create');
    }

    public function checkNik(Request $request)
    {
        $request->validate([
            'nik' => 'required|string'
        ]);

        $pasien = Pasien::where('nik', $request->nik)->first();
        
        if ($pasien) {
            // Check if already in PRB
            $isPrb = PasienPrb::where('pasien_id', $pasien->id)->exists();
            return response()->json([
                'found' => true,
                'is_prb' => $isPrb,
                'data' => $pasien
            ]);
        }

        return response()->json([
            'found' => false
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
            'alamat' => 'nullable|string'
        ]);

        // Find or create Pasien
        $pasien = Pasien::where('nik', $request->nik)->first();

        if (!$pasien) {
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
            'tanggal_terdaftar' => now()->toDateString()
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
        return view('admin.prb.riwayat_create', compact('prb'));
    }

    public function storeRiwayat(Request $request, $id)
    {
        $prb = PasienPrb::findOrFail($id);
        
        $request->validate([
            'tanggal_diambil' => 'required|date',
            'tanggal_jatuh_tempo' => 'required|date',
            'obat' => 'nullable|array',
            'obat.*.nama' => 'required_with:obat|string|max:255',
            'obat.*.kategori' => 'required_with:obat|in:prb,non_prb'
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
                    'sumber' => 'manual'
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

    private function normalizePhoneNumber($phone)
    {
        if (empty($phone)) return null;
        
        // Remove spaces, dashes, parentheses
        $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);
        
        // Replace leading 0 with 62
        if (strpos($phone, '0') === 0) {
            $phone = '62' . substr($phone, 1);
        }
        
        return $phone;
    }
}
