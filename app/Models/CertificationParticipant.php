<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CertificationParticipant extends Model {protected $guarded=[];protected $casts=['biodata_submitted_at'=>'datetime','certificate_submitted_at'=>'datetime','certification_rating'=>'integer'];protected static function booted(){static::creating(fn($model)=>$model->biodata_token??=\Illuminate\Support\Str::random(48));}public function event(){return $this->belongsTo(CertificationEvent::class,'certification_event_id');}public function biodataFile(){return $this->belongsTo(File::class,'biodata_file_id');}public function certificateFile(){return $this->belongsTo(File::class,'certificate_file_id');}}
