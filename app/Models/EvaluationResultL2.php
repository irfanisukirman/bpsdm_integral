<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationResultL2 extends Model
{
    // Tambahkan baris ini
    protected $table = 'evaluation_results_l2';

    protected $guarded = [];

    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }
}
