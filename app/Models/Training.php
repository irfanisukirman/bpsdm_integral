<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Training extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'nama_pelatihan',
        'invitation_code',
        'link_lms',
        'bidang',
        'program_evaluasi',
        'model',
        'metode',
        'lokasi',
        'angkatan',
        'jumlah_peserta',
        'jp',
        'tgl_mulai',
        'tgl_selesai',
        'created_by',
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

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function messages() { return $this->hasMany(TrainingMessage::class); }

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

    public function getTglSebarL34Attribute()
    {
        $selesai = Carbon::parse($this->tgl_selesai);

        // Jika Bidang Manajerial -> 1 Tahun
        if ($this->bidang == 'Bidang Pengembangan Kompetensi Manajerial') {
            return $selesai->addYear();
        }

        // Selain itu (Teknis/Sertifikasi) -> 4 Bulan
        return $selesai->addMonths(4);
    }

    /**
     * Menghitung Sisa Hari Menuju Sebar Kuisioner
     */
    public function getSisaHariSebarAttribute()
    {
        $today = \Carbon\Carbon::now('Asia/Jakarta')->startOfDay();
        $target = $this->tgl_sebar_l34->startOfDay();

        // Menghasilkan angka positif jika masa depan, negatif jika sudah lewat
        return (int) $today->diffInDays($target, false);
    }
    
    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            $model->invitation_code = strtoupper(\Illuminate\Support\Str::random(6));
        });
    }

    public function folder() {
        return $this->hasOne(Folder::class, 'training_id');
    }

}
