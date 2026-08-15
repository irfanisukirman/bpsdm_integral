<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $table = 'participants';

    protected $fillable = [
        'training_id', 
        'user_id', 
        'nip_nik', 
        'name', 
        'gender', 
        'jabatan', 
        'instansi',
        'provinsi', 
        'kabupaten_kota', 
        'status_kepegawaian',
        'biodata_file_id',    // TAMBAHKAN INI
        'surat_tugas_file_id' // TAMBAHKAN INI
    ];

    /**
     * Cek apakah peserta sudah dinilai oleh role tertentu
     */
    public function hasVoted($role)
    {
        return \App\Models\EvaluationResultL34::where('participant_id', $this->id)
            ->where('evaluator_role', $role)
            ->exists();
    }

    /**
     * Menghitung rata-rata skor Level 4 (Dampak)
     */
    public function hasFilledL1()
    {
        return \App\Models\EvaluationResultL1::where('participant_id', $this->id)->exists();
    }

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function evaluationL2()
    {
        return $this->hasOne(EvaluationResultL2::class, 'participant_id');
    }

    public function evaluationResultsL34()
    {
        return $this->hasMany(EvaluationResultL34::class, 'participant_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function hasFilledL34($role)
    {
        return \App\Models\EvaluationResultL34::where('participant_id', $this->id)
            ->where('evaluator_role', $role)
            ->exists();
    }

    public function getAvgL4Attribute()
    {
        // Menghitung rata-rata skor dari semua penilai (Mandiri, Atasan, Rekan)
        return \App\Models\EvaluationResultL34::where('participant_id', $this->id)
            ->whereNotNull('score')
            ->avg('score') ?? 0;
    }

    public function alumniProfile() {
        return $this->hasOne(AlumniProfile::class);
    }
    
}
