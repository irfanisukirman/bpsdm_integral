<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoringResult extends Model
{
    protected $table = 'monitoring_results';

    protected $fillable = ['training_id', 'training_stage_id', 'question_id', 'category', 'answer', 'notes', 'follow_up_target', 'is_resolved'];

    public function training() {
        return $this->belongsTo(Training::class);
    }

    public function question() {
        return $this->belongsTo(Question::class, 'question_id');
    }
}