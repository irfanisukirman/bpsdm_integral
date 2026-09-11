<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiGeneration extends Model
{
    protected $guarded = [];
    protected $casts = ['input_summary' => 'array', 'generated_content' => 'array', 'generated_at' => 'datetime'];
    public function training() { return $this->belongsTo(Training::class); }
    public function user() { return $this->belongsTo(User::class); }
}
