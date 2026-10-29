<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Pengguna extends User
{
    use HasFactory, Notifiable;

    protected $table = 'pengguna';

    protected $guarded = ['id'];

    protected $attributes = [
        'is_active' => true,
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function jadwalLayanans(): HasMany
    {
        return $this->hasMany(JadwalLayanan::class, 'dibuat_oleh');
    }
}
