<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoringResult extends Model
{
    protected $table = 'monitoring_results';

    protected $fillable = [
        'training_id', 'training_stage_id', 'monitoring_date', 'question_id', 'category',
        'answer', 'notes', 'recommendation', 'follow_up_target', 'priority', 'due_date',
        'workflow_status', 'is_resolved', 'resolution_notes', 'evidence_file',
        'submitted_by', 'submitted_at', 'verified_by', 'verified_at', 'verification_notes',
    ];

    protected $casts = [
        'monitoring_date' => 'date',
        'due_date' => 'date',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_resolved' => 'boolean',
    ];

    public function training() {
        return $this->belongsTo(Training::class);
    }

    public function question() {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function stage()
    {
        return $this->belongsTo(TrainingStage::class, 'training_stage_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
