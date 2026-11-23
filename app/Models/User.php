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

#[Fillable(['name', 'email', 'password', 'nik', 'nip', 'no_sip', 'tanggal_lahir', 'alamat', 'no_hp', 'jabatan', 'peran', 'nama', 'is_active'])]
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
     * Format nama dengan gelar dokter yang rapi tanpa duplikasi (misal: dr. Nama Dokter)
     */
    public function getNamaGelarAttribute(): string
    {
        $nama = trim((string) $this->nama);
        if ($nama === '' || in_array(strtolower($nama), ['admin', 'admin test', 'petugas'])) {
            return $this->isDokter() ? 'Dokter Pemeriksa' : ($this->nama ?: 'Petugas');
        }

        if (! $this->isDokter()) {
            return $nama;
        }

        $nama = preg_replace('/^(dr\.?\s*)+/i', 'dr. ', $nama);
        $nama = preg_replace('/^(drg\.?\s*)+/i', 'drg. ', $nama);
        if (! preg_match('/^(dr\.|drg\.|dokter)/i', $nama)) {
            $nama = 'dr. '.$nama;
        }

        return $nama;
    }

    /**
     * Format nomor SIP dokter untuk keperluan e-resep dan dokumen medis
     */
    public function getSipFormattedAttribute(): string
    {
        if (! empty($this->no_sip)) {
            return str_starts_with(strtoupper($this->no_sip), 'SIP') ? $this->no_sip : 'SIP. '.$this->no_sip;
        }

        if (! empty($this->nip)) {
            return 'SIP. '.$this->nip;
        }

        return 'SIP. -';
    }

    /**
     * Alias atribut sip
     */
    public function getSipAttribute(): string
    {
        return $this->getSipFormattedAttribute();
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
        $nama = strtolower((string) $this->nama);

        return str_contains($role, 'dokter')
            || str_contains($jabatan, 'dokter')
            || str_contains($jabatan, 'kepala puskesmas')
            || str_starts_with($nama, 'dr.')
            || str_starts_with($nama, 'dr ')
            || str_starts_with($nama, 'drg.')
            || str_starts_with($nama, 'drg ')
            || str_contains($nama, 'dr.')
            || str_contains($nama, 'dokter');
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
     * Cek apakah user memiliki profesi/jabatan/peran perawat
     */
    public function isPerawat(): bool
    {
        $role = strtolower((string) $this->peran);
        $jabatan = strtolower((string) ($this->jabatan ?: $this->pegawai?->jabatan));

        return str_contains($role, 'perawat') || str_contains($jabatan, 'perawat');
    }

    /**
     * Cek apakah user berhak mengakses menu dan modul PRB
     * (Admin, Dokter, Farmasi, dan Perawat)
     */
    public function canAccessPrb(): bool
    {
        return $this->isAdmin() || $this->isDokter() || $this->isFarmasi() || $this->isPerawat();
    }

    /**
     * Cek apakah user berhak mengakses menu dan modul Farmasi PRB (menu apoteker)
     * (Khusus Farmasi dan Admin non-dokter, non-perawat)
     */
    public function canAccessFarmasi(): bool
    {
        if ($this->isDokter() || $this->isPerawat()) {
            return false;
        }

        return $this->isAdmin() || $this->isFarmasi();
    }

    /**
     * Cek apakah user berhak menambah pasien baru PRB
     * (Admin, Dokter, dan Perawat. Bukan Apoteker/Farmasi)
     */
    public function canTambahPasienPrb(): bool
    {
        if ($this->isFarmasi()) {
            return false;
        }

        return $this->isAdmin() || $this->isDokter() || $this->isPerawat();
    }

    /**
     * Cek apakah user berhak mengakses modul Pengingat WA PRB
     * (Admin dan Dokter, tidak untuk Farmasi/Apoteker maupun Perawat)
     */
    public function canAccessPengingat(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isFarmasi() || $this->isPerawat()) {
            return false;
        }

        return $this->isDokter();
    }

    /**
     * Cek apakah user berhak mengelola data profil pasien dan status keaktifan pasien
     * (Dokter, Perawat, dan Admin, bukan Farmasi/Apoteker)
     */
    public function canKelolaPasien(): bool
    {
        if ($this->isFarmasi()) {
            return false;
        }

        return $this->isAdmin() || $this->isDokter() || $this->isPerawat();
    }

    /**
     * Cek apakah user berhak mencatat/menambah riwayat pengambilan obat / pemeriksaan PRB
     * (Khusus Dokter dan Admin, bukan Perawat atau Farmasi/Apoteker)
     * Untuk role dokter: jika status pasien tidak aktif, akses mencatat pengambilan disembunyikan/dibatasi.
     */
    public function canCatatPengambilan(?PasienPrb $prb = null): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isFarmasi() || $this->isPerawat()) {
            return false;
        }

        if (! $this->isDokter()) {
            return false;
        }

        if ($prb && $prb->status_keaktifan !== 'aktif') {
            return false;
        }

        return true;
    }

    /**
     * Cek apakah user berhak mengedit atau menghapus riwayat pengambilan obat tertentu
     * - Khusus role admin: selalu diizinkan meskipun obat sudah diserahkan di farmasi
     * - Role selain admin (dokter): dibatasi jika obat sudah diserahkan di farmasi
     */
    public function canEditHapusRiwayat(RiwayatPengambilanObatPrb $riwayat): bool
    {
        // Khusus admin selalu memiliki hak akses penuh untuk edit dan hapus
        if ($this->isAdmin()) {
            return true;
        }

        if ($riwayat->isFarmasiSudah()) {
            return false;
        }

        return $this->canCatatPengambilan();
    }
}
