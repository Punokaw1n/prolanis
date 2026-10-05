<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObatPrb extends Model
{
    protected $guarded = ['id'];

    public function riwayatPengambilan()
    {
        return $this->belongsTo(RiwayatPengambilanObatPrb::class, 'riwayat_pengambilan_obat_prb_id');
    }
}
