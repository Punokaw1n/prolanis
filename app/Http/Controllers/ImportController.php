<?php

namespace App\Http\Controllers;

use App\Imports\PoliklinikImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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

        $import = new PoliklinikImport;
        Excel::import($import, $request->file('file'));

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

        $import = new \App\Imports\ApotekImport();
        Excel::import($import, $request->file('file'));

        return redirect()->route('admin.import.apotek')
            ->with('import_summary', $import->summary);
    }
}
