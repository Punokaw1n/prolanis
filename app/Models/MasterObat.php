<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterObat extends Model
{
    protected $guarded = ['id'];

    public function scopePrb($query)
    {
        return $query->where('kategori', 'prb');
    }

    public function scopeNonPrb($query)
    {
        return $query->where('kategori', 'non_prb');
    }
}
