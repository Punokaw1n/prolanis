<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasienSrkTemplate extends Model
{
    protected $table = 'pasien_srk_templates';

    protected $guarded = ['id'];

    public static function defaultTemplate(): string
    {
        return "Halo Bapak/Ibu {nama},\n\nKami dari Puskesmas Kalijudan ingin menghubungi Anda terkait layanan kesehatan.\n\nTerima kasih dan salam sehat.";
    }

    public static function currentText(): string
    {
        return static::query()->value('isi_pesan') ?? static::defaultTemplate();
    }
}
