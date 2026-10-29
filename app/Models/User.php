<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'nik', 'nip', 'tanggal_lahir', 'alamat', 'no_hp', 'jabatan', 'peran', 'nama', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'pengguna';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke data profil pegawai (tabel pegawai)
     */
    public function pegawai(): HasOne
    {
        return $this->hasOne(Pegawai::class, 'nik', 'nik');
    }

    /**
     * Dapatkan label jabatan atau peran untuk tampilan profil dan sidebar
     */
    public function getRoleOrJabatanAttribute(): string
    {
        $jabatan = trim((string) ($this->jabatan ?: $this->pegawai?->jabatan));

        if ($jabatan !== '') {
            return $jabatan;
        }

        return ! empty($this->peran) ? ucfirst($this->peran) : 'Petugas';
    }

    /**
     * Alias untuk role_or_jabatan
     */
    public function getDisplayRoleAttribute(): string
    {
        return $this->getRoleOrJabatanAttribute();
    }

    /**
     * Cek apakah user memiliki peran admin
     */
    public function isAdmin(): bool
    {
        return $this->peran === 'admin';
    }

    /**
     * Cek apakah user memiliki profesi/jabatan/peran dokter
     */
    public function isDokter(): bool
    {
        $role = strtolower((string) $this->peran);
        $jabatan = strtolower((string) ($this->jabatan ?: $this->pegawai?->jabatan));

        return str_contains($role, 'dokter') || str_contains($jabatan, 'dokter');
    }

    /**
     * Cek apakah user memiliki profesi/jabatan/peran farmasi (apoteker / asisten apoteker)
     */
    public function isFarmasi(): bool
    {
        $role = strtolower((string) $this->peran);
        $jabatan = strtolower((string) ($this->jabatan ?: $this->pegawai?->jabatan));

        return str_contains($role, 'farmasi') || str_contains($role, 'apotek')
            || str_contains($jabatan, 'farmasi') || str_contains($jabatan, 'apotek');
    }

    /**
     * Cek apakah user berhak mengakses menu dan modul PRB
     * (Admin, Dokter, dan Farmasi)
     */
    public function canAccessPrb(): bool
    {
        return $this->isAdmin() || $this->isDokter() || $this->isFarmasi();
    }

    /**
     * Cek apakah user berhak mengakses menu dan modul Farmasi PRB
     * (Khusus Farmasi dan Admin)
     */
    public function canAccessFarmasi(): bool
    {
        return $this->isAdmin() || $this->isFarmasi();
    }

    /**
     * Cek apakah user berhak mengakses modul Pengingat WA PRB
     * (Admin dan Dokter, tidak untuk Farmasi/Apoteker/Asisten Apoteker)
     */
    public function canAccessPengingat(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isFarmasi()) {
            return false;
        }

        return $this->isDokter();
    }

    /**
     * Cek apakah user berhak mencatat/menambah riwayat pengambilan obat PRB
     * (Khusus Dokter dan Admin, bukan Farmasi atau staf umum)
     */
    public function canCatatPengambilan(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isFarmasi()) {
            return false;
        }

        return $this->isDokter();
    }

    /**
     * Cek apakah user berhak mengedit atau menghapus riwayat pengambilan obat tertentu
     * - Hak dasar ada di role dokter dan admin
     * - Hilang / hidden ketika status obat di menu farmasi = sudah (sudah dilayani farmasi)
     */
    public function canEditHapusRiwayat(RiwayatPengambilanObatPrb $riwayat): bool
    {
        if ($riwayat->isFarmasiSudah()) {
            return false;
        }

        return $this->canCatatPengambilan();
    }
}
