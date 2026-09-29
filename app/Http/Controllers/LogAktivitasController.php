<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LogAktivitas;

class LogAktivitasController extends Controller
{
    public function index()
    {
        $logs = LogAktivitas::with('user')->latest()->paginate(25);
        return view('admin.log.index', compact('logs'));
    }
}
