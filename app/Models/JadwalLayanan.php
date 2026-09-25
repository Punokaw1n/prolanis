<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalLayanan extends Model
{
    protected $table = 'jadwal_layanan';

    protected $guarded = ['id'];

    public function pemeriksaans(): HasMany
    {
        return $this->hasMany(Pemeriksaan::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'dibuat_oleh');
    }

    public function pasien(): BelongsToMany
    {
        return $this->belongsToMany(Pasien::class, 'jadwal_layanan_pasien', 'jadwal_layanan_id', 'pasien_id')->withTimestamps();
    }
}
