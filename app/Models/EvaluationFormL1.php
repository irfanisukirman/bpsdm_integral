<?php

namespace App\Models;

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
        if ($this->type === 'narasumber') {
            $schedule = $this->schedule;
            if (!$schedule || !$schedule->pengajar_id || !$schedule->date || !$schedule->end_time) return null;
            return Carbon::parse($schedule->date.' '.$schedule->end_time, 'Asia/Jakarta');
        }
        $training = $this->training;
        return $training?->tgl_selesai ? Carbon::parse($training->tgl_selesai, 'Asia/Jakarta')->startOfDay() : null;
    }

    public function isOpen(): bool
    {
        $opensAt = $this->opensAt();
        return $opensAt && now('Asia/Jakarta')->greaterThanOrEqualTo($opensAt);
    }}
