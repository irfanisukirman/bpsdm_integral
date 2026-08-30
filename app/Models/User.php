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
        'nip_nik', 'gender', 'jabatan', 'instansi', 
        'provinsi', 'kota', 'kecamatan', 'kelurahan', 'latitude', 'longitude',
        'status_kepegawaian', 'password', 'profile_photo'
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
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Relasi untuk melihat riwayat pelatihan (berdasarkan NIP/Username)
     * Kita asumsikan username menyimpan NIP jika user adalah participant
     */
    public function trainingHistories()
    {
        return $this->hasMany(Participant::class, 'nip_nik', 'username');
    }
    public function pengajar()
    {
        return $this->hasOne(Pengajar::class);
    }

    public function sharedFolders()
    {
        return $this->belongsToMany(Folder::class, 'folder_user_permissions')->withPivot(['permission', 'shared_by'])->withTimestamps();
    }
    public function teachingSchedules()
    {
        return $this->hasMany(Schedule::class, 'pengajar_id');
    }

    public function hasTeachingAssignment(): bool
    {
        return $this->role === 'pengajar' || $this->teachingSchedules()->exists();
    }
}
