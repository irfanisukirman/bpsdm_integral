<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationResultL34 extends Model
{
    // Pastikan nama tabel sama dengan yang di phpMyAdmin Anda
    protected $table = 'evaluation_results_l34';

    protected $fillable = [
        'participant_id',
        'training_id',   // Pastikan ini ada di database Anda
        'evaluator_role',
        'evaluator_name',
        'question_id',
        'score',
        'note'
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    // Tambahkan juga relasi ke question agar bisa menarik teks soal nantinya
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function hasFilledL34($role)
    {
        return \App\Models\EvaluationResultL34::where('participant_id', $this->id)
            ->where('evaluator_role', $role)
            ->exists();
    }
}