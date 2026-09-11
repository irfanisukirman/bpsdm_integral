<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $fillable = [
        'training_id',
        'document_year',
        'is_archived',
        'archived_at',
        'archived_by',
        'name',
        'bidang',
        'parent_id',
        'user_id',
        'is_public',
        'share_token'
    ];

    protected $casts = ['is_archived' => 'boolean', 'archived_at' => 'datetime', 'document_year' => 'integer'];

    protected static function booted(): void
    {
        static::creating(function (Folder $folder) {
            if (!$folder->parent_id && !$folder->document_year) {
                $date = $folder->training_id ? Training::whereKey($folder->training_id)->value('tgl_mulai') : null;
                $folder->document_year = $date ? (int) substr((string) $date, 0, 4) : (int) now()->format('Y');
            }
        });
    }

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
    
    public function archiver() { return $this->belongsTo(User::class, 'archived_by'); }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
