<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Agenda extends Model {protected $guarded=[];protected $casts=['is_public'=>'boolean'];public function schedules(){return $this->hasMany(AgendaSchedule::class);}public function creator(){return $this->belongsTo(User::class,'created_by');}}
