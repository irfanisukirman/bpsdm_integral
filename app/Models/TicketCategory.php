<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TicketCategory extends Model {
 protected $fillable=['name','slug','is_active','sort_order'];
 protected $casts=['is_active'=>'boolean'];
 public function routingRules(){return $this->hasMany(TicketRoutingRule::class);}
 public function slas(){return $this->hasMany(TicketSla::class);}
}