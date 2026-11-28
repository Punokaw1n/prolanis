<?php

namespace App\Http\Controllers;

use App\Exports\PasienSrkExport;
use App\Exports\TemplatePasienSrkExport;
use App\Imports\PasienSrkImport;
use App\Models\PasienSrk;
use App\Models\PasienSrkTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PasienSrkController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $status = (string) $request->input('status', '');

        $pasienSrk = $this->filteredQuery($search, $status)
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        $templatePesan = PasienSrkTemplate::currentText();
        $totalKontak = PasienSrk::count();
        $totalDihubungi = PasienSrk::whereNotNull('terakhir_dihubungi')->count();

        return view('admin.srk.index', compact(
            'pasienSrk',
            'search',
            'status',
            'templatePesan',
            'totalKontak',
            'totalDihubungi'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $search = trim((string) $request->input('search', ''));
        $status = (string) $request->input('status', '');

        $items = $this->filteredQuery($search, $status)
            ->orderByDesc('id')
            ->get();

        return PasienSrkExport::download($items, $status);
    }

    /**
     * @return Builder<PasienSrk>
     */
    private function filteredQuery(string $search, string $status): Builder
    {
        return PasienSrk::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('no_hp', 'like', "%{$search}%");
                });
            })
            ->when($status === 'belum', fn ($q) => $q->whereNull('terakhir_dihubungi'))
            ->when($status === 'sudah', fn ($q) => $q->whereNotNull('terakhir_dihubungi'));
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'File harus berformat .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $import = new PasienSrkImport;
        Excel::import($import, $request->file('file'));

        if ($import->summary['imported'] === 0 && $import->summary['existing'] === 0) {
            return redirect()
                ->route('admin.srk.index')
                ->with(
                    'error',
                    "Tidak ada data yang berhasil disimpan ({$import->summary['skipped']} baris dilewati). Pastikan header kolom berisi Nama dan Nomor HP / No. Telepon yang valid."
                );
        }

        return redirect()
            ->route('admin.srk.index')
            ->with(
                'success',
                "Import selesai: {$import->summary['imported']} kontak baru ditambahkan, {$import->summary['existing']} sudah ada, {$import->summary['skipped']} baris dilewati."
            );
    }

    public function downloadTemplate(): StreamedResponse
    {
        return TemplatePasienSrkExport::download();
    }

    public function updateTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'isi_pesan' => 'required|string|max:2000',
        ], [
            'isi_pesan.required' => 'Template pesan WhatsApp wajib diisi.',
            'isi_pesan.max' => 'Template pesan maksimal 2000 karakter.',
        ]);

        $template = PasienSrkTemplate::first();
        if ($template) {
            $template->update(['isi_pesan' => $validated['isi_pesan']]);
        } else {
            PasienSrkTemplate::create(['isi_pesan' => $validated['isi_pesan']]);
        }

        return redirect()
            ->route('admin.srk.index')
            ->with('success', 'Template pesan WhatsApp berhasil disimpan.');
    }

    public function tandaiDihubungi(int $id): JsonResponse
    {
        $pasienSrk = PasienSrk::findOrFail($id);
        $pasienSrk->update([
            'terakhir_dihubungi' => now(),
        ]);

        return response()->json([
            'success' => true,
            'terakhir_dihubungi' => $pasienSrk->terakhir_dihubungi->translatedFormat('d M Y, H:i'),
        ]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $pasienSrk = PasienSrk::findOrFail($id);
        $nama = $pasienSrk->nama;
        $pasienSrk->delete();

        return redirect()
            ->route('admin.srk.index')
            ->with('success', "Data kontak {$nama} berhasil dihapus.");
    }

    public function destroyAll(): RedirectResponse
    {
        $jumlah = PasienSrk::count();
        PasienSrk::query()->delete();

        return redirect()
            ->route('admin.srk.index')
            ->with('success', "Seluruh data Pasien SRK ({$jumlah} kontak) berhasil dikosongkan.");
    }
}
