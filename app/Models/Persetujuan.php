<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persetujuan extends Model
{
    protected $table = 'persetujuan';

    protected $fillable = [
        'pasien_id',
        'template_persetujuan_id',
        'petugas_id',
        'salinan_judul',
        'salinan_teks_pembuka',
        'salinan_teks_penutup',
        'salinan_poin',
        'token_qr',
        'status',
        'kedaluwarsa_pada',
        'ditandatangani_pada',
        'tanda_tangan_path',
        'ip_address',
        'user_agent',
        'hash_dokumen',
    ];

    protected $casts = [
        'salinan_poin' => 'array',
        'kedaluwarsa_pada' => 'datetime',
        'ditandatangani_pada' => 'datetime',
    ];

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    public function templatePersetujuan()
    {
        return $this->belongsTo(TemplatePersetujuan::class, 'template_persetujuan_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id'); // User model corresponds to pengguna table
    }
}
