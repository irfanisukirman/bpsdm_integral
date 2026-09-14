<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InternshipParticipant extends Model {
 protected $guarded=[];protected $casts=['start_date'=>'date','end_date'=>'date','reviewed_at'=>'datetime','certificate_generated_at'=>'datetime','certificate_sent_at'=>'datetime','certificate_downloaded_at'=>'datetime'];
 public function program(){return $this->belongsTo(InternshipProgram::class,'internship_program_id');}
 public function user(){return $this->belongsTo(User::class);}
 public function attendances(){return $this->hasMany(InternshipAttendance::class);}
 public function reviewer(){return $this->belongsTo(User::class,'reviewed_by');}
 public function electronicSignatureDocuments(){return $this->hasMany(ElectronicSignatureDocument::class);}
 public function certificateSender(){return $this->belongsTo(User::class,'certificate_sent_by');}
}