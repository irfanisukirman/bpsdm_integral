<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingExecutionNote extends Model
{
    protected $fillable = ['training_id', 'created_by', 'title', 'note'];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}