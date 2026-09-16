<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TicketStatusHistory extends Model {
 protected $fillable=['ticket_id','from_status','to_status','changed_by_name','changed_by_user_id','note'];
 protected $casts=['created_at'=>'datetime'];
 public $timestamps=false;
 protected static function booted():void{
  static::creating(function(TicketStatusHistory $h){
   if(!$h->created_at)$h->created_at=now();
  });
 }
 public function ticket(){return $this->belongsTo(Ticket::class);}
 public function changedBy(){return $this->belongsTo(User::class,'changed_by_user_id');
 }
}