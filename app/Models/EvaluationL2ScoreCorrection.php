<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationL2ScoreCorrection extends Model
{
    protected $fillable = ['changed_by', 'previous_score', 'score', 'reason'];
}
