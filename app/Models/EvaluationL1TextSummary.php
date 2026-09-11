<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationL1TextSummary extends Model
{
    protected $fillable = [
        'training_id',
        'conclusion',
        'follow_up',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
