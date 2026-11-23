<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EvaluationFormL1 extends Model
{
    // Tambahkan baris ini agar model membaca tabel 'evaluation_forms'
    protected $table = 'evaluation_forms';

    protected $fillable = [
        'training_id', 
        'type', 
        'name', 
        'schedule_id', 
        'target_name', 
        'materi'
    ];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
    public function opensAt(): ?Carbon
    {
        $openingDate = $this->training?->evaluation_l1_opens_on;
        if ($this->type === 'narasumber') {
            $schedule = $this->schedule;
            if (!$schedule?->pengajar_id) return null;
        }

        return $openingDate ? Carbon::parse($openingDate->toDateString(), 'Asia/Jakarta') : null;
    }

    public function isOpen(): bool
    {
        $opensAt = $this->opensAt();
        return $opensAt && now('Asia/Jakarta')->greaterThanOrEqualTo($opensAt);
    }}
