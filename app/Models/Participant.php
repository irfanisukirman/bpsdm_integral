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
        'phone',
        'jabatan',
        'instansi',
        'provinsi',
        'kota',
        'kecamatan',
        'kelurahan',
        'status_kepegawaian',
        'biodata_file_id',
        'surat_tugas_file_id',
        'pas_foto_file_id',
        'registration_status'
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
    public function hasFilledL1($formId, $scheduleId = null)
    {
        return \App\Models\EvaluationResultL1::where('participant_id', $this->id)
            ->where('training_id', $this->training_id)
            ->where('schedule_id', $scheduleId)
            ->exists();
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
            ->where('evaluator_role', 'mandiri')
            ->exists();
    }

    public function hasFilledL34Mandiri()
    {
        return \App\Models\EvaluationResultL34::where('participant_id', $this->id)
            ->where('evaluator_role', 'mandiri')
            ->exists();
    }

    public function getAvgL4Attribute()
    {
        // Menghitung rata-rata skor dari semua penilai (Mandiri, Atasan, Rekan)
        return \App\Models\EvaluationResultL34::where('participant_id', $this->id)
            ->whereNotNull('score')
            ->avg('score') ?? 0;
    }

    public function alumniProfile()
    {
        return $this->hasOne(AlumniProfile::class);
    }

    public function hasFilledL1Any()
    {
        // Cek apakah minimal ada 1 baris hasil evaluasi L1 untuk peserta ini di pelatihan ini
        return \App\Models\EvaluationResultL1::where('participant_id', $this->id)
            ->where('training_id', $this->training_id)
            ->exists();
    }

    public function getIsAllFinishedAttribute()
    {
        // 1. Cek Berkas (Biodata, Surat Tugas, Pas Foto)
        $docsComplete = $this->biodata_file_id && $this->surat_tugas_file_id && $this->pas_foto_file_id;

        // 2. Cek Evaluasi Level 1 (Harus mengisi semua form yang disediakan admin)
        $formsCount = \App\Models\EvaluationFormL1::where('training_id', $this->training_id)->count();
        $filledL1Count = \App\Models\EvaluationResultL1::where('participant_id', $this->id)->distinct('schedule_id')->count();
        $l1Complete = ($formsCount > 0) ? ($filledL1Count >= $formsCount) : true;

        // 3. Cek Evaluasi Level 2 (Admin sudah input nilai)
        $l2Complete = \App\Models\EvaluationResultL2::where('participant_id', $this->id)->exists();

        // 4. Cek Evaluasi Level 3 & 4 (Mandiri)
        $l34Complete = \App\Models\EvaluationResultL34::where('participant_id', $this->id)
                        ->where('evaluator_role', 'mandiri')->exists();

        return $docsComplete && $l1Complete && $l2Complete && $l34Complete;
    }
    
}
