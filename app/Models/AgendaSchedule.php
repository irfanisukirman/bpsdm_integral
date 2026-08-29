<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AgendaSchedule extends Model {protected $guarded=[];protected $casts=['starts_at'=>'datetime','ends_at'=>'datetime'];public function agenda(){return $this->belongsTo(Agenda::class);}public function bookings(){return $this->morphMany(AssetBooking::class,'bookable');}}
