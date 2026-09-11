<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingActivityDocumentation extends Model
{
    protected $guarded = [];

    protected $casts = ['taken_at' => 'date', 'include_in_report' => 'boolean', 'is_featured' => 'boolean'];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
