<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    protected $guarded = ['id'];

    public function pemeriksaans(): HasMany
    {
        return $this->hasMany(Pemeriksaan::class, 'nakes_id');
    }
}
