<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Kolom yang bisa diisi massal.
     *
     * name       = Nama lengkap peserta
     * nip        = NIP / Nomor Peserta ujian
     * password   = PIN / kata sandi (di-hash)
     * is_guest   = true jika masuk sebagai "Tamu" (progres tidak wajib persist)
     */
    protected $fillable = [
        'name',
        'nip',
        'password',
        'is_guest',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_guest' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    /** Inisial untuk avatar bulat di UI, mis. "Budi Santoso" -> "B" */
    public function initial(): string
    {
        return strtoupper(substr(trim($this->name ?: 'T'), 0, 1));
    }
}
