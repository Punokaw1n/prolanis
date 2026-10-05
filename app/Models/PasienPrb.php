<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasienPrb extends Model
{
    protected $guarded = ['id'];

    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }

    public function riwayatPengambilan()
    {
        return $this->hasMany(RiwayatPengambilanObatPrb::class, 'pasien_prb_id');
    }
}
