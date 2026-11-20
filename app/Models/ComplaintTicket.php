<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ComplaintTicket extends Model
{
    public const STAFF_ROLES = ['superadmin', 'admin_bidang'];

    public const CATEGORIES = [
        'account' => 'Akun & Login',
        'attendance' => 'Presensi',
        'training' => 'Jadwal & Pelaksanaan Pelatihan',
        'evaluation' => 'Evaluasi & Sertifikat',
        'application' => 'Kendala Aplikasi',
        'other' => 'Lainnya',
    ];

    public const TRAINING_CATEGORIES = ['attendance', 'training', 'evaluation'];

    public const STATUSES = [
        'received' => 'Diterima',
        'in_progress' => 'Diproses',
        'resolved' => 'Selesai',
    ];

    protected $fillable = ['user_id', 'training_id', 'guest_name', 'chat_name', 'chat_institution', 'tracking_hash', 'category', 'status', 'assigned_to'];

    protected $hidden = ['tracking_hash'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(ComplaintMessage::class);
    }

    public function latestStaffMessage()
    {
        return $this->hasOne(ComplaintMessage::class)->ofMany(['id' => 'max'], fn ($query) => $query->where('from_staff', true));
    }

    public function firstCustomerMessage()
    {
        return $this->hasOne(ComplaintMessage::class)->ofMany(['id' => 'min'], fn ($query) => $query->where('from_staff', false));
    }

    public function latestCustomerMessage()
    {
        return $this->hasOne(ComplaintMessage::class)->ofMany(['id' => 'max'], fn ($query) => $query->where('from_staff', false));
    }

    public function scopeStaffInbox(Builder $query, User $user): Builder
    {
        return in_array($user->role, self::STAFF_ROLES, true) ? $query : $query->whereRaw('1 = 0');
    }

    public function reference(): string
    {
        return 'PGD-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }
}
