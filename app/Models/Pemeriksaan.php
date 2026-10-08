<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pemeriksaan extends Model
{
    protected $table = 'pemeriksaan';

    protected $guarded = ['id'];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }

    public function jadwalLayanan(): BelongsTo
    {
        return $this->belongsTo(JadwalLayanan::class);
    }

    public function nakes(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nakes_id');
    }

    public function pemberianObats(): HasMany
    {
        return $this->hasMany(PemberianObat::class);
    }

    /**
     * Hitung nilai IMT berdasarkan BB (kg) dan TB (cm)
     */
    public function getNilaiImtAttribute()
    {
        if (empty($this->berat_badan) || empty($this->tinggi_badan)) {
            return null;
        }

        $tinggiMeter = $this->tinggi_badan / 100;
        $imt = $this->berat_badan / ($tinggiMeter * $tinggiMeter);
        
        return round($imt, 1);
    }

    /**
     * Dapatkan Status IMT beserta warna badge-nya berdasarkan standar Asia-Pasifik
     */
    public function getStatusImtAttribute()
    {
        $imt = $this->nilai_imt;

        if (!$imt) {
            return ['label' => 'Belum Diukur', 'color' => 'bg-gray-100 text-gray-800'];
        }

        if ($imt < 18.5) {
            return ['label' => 'Kurus', 'color' => 'bg-blue-100 text-blue-800'];
        } elseif ($imt >= 18.5 && $imt <= 22.9) {
            return ['label' => 'Normal', 'color' => 'bg-green-100 text-green-800'];
        } elseif ($imt >= 23.0 && $imt <= 24.9) {
            return ['label' => 'Overweight', 'color' => 'bg-amber-100 text-amber-800'];
        } elseif ($imt >= 25.0 && $imt <= 29.9) {
            return ['label' => 'Obesitas Tingkat I', 'color' => 'bg-orange-100 text-orange-800'];
        } else {
            return ['label' => 'Obesitas Tingkat II', 'color' => 'bg-red-100 text-red-800'];
        }
    }
}
