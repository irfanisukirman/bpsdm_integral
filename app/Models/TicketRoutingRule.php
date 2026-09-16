<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TicketRoutingRule extends Model {
 protected $fillable=['service_id','category_id','bidang_id','default_pic_user_id','sla_respond_hours','sla_resolve_hours','is_active'];
 protected $casts=['is_active'=>'boolean','sla_respond_hours'=>'decimal:2','sla_resolve_hours'=>'decimal:2'];
 public function service(){return $this->belongsTo(TicketService::class);}
 public function category(){return $this->belongsTo(TicketCategory::class);}
 public function bidang(){return $this->belongsTo(TicketBidang::class);}
 public function defaultPic(){return $this->belongsTo(User::class,'default_pic_user_id');
 }
}