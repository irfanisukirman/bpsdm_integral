<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniProfile extends Model
{
    protected $table = 'alumni_profiles';

    protected $fillable = [
        'participant_id',
        'training_id',
        'edu_during_training',
        'edu_current',
        'rank_during_training',
        'rank_current',
        'pos_during_training',
        'pos_current',
        'unit_during_training',
        'unit_current'
    ];
}