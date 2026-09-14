<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GuestVisit extends Model {protected $guarded=[];protected $casts=['checked_in_at'=>'datetime','checked_out_at'=>'datetime'];public function location(){return $this->belongsTo(GuestBookLocation::class,'guest_book_location_id');}}