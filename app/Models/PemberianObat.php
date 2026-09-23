<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemberianObat extends Model
{
    protected $table = 'pemberian_obat';
    protected $fillable = [
        'pemeriksaan_id',
        'nama_obat',
        'tanggal_diberikan',
        'aturan_pakai',
        'jumlah',
    ];

    public function pemeriksaan(): BelongsTo
    {
        return $this->belongsTo(Pemeriksaan::class);
    }
}
