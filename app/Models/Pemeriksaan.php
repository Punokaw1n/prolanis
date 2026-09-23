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
}
