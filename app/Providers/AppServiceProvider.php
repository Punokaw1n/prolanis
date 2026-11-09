<?php

namespace App\Providers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');
        Paginator::defaultView('vendor.pagination.tailwind');

        View::composer('*', function ($view) {
            static $logoSurabayaBase64 = null;
            if ($logoSurabayaBase64 === null) {
                $path = public_path('surabaya-city.png');
                $logoSurabayaBase64 = file_exists($path)
                    ? 'data:image/png;base64,'.base64_encode(file_get_contents($path))
                    : asset('surabaya-city.png');
            }
            $view->with('logoSurabayaBase64', $logoSurabayaBase64);
        });

        View::composer('admin.prb.*', function ($view) {
            static $daftarDokterPemeriksa = null;
            if ($daftarDokterPemeriksa === null) {
                $daftarDokterPemeriksa = User::query()
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->where('peran', 'like', '%dokter%')
                            ->orWhere('jabatan', 'like', '%dokter%')
                            ->orWhere('nama', 'like', '%dr.%')
                            ->orWhere('nama', 'like', '%drg.%');
                    })
                    ->orderBy('nama')
                    ->get()
                    ->map(function ($d) {
                        return [
                            'id' => $d->id,
                            'nama' => $d->nama_gelar,
                            'nip' => $d->nip,
                            'sip' => $d->no_sip ?: ($d->nip ? 'SIP. '.$d->nip : 'SIP. -'),
                        ];
                    })
                    ->values()
                    ->toArray();
            }
            $view->with('daftarDokterPemeriksa', $daftarDokterPemeriksa);
        });
    }
}
