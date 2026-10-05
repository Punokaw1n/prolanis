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
