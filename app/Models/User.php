<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    private ?string $activeRoleContext = null;

    private ?string $activeBidangContext = null;

    public function roleAssignments()
    {
        return $this->hasMany(UserRoleAssignment::class);
    }

    public function primaryRole(): string
    {
        return (string) ($this->attributes['role'] ?? 'participant');
    }

    public function assignedRoles(): array
    {
        return array_values(array_unique(array_merge([$this->primaryRole()], $this->roleAssignments->pluck('role')->all())));
    }

    public function hasAssignedRole(string $role): bool
    {
        return in_array($role, $this->assignedRoles(), true);
    }

    public function scopeWithRoles($query, array $roles)
    {
        return $query->where(fn ($q) => $q->whereIn('users.role', $roles)->orWhereHas('roleAssignments', fn ($assignments) => $assignments->whereIn('role', $roles)));
    }

    public function scopeWithRoleInBidang($query, string $role, string $bidang)
    {
        return $query->where(fn ($q) => $q->where(fn ($primary) => $primary->where('users.role', $role)->where('users.bidang', $bidang))
            ->orWhereHas('roleAssignments', fn ($assignments) => $assignments->where('role', $role)->where('bidang', $bidang)));
    }

    public function scopeEligibleTeachers($query)
    {
        return $query->where(fn ($q) => $q->where(fn ($primary) => $primary->where('users.role', 'pengajar')->where('user_type', 'narasumber')->where('user_type_status', 'approved'))
            ->orWhereHas('roleAssignments', fn ($assignments) => $assignments->where('role', 'pengajar')));
    }

    public function useRoleContext(?string $role): void
    {
        $this->activeRoleContext = null;
        $this->activeBidangContext = null;
        if ($role === null || $role === $this->primaryRole() || ! $this->hasAssignedRole($role)) {
            return;
        }
        $this->activeRoleContext = $role;
        $this->activeBidangContext = $role === 'admin_aset' ? 'Pengelola Aset' : $this->roleAssignments->firstWhere('role', $role)?->bidang;
    }

    public function getRoleAttribute($value): string
    {
        return $this->activeRoleContext ?? (string) $value;
    }

    public function getBidangAttribute($value): ?string
    {
        return $this->activeRoleContext !== null ? $this->activeBidangContext : $value;
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name', 'username', 'google_id', 'avatar', 'whatsapp', 'role', 'user_type', 'user_type_status', 'bidang',
        'nip_nik', 'gender', 'birth_place', 'birth_date', 'jabatan', 'golongan', 'instansi', 'institution_id',
        'provinsi', 'kota', 'kecamatan', 'kelurahan', 'address', 'latitude', 'longitude',
        'status_kepegawaian', 'employment_status_id', 'password', 'profile_photo', 'must_complete_profile', 'must_change_password',
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

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function employmentStatus()
    {
        return $this->belongsTo(EmploymentStatus::class);
    }

    public function dailyReportAssignment()
    {
        return $this->hasOne(DailyReportAssignment::class);
    }

    public function activeDailyReportAssignment()
    {
        return $this->hasOne(DailyReportAssignment::class)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', today())
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()));
    }

    public function isNarasumber(): bool
    {
        return $this->role === 'pengajar'
            && (($this->user_type === 'narasumber' && $this->user_type_status === 'approved')
                || ($this->primaryRole() !== 'pengajar' && $this->hasAssignedRole('pengajar')));
    }

    public function canAccessNarasumberPortal(): bool
    {
        return $this->isNarasumber();
    }
}
