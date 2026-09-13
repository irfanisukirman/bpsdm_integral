<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActivityAttendanceQuestion extends Model
{
    protected $guarded=[];
    protected $casts=['options'=>'array','is_required'=>'boolean'];
    public function form(){return $this->belongsTo(ActivityAttendanceForm::class,'activity_attendance_form_id');}
    public function answers(){return $this->hasMany(ActivityAttendanceAnswer::class);}
}
