<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TicketMessage extends Model {
 protected $fillable=['ticket_id','sender_name','sender_role','sender_user_id','message','is_internal','attachment_path'];
 protected $casts=['is_internal'=>'boolean'];
 public function ticket(){return $this->belongsTo(Ticket::class);}
 public function sender(){return $this->belongsTo(User::class,'sender_user_id');
 }
}