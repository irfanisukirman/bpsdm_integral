<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActivityAttendanceAnswer extends Model
{
    protected $guarded=[];
    protected $casts=['value_json'=>'array'];
    public function response(){return $this->belongsTo(ActivityAttendanceResponse::class,'activity_attendance_response_id');}
    public function question(){return $this->belongsTo(ActivityAttendanceQuestion::class,'activity_attendance_question_id');}
}
