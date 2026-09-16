<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TicketBidang extends Model {
 protected $table='ticket_bidang';
 protected $fillable=['name','is_active','default_handler_user_id'];
 protected $casts=['is_active'=>'boolean'];
 public function defaultHandler(){return $this->belongsTo(User::class,'default_handler_user_id');}
 public function routingRules(){return $this->hasMany(TicketRoutingRule::class);}
 public function slas(){return $this->hasMany(TicketSla::class);}
}