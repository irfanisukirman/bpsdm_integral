<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostEvaluationInterview extends Model
{
    protected $guarded = [];

    protected $casts = ['scores' => 'array', 'continuation' => 'array', 'notes' => 'array', 'revision' => 'integer'];
}
