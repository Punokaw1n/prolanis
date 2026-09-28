<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplatePersetujuan extends Model
{
    protected $table = 'template_persetujuan';

    protected $fillable = [
        'judul',
        'teks_pembuka',
        'teks_penutup',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function poinTemplates()
    {
        return $this->hasMany(PoinTemplate::class, 'template_persetujuan_id')->orderBy('urutan', 'asc');
    }
}
