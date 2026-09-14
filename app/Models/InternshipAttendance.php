<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InternshipAttendance extends Model {
 protected $guarded=[];protected $casts=['attendance_date'=>'date','check_in_at'=>'datetime','check_out_at'=>'datetime','reviewed_at'=>'datetime'];
 public function participant(){return $this->belongsTo(InternshipParticipant::class,'internship_participant_id');}
 public function reviewer(){return $this->belongsTo(User::class,'reviewed_by');}
}