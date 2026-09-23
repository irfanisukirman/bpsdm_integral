<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingIdentityCardSetting extends Model
{
    protected $guarded = [];
    protected $casts = ['enabled' => 'boolean', 'background_opacity' => 'integer'];

    public function training() { return $this->belongsTo(Training::class); }
    public function updater() { return $this->belongsTo(User::class, 'updated_by'); }
}