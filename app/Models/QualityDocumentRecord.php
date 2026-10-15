<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualityDocumentRecord extends Model
{
    protected $guarded = [];
    protected $casts = ['is_required' => 'boolean', 'requirement_updated_at' => 'datetime'];
    public function training() { return $this->belongsTo(Training::class); }
    public function file() { return $this->belongsTo(File::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
}
