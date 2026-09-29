<?php

namespace App\Http\Controllers;

use App\Models\Persetujuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ConsentPubController extends Controller
{
    /**
     * Halaman publik: pasien melihat isi surat dan menandatangani.
     * Diakses via QR Code tanpa login.
     */
    public function show(string $token)
    {
        $persetujuan = Persetujuan::with('pasien')->where('token_qr', $token)->first();

        if (! $persetujuan) {
            return response()->view('consent.error', [
                'judul' => 'Dokumen Tidak Ditemukan',
                'pesan' => 'Link yang Anda akses tidak valid atau dokumen tidak ada di sistem kami.',
            ], 404);
        }

        // Cek apakah sudah kedaluwarsa
        if ($persetujuan->status === 'menunggu_tanda_tangan' && now()->greaterThan($persetujuan->kedaluwarsa_pada)) {
            $persetujuan->status = 'kedaluwarsa';
            $persetujuan->save();
        }

        if ($persetujuan->status === 'ditandatangani') {
            return view('consent.sudah_ditandatangani', compact('persetujuan'));
        }

        if ($persetujuan->status === 'kedaluwarsa') {
            return response()->view('consent.error', [
                'judul' => 'Batas Waktu Habis',
                'pesan' => 'Dokumen ini sudah kedaluwarsa. Silakan minta petugas untuk menerbitkan ulang surat persetujuan.',
            ], 410);
        }

        if ($persetujuan->status === 'dibatalkan') {
            return response()->view('consent.error', [
                'judul' => 'Dokumen Dibatalkan',
                'pesan' => 'Surat persetujuan ini sudah dibatalkan oleh petugas.',
            ], 410);
        }

        return view('consent.tanda_tangan', compact('persetujuan'));
    }

    /**
     * Proses penyimpanan tanda tangan pasien.
     */
    public function sign(Request $request, string $token)
    {
        $persetujuan = Persetujuan::with('pasien')->where('token_qr', $token)->first();

        if (! $persetujuan || $persetujuan->status !== 'menunggu_tanda_tangan') {
            return back()->withErrors(['general' => 'Dokumen tidak valid atau sudah tidak bisa ditandatangani.']);
        }

        // Cek ulang kedaluwarsa
        if (now()->greaterThan($persetujuan->kedaluwarsa_pada)) {
            $persetujuan->status = 'kedaluwarsa';
            $persetujuan->save();

            return back()->withErrors(['general' => 'Batas waktu penandatanganan sudah habis.']);
        }

        $request->validate([
            'tanda_tangan' => 'required|string', // base64 dari canvas
        ]);

        // Decode base64 image
        $base64 = $request->tanda_tangan;
        $image = str_replace('data:image/png;base64,', '', $base64);
        $image = str_replace(' ', '+', $image);
        $imageData = base64_decode($image);

        // Simpan ke storage
        $filename = 'tanda_tangan/'.Str::random(40).'.png';
        Storage::disk('public')->put($filename, $imageData);

        // Buat hash dokumen untuk integritas
        $hashContent = $persetujuan->salinan_judul
            .$persetujuan->salinan_teks_pembuka
            .json_encode($persetujuan->salinan_poin)
            .$persetujuan->salinan_teks_penutup
            .$persetujuan->pasien_id
            .$persetujuan->token_qr;
        $hashDokumen = hash('sha256', $hashContent);

        // Update persetujuan
        $persetujuan->update([
            'status' => 'ditandatangani',
            'ditandatangani_pada' => now(),
            'tanda_tangan_path' => $filename,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'hash_dokumen' => $hashDokumen,
        ]);

        return redirect()->route('consent.selesai', $token);
    }

    /**
     * Halaman sukses setelah tanda tangan.
     */
    public function selesai(string $token)
    {
        $persetujuan = Persetujuan::with('pasien')->where('token_qr', $token)->first();

        if (! $persetujuan || $persetujuan->status !== 'ditandatangani') {
            return redirect()->route('consent.show', $token);
        }

        return view('consent.selesai', compact('persetujuan'));
    }
}
