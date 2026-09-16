<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TicketSla extends Model {
 protected $fillable=['service_id','category_id','bidang_id','respond_hours','resolve_hours','working_hours_start','working_hours_end','is_active'];
 protected $casts=['is_active'=>'boolean','respond_hours'=>'decimal:2','resolve_hours'=>'decimal:2'];
 public function service(){return $this->belongsTo(TicketService::class);}
 public function category(){return $this->belongsTo(TicketCategory::class);}
 public function bidang(){return $this->belongsTo(TicketBidang::class);}
}