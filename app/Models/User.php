<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name', 'username', 'google_id', 'avatar', 'whatsapp', 'role', 'bidang', 
        'nip_nik', 'gender', 'jabatan', 'instansi', 'provinsi', 
        'kabupaten_kota', 'status_kepegawaian', 'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relasi untuk melihat riwayat pelatihan (berdasarkan NIP/Username)
     * Kita asumsikan username menyimpan NIP jika user adalah participant
     */
    public function trainingHistories()
    {
        return $this->hasMany(Participant::class, 'nip_nik', 'username');
    }
}