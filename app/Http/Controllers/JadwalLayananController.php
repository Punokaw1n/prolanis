<?php

namespace App\Http\Controllers;

use App\Models\JadwalLayanan;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalLayananController extends Controller
{
    public function peserta(Request $request)
    {
        $search = $request->input('search');
        $filterTanggal = $request->input('tanggal');

        $query = \App\Models\Pasien::query()
            ->join('jadwal_layanan_pasien', 'pasien.id', '=', 'jadwal_layanan_pasien.pasien_id')
            ->join('jadwal_layanan', 'jadwal_layanan_pasien.jadwal_layanan_id', '=', 'jadwal_layanan.id')
            ->select(
                'pasien.id as pasien_id',
                'pasien.nama',
                'pasien.nik',
                'pasien.no_rm',
                'pasien.alamat',
                'pasien.no_hp',
                'jadwal_layanan.id as jadwal_id',
                'jadwal_layanan.tanggal',
                'jadwal_layanan.keterangan'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pasien.nama', 'like', "%{$search}%")
                  ->orWhere('pasien.nik', 'like', "%{$search}%")
                  ->orWhere('pasien.no_rm', 'like', "%{$search}%");
            });
        }

        if ($filterTanggal) {
            $query->where('jadwal_layanan.tanggal', $filterTanggal);
        }

        $peserta = $query->orderBy('jadwal_layanan.tanggal', 'desc')
                         ->orderBy('pasien.nama', 'asc')
                         ->paginate(15)
                         ->withQueryString();

        return view('admin.jadwal.peserta', compact('peserta', 'search', 'filterTanggal'));
    }

    public function index()
    {
        $jadwal = JadwalLayanan::with('pembuat')->orderBy('tanggal', 'desc')->paginate(10);

        return view('admin.jadwal.index', compact('jadwal'));
    }

    public function create()
    {
        $initialPasiens = [];
        $patientSchedules = $this->getPatientSchedules();

        return view('admin.jadwal.create', compact('initialPasiens', 'patientSchedules'));
    }

    public function searchPasien(Request $request)
    {
        $search = $request->get('q');
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $pasiens = Pasien::with(['pemeriksaans' => function($q) {
                $q->orderBy('tanggal_periksa', 'desc')->select('id', 'pasien_id', 'tanggal_periksa');
            }])
            ->where('nama', 'like', "%{$search}%")
            ->orWhere('nik', 'like', "%{$search}%")
            ->orWhere('no_rm', 'like', "%{$search}%")
            ->orderBy('nama', 'asc')
            ->limit(30)
            ->get(['id', 'nama', 'nik', 'alamat', 'no_rm'])
            ->map(function ($p) {
                $p->kunjungan_terakhir = $p->pemeriksaans->first() ? \Carbon\Carbon::parse($p->pemeriksaans->first()->tanggal_periksa)->translatedFormat('d F Y') : null;
                unset($p->pemeriksaans);
                return $p;
            });

        return response()->json($pasiens);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $isActive = $request->has('is_active');

        if ($isActive) {
            JadwalLayanan::query()->update(['is_active' => false]);
        }

        $jadwal = JadwalLayanan::create([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'dibuat_oleh' => auth()->id(),
            'is_active' => $isActive,
        ]);

        if ($request->has('pasien_ids')) {
            $jadwal->pasien()->sync($request->pasien_ids);
        }

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal layanan berhasil ditambahkan.');
    }

    public function edit(JadwalLayanan $jadwal)
    {
        $initialPasiens = $jadwal->pasien()->with(['pemeriksaans' => function($q) {
                $q->orderBy('tanggal_periksa', 'desc')->select('id', 'pasien_id', 'tanggal_periksa');
            }])->get(['pasien.id', 'nama', 'nik', 'alamat', 'no_rm'])->map(function($p) {
                $p->kunjungan_terakhir = $p->pemeriksaans->first() ? \Carbon\Carbon::parse($p->pemeriksaans->first()->tanggal_periksa)->translatedFormat('d F Y') : null;
                unset($p->pemeriksaans);
                return $p;
            });
            
        $patientSchedules = $this->getPatientSchedules();

        return view('admin.jadwal.edit', compact('jadwal', 'initialPasiens', 'patientSchedules'));
    }

    public function update(Request $request, JadwalLayanan $jadwal)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $isActive = $request->has('is_active');

        if ($isActive) {
            JadwalLayanan::where('id', '!=', $jadwal->id)->update(['is_active' => false]);
        }

        $jadwal->update([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'is_active' => $isActive,
        ]);

        if ($request->has('pasien_ids')) {
            $jadwal->pasien()->sync($request->pasien_ids);
        } else {
            $jadwal->pasien()->detach();
        }

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal layanan berhasil diperbarui.');
    }

    public function destroy(JadwalLayanan $jadwal)
    {
        $jadwal->delete();

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal layanan berhasil dihapus.');
    }

    private function getPatientSchedules()
    {
        $allSchedules = DB::table('jadwal_layanan_pasien')
            ->join('jadwal_layanan', 'jadwal_layanan_pasien.jadwal_layanan_id', '=', 'jadwal_layanan.id')
            ->select('jadwal_layanan_pasien.pasien_id', 'jadwal_layanan.tanggal', 'jadwal_layanan.id as jadwal_id')
            ->get();

        $patientSchedules = [];
        foreach ($allSchedules as $schedule) {
            $month = substr($schedule->tanggal, 0, 7); // Format: YYYY-MM
            $formattedDate = \Carbon\Carbon::parse($schedule->tanggal)->translatedFormat('d F Y');
            
            if (!isset($patientSchedules[$schedule->pasien_id])) {
                $patientSchedules[$schedule->pasien_id] = [];
            }
            $patientSchedules[$schedule->pasien_id][] = [
                'month' => $month,
                'jadwal_id' => $schedule->jadwal_id,
                'formatted_date' => $formattedDate
            ];
        }

        return $patientSchedules;
    }
}
