<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_id',
        'date',
        'start_time',
        'end_time',
        'activity',
        'schedule_type',
        'jp',
        'duration_unit',
        'link_zoom',
        'pic',
        'pengajar_id',
        'venue_type',
        'external_place'
    ];

    public function getDurationLabelAttribute(): string
    {
        return ($this->jp ?? 0).' '.strtoupper($this->duration_unit ?: 'JP');
    }

    public function getDurationMinutesAttribute(): int
    {
        return (int) ($this->jp ?? 0) * (strtoupper($this->duration_unit ?: 'JP') === 'OJ' ? 60 : 45);
    }

    // Relasi ke User Pengajar
    public function pengajar()
    {
        return $this->belongsTo(User::class, 'pengajar_id');
    }

    // Relasi ke Pelatihan
    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function pengajarDocuments()
    {
        return $this->hasOne(PengajarScheduleDocument::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function bookings()
    {
        return $this->morphMany(AssetBooking::class, 'bookable');
    }

    public function assetLoanRequest()
    {
        return $this->morphOne(AssetLoanRequest::class, 'requestable');
    }
}
