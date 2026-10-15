<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DailyReportAssignment extends Model { protected $guarded=[]; protected $casts=['start_date'=>'date','end_date'=>'date','is_active'=>'boolean']; public function user(){return $this->belongsTo(User::class);} public function supervisor(){return $this->belongsTo(User::class,'supervisor_user_id');} public function institution(){return $this->belongsTo(Institution::class);} public function reports(){return $this->hasMany(DailyReport::class,'assignment_id');} }
