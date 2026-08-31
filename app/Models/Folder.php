<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $fillable = [
        'training_id',
        'name',
        'bidang',
        'parent_id',
        'user_id',
        'is_public',
        'share_token'
    ];

    public function files() { 
        return $this->hasMany(File::class); 
    }

    public function children() { 
        return $this->hasMany(Folder::class, 'parent_id'); 
    }

    public function permissions()
    {
        return $this->hasMany(FolderUserPermission::class);
    }

    public function sharedUsers()
    {
        return $this->belongsToMany(User::class, 'folder_user_permissions')->withPivot(['permission', 'shared_by'])->withTimestamps();
    }
    public function parent() { 
        return $this->belongsTo(Folder::class, 'parent_id'); 
    }
    
    public function user() {
        return $this->belongsTo(User::class);
    }
}
