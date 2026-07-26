<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Training extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'nama_pelatihan',
        'bidang',
        'model',
        'metode', // <--- Pastikan ini ada
        'lokasi',
        'angkatan',
        'jumlah_peserta',
        'jp',
        'tgl_mulai',
        'tgl_selesai',
    ];

    public function getSisaHariAttribute()
    {
        $today = Carbon::now()->startOfDay();
        $end = Carbon::parse($this->tgl_selesai)->startOfDay();
        
        return (int) $today->diffInDays($end, false);
    }

    /**
     * Relasi ke Tabel Schedule (Satu Pelatihan memiliki Banyak Jadwal)
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
    public function stages() {
        return $this->hasMany(TrainingStage::class);
    }

    /**
     * Relasi ke Tabel Participant (Satu Pelatihan memiliki Banyak Peserta)
     */
    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function monitoringResults()
    {
        return $this->hasMany(MonitoringResult::class);
    }

    public function summaries() { 
        return $this->hasMany(MonitoringSummary::class); 
    }

    public function getCurrentActivityAttribute()
    {
        // Pastikan timezone sudah Asia/Jakarta di config/app.php atau .env
        $now = \Carbon\Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();

        return $this->schedules()
            ->where('date', $today)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->first(); // Mengambil sesi pertama yang cocok
    }
}