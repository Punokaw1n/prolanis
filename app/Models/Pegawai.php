<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    protected $guarded = ['id'];

    public function pemeriksaans(): HasMany
    {
        return $this->hasMany(Pemeriksaan::class, 'nakes_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'nik', 'nik');
    }
}
