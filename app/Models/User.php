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
        'name', 'username', 'google_id', 'avatar', 'whatsapp', 'role', 'user_type', 'user_type_status', 'bidang',
        'nip_nik', 'gender', 'birth_place', 'birth_date', 'jabatan', 'golongan', 'instansi', 
        'provinsi', 'kota', 'kecamatan', 'kelurahan', 'address', 'latitude', 'longitude',
        'status_kepegawaian', 'password', 'profile_photo', 'must_complete_profile', 'must_change_password'
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
        'birth_date' => 'date',
        'must_complete_profile' => 'boolean',
        'must_change_password' => 'boolean',
    ];

    /**
     * Relasi untuk melihat riwayat pelatihan (berdasarkan NIP/Username)
     * Kita asumsikan username menyimpan NIP jika user adalah participant
     */
    public function trainingHistories()
    {
        return $this->hasMany(Participant::class, 'nip_nik', 'username');
    }
    public function managedInternshipPrograms()
    {
        return $this->hasMany(InternshipProgram::class, 'manager_id');
    }

    public function internshipParticipant()
    {
        return $this->hasOne(InternshipParticipant::class);
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

    public function electronicSignatureActors()
    {
        return $this->hasMany(ElectronicSignatureActor::class, 'user_id');
    }

    public function isNarasumber(): bool
    {
        return $this->role === 'pengajar'
            && $this->user_type === 'narasumber'
            && $this->user_type_status === 'approved';
    }

    public function canAccessNarasumberPortal(): bool
    {
        return $this->isNarasumber();
    }
}
