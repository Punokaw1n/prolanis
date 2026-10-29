<?php

namespace App\Http\Controllers;

use App\Exports\TemplateObatUmumExport;
use App\Imports\ApotekImport;
use App\Imports\MasterObatPrbImport;
use App\Imports\MasterObatUmumImport;
use App\Imports\PoliklinikImport;
use App\Imports\PrbImport;
use App\Imports\RegisterPrbImport;
use App\Models\LogAktivitas;
use App\Models\MasterObat;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class ImportController extends Controller
{
    public function showPoliklinik()
    {
        return view('admin.import.poliklinik');
    }

    public function importPoliklinik(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'File harus berformat .xlsx atau .xls.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $headings = (new HeadingRowImport)->toArray($request->file('file'));
            $headers = $headings[0][0] ?? [];

            // File Poliklinik harus punya kolom keluhan atau icd_x, dan tidak boleh punya no_nota (ciri khas apotek)
            $hasKeluhan = in_array('keluhan', $headers) || in_array('icd_x', $headers);
            $hasNota = in_array('no_nota', $headers);

            if (! $hasKeluhan || $hasNota) {
                throw ValidationException::withMessages([
                    'file' => 'File ditolak. File yang Anda unggah sepertinya BUKAN file Register Poliklinik.',
                ]);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'file' => 'Gagal membaca format file Excel.',
            ]);
        }

        $import = new PoliklinikImport;
        Excel::import($import, $request->file('file'));

        LogAktivitas::catat('IMPORT_POLI', 'Melakukan import data Poliklinik dari file Excel.');

        return redirect()->route('admin.import.poliklinik')
            ->with('import_summary', $import->summary);
    }

    public function showApotek()
    {
        return view('admin.import.apotek');
    }

    public function importApotek(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'File harus berformat .xlsx atau .xls.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $headings = (new HeadingRowImport)->toArray($request->file('file'));
            $headers = $headings[0][0] ?? [];

            // File Apotek harus punya kolom obat atau no_nota
            $hasObat = in_array('obat', $headers) || in_array('nama_obat', $headers) || in_array('no_nota', $headers);

            if (! $hasObat) {
                throw ValidationException::withMessages([
                    'file' => 'File ditolak. File yang Anda unggah sepertinya BUKAN file Register Apotek.',
                ]);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'file' => 'Gagal membaca format file Excel.',
            ]);
        }

        $import = new ApotekImport;
        Excel::import($import, $request->file('file'));

        LogAktivitas::catat('IMPORT_APOTEK', 'Melakukan import data Apotek dari file Excel.');

        return redirect()->route('admin.import.apotek')
            ->with('import_summary', $import->summary);
    }

    public function showPrb()
    {
        return view('admin.import.prb');
    }

    public function importPrb(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'File harus berformat .xlsx atau .xls.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $headings = (new HeadingRowImport)->toArray($request->file('file'));
            $headers = $headings[0][0] ?? [];

            // Jika file poli simpus (memiliki keluhan / icd_x), gunakan PrbImport
            $isPoli = in_array('keluhan', $headers) || in_array('icd_x', $headers);
            $import = $isPoli ? new PrbImport : new RegisterPrbImport($request->file('file')->getRealPath());

            Excel::import($import, $request->file('file'));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'file' => 'Gagal memproses file Excel: '.$e->getMessage(),
            ]);
        }

        LogAktivitas::catat('IMPORT_PRB', 'Melakukan import data PRB dari file Excel.');

        return redirect()->route('admin.import.prb')
            ->with('import_summary', $import->summary);
    }

    public function showObatPrb()
    {
        $obats = MasterObat::prb()->orderBy('nama_obat')->get();

        return view('admin.import.obat_prb', compact('obats'));
    }

    public function importObatPrb(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'File harus berformat .xlsx atau .xls.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $headings = (new HeadingRowImport)->toArray($request->file('file'));
            $headers = $headings[0][0] ?? [];

            // Memastikan ini format file obat PRB
            $hasNamaBarang = in_array('nama_barang', $headers) || in_array('nama_obat', $headers) || in_array('nama', $headers);

            if (! $hasNamaBarang) {
                throw ValidationException::withMessages([
                    'file' => 'File ditolak. File yang Anda unggah sepertinya BUKAN file Master Obat PRB (kolom "Nama Barang" tidak ditemukan).',
                ]);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'file' => 'Gagal membaca format file Excel.',
            ]);
        }

        $import = new MasterObatPrbImport;
        Excel::import($import, $request->file('file'));

        LogAktivitas::catat('IMPORT_OBAT_PRB', 'Melakukan import master Obat PRB dari file Excel.');

        return redirect()->route('admin.import.obat-prb')
            ->with('import_summary', $import->summary);
    }

    public function showObatUmum()
    {
        $obats = MasterObat::nonPrb()->orderBy('nama_obat')->get();

        return view('admin.import.obat_umum', compact('obats'));
    }

    public function downloadTemplateObatUmum()
    {
        return TemplateObatUmumExport::download();
    }

    public function importObatUmum(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'File harus berformat .xlsx atau .xls.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $headings = (new HeadingRowImport)->toArray($request->file('file'));
            $headers = $headings[0][0] ?? [];

            // Memastikan ini format file obat umum SIMBOK
            $hasNamaKomponen = in_array('nama_komponen', $headers) || in_array('nama_obat', $headers) || in_array('nama_barang', $headers) || in_array('nama', $headers);

            if (! $hasNamaKomponen) {
                throw ValidationException::withMessages([
                    'file' => 'File ditolak. File yang Anda unggah sepertinya BUKAN file Master Obat Umum SIMBOK (kolom "Nama Komponen" tidak ditemukan).',
                ]);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'file' => 'Gagal membaca format file Excel.',
            ]);
        }

        $import = new MasterObatUmumImport;
        Excel::import($import, $request->file('file'));

        LogAktivitas::catat('IMPORT_OBAT_UMUM', 'Melakukan import master Obat Umum SIMBOK dari file Excel.');

        return redirect()->route('admin.import.obat-umum')
            ->with('import_summary', $import->summary);
    }
}
