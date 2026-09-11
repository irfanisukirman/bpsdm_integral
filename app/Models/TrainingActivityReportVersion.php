<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingActivityReportVersion extends Model
{
    protected $guarded = [];

    protected $casts = ['snapshot' => 'array'];

    public function report()
    {
        return $this->belongsTo(TrainingActivityReport::class, 'training_activity_report_id');
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
