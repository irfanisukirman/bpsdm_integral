<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostEvaluationInterview extends Model
{
    public const DEFAULT_NO_SCORE = 25.0;

    protected $guarded = [];

    protected $casts = ['scores' => 'array', 'continuation' => 'array', 'notes' => 'array', 'revision' => 'integer'];
}
