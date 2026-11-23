<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Training extends Model
{
    public const COOPERATION_OPTIONS = ['APBD Provinsi/Mandiri', 'Kerjasama/Fasilitasi', 'Kerjasama/Pengiriman'];
    public const FUNDING_SOURCE_OPTIONS = ['APBN', 'APBD', 'APBD Kab/Kota'];

    public function getProgramEvaluasiAttribute($value): string
    {
        return \App\Support\EvaluationProgram::normalize($value);
    }

    public function setProgramEvaluasiAttribute($value): void
    {
        $this->attributes['program_evaluasi'] = \App\Support\EvaluationProgram::normalize($value);
    }

    public function evaluationRoles(): array
    {
        return \App\Support\EvaluationProgram::roles($this->program_evaluasi);
    }

    public function evaluationRoleLabels(): array
    {
        return array_intersect_key(['mandiri' => 'Mandiri', 'atasan' => 'Atasan Langsung', 'rekan' => 'Rekan Kerja'], array_flip($this->evaluationRoles()));
    }

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
        'kerjasama',
        'sumber_anggaran',
        'jumlah_peserta',
        'jp',
        'tgl_mulai',
        'tgl_selesai',
        'evaluation_l1_opens_on',
        'evaluation_l34_opens_on',
        'created_by',
    ];

    protected $casts = [
        'l34_report_signatories' => 'array', 'monitoring_report_signatories' => 'array',
        'evaluation_l1_opens_on' => 'date', 'evaluation_l34_opens_on' => 'date',
    ];

    public function monitoringReportSignatories(): array
    {
        return array_replace([
            'head_title' => 'Kepala Bidang Sertifikasi Kompetensi dan Pengelolaan Kelembagaan',
            'head_name' => 'RISGIYANTO S.Si., M.Kom.', 'head_nip' => '197011012007011010',
            'monitor_title' => 'Tim Monitoring', 'monitor_name' => 'Tantan Gunawan, M.I.Kom',
            'monitor_nip' => '197307012014111001',
        ], $this->monitoring_report_signatories ?? []);
    }

    public function l34ReportSignatories(): array
    {
        return array_replace([
            'head_title' => 'Kepala Bidang Sertifikasi Kompetensi dan Pengelolaan Kelembagaan',
            'head_name' => 'Angga Muchlis Al-Rachmat,S.I.P., M.Si.',
            'head_nip' => '197905012009021001',
            'monitor_title' => 'Tim Monitoring',
            'monitor_name' => 'Lucky Rizki Rachmadi, S.Pi.',
            'monitor_nip' => '198904112014031002',
        ], $this->l34_report_signatories ?? []);
    }

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
    public function identityCardSetting()
    {
        return $this->hasOne(TrainingIdentityCardSetting::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function evaluationL1TextSummary()
    {
        return $this->hasOne(EvaluationL1TextSummary::class);
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

    public function getTglSebarL34Attribute(): ?Carbon
    {
        return $this->evaluation_l34_opens_on
            ? Carbon::parse($this->evaluation_l34_opens_on->toDateString(), 'Asia/Jakarta')
            : null;
    }

    public function isL34Open(): bool
    {
        $opensAt = $this->tgl_sebar_l34;

        return $opensAt !== null && now('Asia/Jakarta')->greaterThanOrEqualTo($opensAt);
    }

    /**
     * Menghitung Sisa Hari Menuju Sebar Kuisioner
     */
    public function getSisaHariSebarAttribute(): ?int
    {
        $today = \Carbon\Carbon::now('Asia/Jakarta')->startOfDay();
        $target = $this->tgl_sebar_l34;

        // Menghasilkan angka positif jika masa depan, negatif jika sudah lewat
        return $target ? (int) $today->diffInDays($target, false) : null;
    }
    
    protected static function boot() {
        parent::boot();
        static::creating(function ($model) {
            $model->proper_uid = (string) \Illuminate\Support\Str::uuid();
            $model->invitation_code = strtoupper(\Illuminate\Support\Str::random(6));
        });
    }

    public function folder() {
        return $this->hasOne(Folder::class, 'training_id');
    }

    public function activityReport() { return $this->hasOne(TrainingActivityReport::class); }
    public function activityDocumentations() { return $this->hasMany(TrainingActivityDocumentation::class); }
    public function executionNotes() { return $this->hasMany(TrainingExecutionNote::class); }
    public function participantCertificates() { return $this->hasMany(ParticipantCertificate::class); }
    public function qualityDocuments() { return $this->hasMany(QualityDocumentRecord::class); }

}
