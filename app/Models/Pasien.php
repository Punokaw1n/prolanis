<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pasien extends Authenticatable
{
    protected $table = 'pasien';

    protected $guarded = ['id'];

    public function pemeriksaans(): HasMany
    {
        return $this->hasMany(Pemeriksaan::class);
    }

    public function jadwalLayanan(): BelongsToMany
    {
        return $this->belongsToMany(JadwalLayanan::class, 'jadwal_layanan_pasien', 'pasien_id', 'jadwal_layanan_id')->withPivot('konfirmasi_kehadiran')->withTimestamps();
    }

    public function pasienPrb()
    {
        return $this->hasOne(PasienPrb::class, 'pasien_id');
    }

    public function getDiagnosaAttribute(): ?string
    {
        return $this->pasienPrb?->diagnosa;
    }

    /**
     * Get the password for the user.
     * We use tanggal_lahir as the password for patient login.
     * It is not hashed in the DB, but Laravel will check it if we use Auth::attempt,
     * wait, if it's not hashed, Auth::attempt will fail because it expects a bcrypt hash.
     * Wait, no, we can override Auth::attempt behavior, or just manually check it and use Auth::login.
     * Actually, if we use Auth::guard('pasien')->attempt(['nik' => $nik, 'password' => $password]),
     * it expects a hashed password.
     * It's better to manually check the plain text password in the controller and use login().
     * So getAuthPassword is not strictly needed if we don't use attempt().
     * Let's still provide it just in case.
     */
    public function getAuthPassword()
    {
        return $this->tanggal_lahir;
    }
}
