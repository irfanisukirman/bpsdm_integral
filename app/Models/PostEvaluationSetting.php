<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostEvaluationSetting extends Model
{
    protected $guarded = [];

    protected $casts = ['mappings' => 'array', 'rubric' => 'array', 'snapshot' => 'array', 'report_narrative' => 'array', 'published_at' => 'datetime'];
}
