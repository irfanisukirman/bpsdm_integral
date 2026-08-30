<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileVersion extends Model
{
    protected $fillable = ['file_id', 'version_number', 'file_path', 'file_type', 'file_size', 'uploaded_by', 'notes'];
    public function file() { return $this->belongsTo(File::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
}