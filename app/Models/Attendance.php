<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'schedule_id', 
        'participant_id', 
        'status', 
        'check_in_at', 
        'timezone_label',
        'keterangan'
    ];

    // INI YANG KURANG: Relasi ke Schedule
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
