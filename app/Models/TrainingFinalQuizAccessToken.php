<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingFinalQuizAccessToken extends Model
{
    public $timestamps = false;

    protected $fillable = ['participant_id', 'token'];

    protected $casts = ['token' => 'encrypted'];

    protected $hidden = ['token'];
}
