<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintMessage extends Model
{
    protected $fillable = ['user_id', 'from_staff', 'body', 'attachment'];

    protected $casts = ['from_staff' => 'boolean'];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
