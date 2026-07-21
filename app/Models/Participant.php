<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = [
        'training_id', 
        'nip_nik', 
        'name', 
        'jabatan', 
        'instansi'
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
        // Hubungkan ke tabel evaluation_results_l2
        return $this->hasOne(EvaluationResultL2::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    
}
