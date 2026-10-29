<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPengambilanObatPrb extends Model
{
    protected $guarded = ['id'];

    // cast dates
    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_diambil' => 'date',
        'terakhir_diingatkan' => 'datetime',
    ];

    public function getStatusAttribute()
    {
        $jatuhTempo = $this->tanggal_jatuh_tempo;
        $diambil = $this->tanggal_diambil;

        if ($diambil) {
            if (! $jatuhTempo) {
                return ['label' => 'Sudah Ambil (Import/Tanpa Jadwal)', 'color' => 'bg-blue-100 text-blue-800'];
            }
            if ($diambil->lte($jatuhTempo)) {
                return ['label' => 'Sudah Ambil (Tepat Waktu)', 'color' => 'bg-green-100 text-green-800'];
            } else {
                $selisih = abs($diambil->diffInDays($jatuhTempo));

                return ['label' => "Sudah Ambil (Terlambat {$selisih} Hari)", 'color' => 'bg-amber-100 text-amber-800'];
            }
        } else {
            if ($jatuhTempo && now()->startOfDay()->gt($jatuhTempo)) {
                return ['label' => 'Belum Ambil (Terlambat)', 'color' => 'bg-red-100 text-red-800'];
            } else {
                return ['label' => 'Belum Jatuh Tempo', 'color' => 'bg-gray-100 text-gray-800'];
            }
        }
    }

    public function getSelisihHariAttribute()
    {
        $jatuhTempo = $this->tanggal_jatuh_tempo;
        $diambil = $this->tanggal_diambil;

        if ($diambil && $jatuhTempo) {
            return $diambil->diffInDays($jatuhTempo, false) * -1; // positive means late
        } elseif (! $diambil && $jatuhTempo) {
            return now()->startOfDay()->diffInDays($jatuhTempo, false) * -1;
        }

        return 0;
    }

    public function pasienPrb()
    {
        return $this->belongsTo(PasienPrb::class, 'pasien_prb_id');
    }

    public function obatPrb()
    {
        return $this->hasMany(ObatPrb::class, 'riwayat_pengambilan_obat_prb_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(Pengguna::class, 'dicatat_oleh');
    }

    public function petugasFarmasi()
    {
        return $this->belongsTo(Pengguna::class, 'petugas_farmasi_id');
    }

    public function getStatusFarmasiBadgeAttribute(): array
    {
        return match ($this->status_farmasi) {
            'sudah' => [
                'label' => 'Sudah',
                'class' => 'bg-teal-500 text-white shadow-2xs',
                'style' => 'background-color: #14b8a6; color: #ffffff;',
            ],
            'tidak_lengkap' => [
                'label' => 'Sudah Diberikan Tidak Lengkap',
                'class' => 'bg-amber-400 text-gray-900 font-bold border border-amber-500 shadow-2xs',
                'style' => 'background-color: #fbbf24; color: #111827; border: 1px solid #f59e0b;',
            ],
            default => [
                'label' => 'Belum Dipersiapkan',
                'class' => 'bg-gray-100 text-gray-700 border border-gray-300 shadow-2xs',
                'style' => 'background-color: #f3f4f6; color: #374151; border: 1px solid #d1d5db;',
            ],
        };
    }

    public function getDiagnosaLengkapAttribute(): string
    {
        $diag = $this->diagnosa ?: ($this->pasienPrb?->diagnosa ?: 'PRB');
        $kode = $this->kode_diagnosa;

        if ($kode && $diag && ! str_starts_with($diag, $kode)) {
            return "{$kode} - {$diag}";
        }

        return $diag ?: ($kode ?: 'PRB');
    }

    public function getDiagnosaGroupKeyAttribute(): string
    {
        if ($this->kode_diagnosa) {
            return strtoupper(trim($this->kode_diagnosa));
        }
        if ($this->diagnosa) {
            return strtoupper(trim($this->diagnosa));
        }
        if ($this->pasienPrb?->diagnosa) {
            return strtoupper(trim($this->pasienPrb->diagnosa));
        }

        return 'LAINNYA';
    }

    /**
     * Cek apakah status obat di menu farmasi sudah selesai (sudah diserahkan ke pasien)
     */
    public function isFarmasiSudah(): bool
    {
        return in_array($this->status_farmasi, ['sudah', 'tidak_lengkap'], true);
    }
}
