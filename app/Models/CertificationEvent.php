<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CertificationEvent extends Model {
 protected $guarded=[];protected $casts=['start_date'=>'date','end_date'=>'date'];
 protected static function booted(){static::creating(fn($model)=>$model->public_token??=\Illuminate\Support\Str::random(48));}
 public function type(){return $this->belongsTo(CertificationType::class,'certification_type_id');}
 public function participants(){return $this->hasMany(CertificationParticipant::class);}
 public function folder(){return $this->belongsTo(Folder::class);}
 public function minutesFile(){return $this->belongsTo(File::class,'minutes_file_id');}
 public function creator(){return $this->belongsTo(User::class,'created_by');}
}
