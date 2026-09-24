<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipantIdentityCard extends Model
{
    protected $guarded = [];
    protected $casts = ['generated_at' => 'datetime'];

    public function participant() { return $this->belongsTo(Participant::class); }
}