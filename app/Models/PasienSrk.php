<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasienSrk extends Model
{
    protected $table = 'pasien_srk';

    protected $guarded = ['id'];

    protected $casts = [
        'terakhir_dihubungi' => 'datetime',
    ];
}
