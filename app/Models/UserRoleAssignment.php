<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoleAssignment extends Model
{
    protected $fillable = ['role', 'bidang'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
