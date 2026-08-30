<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FolderUserPermission extends Model
{
    protected $fillable = ['folder_id', 'user_id', 'permission', 'shared_by', 'seen_at'];
    protected $casts = ['seen_at' => 'datetime'];
    public function folder() { return $this->belongsTo(Folder::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function sharer() { return $this->belongsTo(User::class, 'shared_by'); }
}