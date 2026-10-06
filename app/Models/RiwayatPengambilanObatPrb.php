<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPengambilanObatPrb extends Model
{
    protected $guarded = ['id'];
    
    // cast dates
    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_diambil' => 'date',
        'terakhir_diingatkan' => 'datetime',
    ];

    public function getStatusAttribute()
    {
        $jatuhTempo = $this->tanggal_jatuh_tempo;
        $diambil = $this->tanggal_diambil;

        if ($diambil) {
            if ($diambil->lte($jatuhTempo)) {
                return ['label' => 'Sudah Ambil (Tepat Waktu)', 'color' => 'bg-green-100 text-green-800'];
            } else {
                $selisih = abs($diambil->diffInDays($jatuhTempo));
                return ['label' => "Sudah Ambil (Terlambat {$selisih} Hari)", 'color' => 'bg-amber-100 text-amber-800'];
            }
        } else {
            if ($jatuhTempo && now()->startOfDay()->gt($jatuhTempo)) {
                return ['label' => 'Belum Ambil (Terlambat)', 'color' => 'bg-red-100 text-red-800'];
            } else {
                return ['label' => 'Belum Jatuh Tempo', 'color' => 'bg-gray-100 text-gray-800'];
            }
        }
    }

    public function getSelisihHariAttribute()
    {
        $jatuhTempo = $this->tanggal_jatuh_tempo;
        $diambil = $this->tanggal_diambil;

        if ($diambil && $jatuhTempo) {
            return $diambil->diffInDays($jatuhTempo, false) * -1; // positive means late
        } elseif (!$diambil && $jatuhTempo) {
            return now()->startOfDay()->diffInDays($jatuhTempo, false) * -1;
        }

        return 0;
    }

    public function pasienPrb()
    {
        return $this->belongsTo(PasienPrb::class, 'pasien_prb_id');
    }

    public function obatPrb()
    {
        return $this->hasMany(ObatPrb::class, 'riwayat_pengambilan_obat_prb_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(Pengguna::class, 'dicatat_oleh');
    }
}
