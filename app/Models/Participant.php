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
        'sertifikat_file_id',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
        if (!in_array($role, ['mandiri', 'atasan', 'rekan'], true)) {
            return false;
        }

        if ($this->relationLoaded('evaluationResultsL34')) {
            return $this->evaluationResultsL34->contains(fn ($result) =>
                $result->evaluator_role === $role &&
                (int) $result->training_id === (int) $this->training_id
            );
        }

        return $this->evaluationResultsL34()
            ->where('training_id', $this->training_id)
            ->where('evaluator_role', $role)
            ->exists();
    }

    public function hasFilledL34Mandiri()
    {
        return $this->hasFilledL34('mandiri');
    }

    public function getAvgL4Attribute()
    {
        return $this->l4_summary['average'];
    }

    public function getL4SummaryAttribute(): array
    {
        $results = $this->relationLoaded('evaluationResultsL34')
            ? $this->evaluationResultsL34
            : $this->evaluationResultsL34()->with('question')
                ->where('training_id', $this->training_id)
                ->get();

        $labelScores = [
            'sangat kurang' => 20,
            'kurang' => 40,
            'cukup' => 60,
            'baik' => 80,
            'sangat baik' => 100,
        ];

        $values = $results
            ->filter(fn ($result) =>
                (int) $result->training_id === (int) $this->training_id &&
                $result->question?->sub_category === 'Dampak Pelatihan'
            )
            ->map(function ($result) use ($labelScores) {
                if (is_numeric($result->score)) {
                    return (float) $result->score;
                }

                return $labelScores[strtolower(trim((string) $result->note))] ?? null;
            })
            ->filter(fn ($value) => $value !== null)
            ->values();

        return [
            'average' => $values->isNotEmpty() ? round($values->avg(), 1) : 0,
            'count' => $values->count(),
        ];
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

    public function hasCompletedAllL1(): bool
    {
        $forms = \App\Models\EvaluationFormL1::where('training_id', $this->training_id)->get();
        if ($forms->isEmpty()) {
            return true;
        }

        return $forms->every(function ($form) {
            return \App\Models\EvaluationResultL1::where('participant_id', $this->id)
                ->where('training_id', $this->training_id)
                ->where(function ($query) use ($form) {
                    if ($form->schedule_id) {
                        $query->where('schedule_id', $form->schedule_id);
                    } else {
                        $query->whereNull('schedule_id');
                    }
                })
                ->exists();
        });
    }

    public function getHasCompletedDocumentsAttribute(): bool
    {
        return (bool) ($this->biodata_file_id && $this->surat_tugas_file_id && $this->pas_foto_file_id);
    }

    public function getIsCoreTrainingCompleteAttribute(): bool
    {
        $training = $this->relationLoaded('training') ? $this->training : $this->training()->first();

        return $this->registration_status === 'approved'
            && $training
            && \Carbon\Carbon::parse($training->tgl_selesai)->endOfDay()->isPast()
            && $this->has_completed_documents
            && $this->hasCompletedAllL1();
    }

    public function getIsPostEvaluationDueAttribute(): bool
    {
        $training = $this->relationLoaded('training') ? $this->training : $this->training()->first();

        return $training
            && now()->startOfDay()->greaterThanOrEqualTo($training->tgl_sebar_l34->copy()->startOfDay());
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
