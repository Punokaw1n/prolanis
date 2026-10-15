<?php

namespace App\Http\Controllers;

use App\Models\TemplatePersetujuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TemplatePersetujuanController extends Controller
{
    public function index()
    {
        $template = TemplatePersetujuan::with(['poinTemplates' => function ($q) {
            $q->orderBy('urutan', 'asc');
        }])->first();

        return view('admin.template_persetujuan.index', compact('template'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'teks_pembuka' => 'required|string',
            'teks_penutup' => 'required|string',
            'poin' => 'nullable|array',
            'poin.*' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            $template = TemplatePersetujuan::first();

            if (! $template) {
                $template = new TemplatePersetujuan;
                $template->is_active = true;
            }

            $template->judul = $request->judul;
            $template->teks_pembuka = $request->teks_pembuka;
            $template->teks_penutup = $request->teks_penutup;
            $template->save();

            $template->poinTemplates()->delete();

            if ($request->has('poin') && is_array($request->poin)) {
                foreach ($request->poin as $index => $teks) {
                    $template->poinTemplates()->create([
                        'urutan' => $index + 1,
                        'teks_poin' => $teks,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Template persetujuan berhasil disimpan.');
    }
}
