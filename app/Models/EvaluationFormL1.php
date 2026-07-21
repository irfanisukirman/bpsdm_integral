<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationFormL1 extends Model
{
    // Tambahkan baris ini agar model membaca tabel 'evaluation_forms'
    protected $table = 'evaluation_forms';

    protected $fillable = [
        'training_id', 
        'type', 
        'name', 
        'schedule_id', 
        'target_name', 
        'materi'
    ];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
}
