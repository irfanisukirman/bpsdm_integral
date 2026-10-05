<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CooperationRecord extends Model
{
    protected $guarded = [];

    protected $casts = [
        'year' => 'integer',
        'file_size' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
