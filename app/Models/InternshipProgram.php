<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InternshipProgram extends Model {
 protected $guarded=[];protected $casts=['registration_opens_at'=>'date','registration_closes_at'=>'date','certificate_issued_at'=>'date','certificate_reviewer_ids'=>'array'];
 public function participants(){return $this->hasMany(InternshipParticipant::class);}
 public function creator(){return $this->belongsTo(User::class,'created_by');}
 public function manager(){return $this->belongsTo(User::class,'manager_id');}
 public function getPublicUrlAttribute(){return route('internships.public.register',$this->public_token);}
 public function isRegistrationOpen():bool {if($this->status!=='open')return false;$today=today();return (!$this->registration_opens_at||$today->gte($this->registration_opens_at))&&(!$this->registration_closes_at||$today->lte($this->registration_closes_at));}
}