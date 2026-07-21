<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',     // Nama PIC
        'username', 
        'whatsapp', 
        'role',     // superadmin / admin_bidang
        'bidang',   // Bidang A, B, C, atau D
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}