<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingActivityReport extends Model
{
    protected $guarded = [];

    protected $casts = ['approval_date' => 'date'];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function versions()
    {
        return $this->hasMany(TrainingActivityReportVersion::class)->latest('version');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
