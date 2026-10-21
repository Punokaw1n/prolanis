<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 25);
        $logs = LogAktivitas::with('user')->latest()->paginate($perPage > 0 ? $perPage : 25)->withQueryString();

        return view('admin.log.index', compact('logs'));
    }
}
