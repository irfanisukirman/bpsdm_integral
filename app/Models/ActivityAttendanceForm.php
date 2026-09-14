<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActivityAttendanceForm extends Model
{
    protected $guarded=[];
    protected $casts=['opens_at'=>'datetime','closes_at'=>'datetime'];
    public function creator(){return $this->belongsTo(User::class,'created_by');}
    public function questions(){return $this->hasMany(ActivityAttendanceQuestion::class)->orderBy('sort_order')->orderBy('id');}
    public function responses(){return $this->hasMany(ActivityAttendanceResponse::class);}
    public function getPublicUrlAttribute():string{return route('activity-attendance.public.show',$this->public_token);}
    public function isOpen():bool{return $this->status==='open'&&(!$this->opens_at||now()->gte($this->opens_at))&&(!$this->closes_at||now()->lte($this->closes_at));}
}
