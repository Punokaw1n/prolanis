<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoinTemplate extends Model
{
    protected $table = 'poin_template';

    protected $fillable = [
        'template_persetujuan_id',
        'urutan',
        'teks_poin',
    ];

    public function templatePersetujuan()
    {
        return $this->belongsTo(TemplatePersetujuan::class, 'template_persetujuan_id');
    }
}
