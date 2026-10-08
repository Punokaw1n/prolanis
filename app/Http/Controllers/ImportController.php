<?php

namespace App\Http\Controllers;

use App\Imports\ApotekImport;
use App\Imports\PoliklinikImport;
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

        \App\Models\LogAktivitas::catat('IMPORT_POLI', "Melakukan import data Poliklinik dari file Excel.");

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

        \App\Models\LogAktivitas::catat('IMPORT_APOTEK', "Melakukan import data Apotek dari file Excel.");

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

            // Memastikan ini format Poliklinik (bukan apotek)
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

        $import = new \App\Imports\PrbImport;
        Excel::import($import, $request->file('file'));

        \App\Models\LogAktivitas::catat('IMPORT_PRB', "Melakukan import data PRB dari file Excel.");

        return redirect()->route('admin.import.prb')
            ->with('import_summary', $import->summary);
    }
}
