<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pasien;
use App\Models\TemplatePersetujuan;
use App\Models\Persetujuan;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PersetujuanController extends Controller
{
    // Arsip Informed Consent
    public function index(Request $request)
    {
        $query = Persetujuan::with(['pasien', 'petugas'])->latest();
        
        if ($request->search) {
            $query->whereHas('pasien', function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('nik', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $persetujuans = $query->paginate(15);
        
        return view('admin.persetujuan.index', compact('persetujuans'));
    }

    // Halaman pencarian dan pratinjau sebelum menerbitkan
    public function create(Request $request)
    {
        $pasien_id = $request->pasien_id;
        $pasien = null;
        $template = TemplatePersetujuan::with('poinTemplates')->where('is_active', true)->first();
        
        if (!$template) {
            $template = TemplatePersetujuan::with('poinTemplates')->first();
        }

        if ($pasien_id) {
            $pasien = Pasien::find($pasien_id);
        }

        $pasiens = [];
        if ($request->search) {
            $pasiens = Pasien::where('nama', 'like', '%' . $request->search . '%')
                             ->orWhere('nik', 'like', '%' . $request->search . '%')
                             ->limit(10)
                             ->get();
        }

        return view('admin.persetujuan.create', compact('pasien', 'template', 'pasiens'));
    }

    // Menerbitkan persetujuan dan membuat QR
    public function store(Request $request)
    {
        $request->validate([
            'pasien_id' => 'required|exists:pasien,id',
            'template_id' => 'required|exists:template_persetujuan,id',
        ]);

        $pasien = Pasien::findOrFail($request->pasien_id);
        $template = TemplatePersetujuan::with('poinTemplates')->findOrFail($request->template_id);

        // Helper untuk mengganti placeholder
        $replacePlaceholders = function($text) use ($pasien) {
            if (!$text) return '';
            $text = str_replace('{nama_pasien}', $pasien->nama, $text);
            $text = str_replace('{nik}', $pasien->nik ?? '-', $text);
            $text = str_replace('{no_rm}', $pasien->no_rm ?? '-', $text);
            $text = str_replace('{tanggal_lahir}', $pasien->tanggal_lahir ? Carbon::parse($pasien->tanggal_lahir)->translatedFormat('d F Y') : '-', $text);
            $text = str_replace('{alamat}', $pasien->alamat ?? '-', $text);
            $text = str_replace('{tanggal}', now()->translatedFormat('d F Y'), $text);
            return $text;
        };

        $salinanPoin = $template->poinTemplates->map(function($poin) use ($replacePlaceholders) {
            return $replacePlaceholders($poin->teks_poin);
        })->toArray();

        $persetujuan = Persetujuan::create([
            'pasien_id' => $pasien->id,
            'template_persetujuan_id' => $template->id,
            'petugas_id' => auth()->id(),
            'salinan_judul' => $replacePlaceholders($template->judul),
            'salinan_teks_pembuka' => $replacePlaceholders($template->teks_pembuka),
            'salinan_teks_penutup' => $replacePlaceholders($template->teks_penutup),
            'salinan_poin' => $salinanPoin,
            'token_qr' => Str::random(60),
            'status' => 'menunggu_tanda_tangan',
            'kedaluwarsa_pada' => now()->addHour(),
        ]);

        return redirect()->route('admin.persetujuan.show', $persetujuan->id)
                         ->with('success', 'Informed Consent berhasil diterbitkan. Silakan minta pasien untuk memindai QR Code.');
    }

    // Halaman untuk petugas melihat QR Code atau arsip
    public function show($id)
    {
        $persetujuan = Persetujuan::with(['pasien', 'petugas'])->findOrFail($id);
        
        // Auto update status if expired
        if ($persetujuan->status === 'menunggu_tanda_tangan' && now()->greaterThan($persetujuan->kedaluwarsa_pada)) {
            $persetujuan->status = 'kedaluwarsa';
            $persetujuan->save();
        }

        return view('admin.persetujuan.show', compact('persetujuan'));
    }
}
