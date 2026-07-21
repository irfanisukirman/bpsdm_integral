<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationResultL1 extends Model
{
    // Pastikan diarahkan ke tabel jamak hasil evaluasi
    protected $table = 'evaluation_results_l1';

    protected $fillable = [
        'training_id',
        'participant_id',
        'question_id',
        'schedule_id', 
        'score',       
        'note'         
    ];
}