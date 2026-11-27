<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingFinalQuizAttempt extends Model
{
    protected $fillable = ['participant_id', 'score', 'pass_score', 'passed', 'correct_count', 'questions_count', 'answers'];

    protected $casts = ['score' => 'decimal:2', 'passed' => 'boolean', 'answers' => 'array'];

    protected $hidden = ['answers'];

    public function passes(float $finalScore): bool
    {
        return $finalScore >= $this->pass_score;
    }
}
