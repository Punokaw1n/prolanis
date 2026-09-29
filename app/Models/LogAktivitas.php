<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper global untuk mencatat log dengan mudah.
     */
    public static function catat($aksi, $deskripsi)
    {
        self::create([
            'user_id' => auth()->check() ? auth()->id() : null,
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
            'ip_address' => request()->ip()
        ]);
    }
}
