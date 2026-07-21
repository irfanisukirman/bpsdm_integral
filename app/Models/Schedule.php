<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $table = 'schedules';
    protected $guarded = [];

    protected $fillable = [
        'training_id',
        'date',
        'start_time',
        'end_time',
        'activity',
        'pic', // Ini digunakan untuk menyimpan nama Penanggung Jawab / Pengajar
    ];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    // Relasi untuk menghitung jumlah yang hadir di halaman kehadiran nanti
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}