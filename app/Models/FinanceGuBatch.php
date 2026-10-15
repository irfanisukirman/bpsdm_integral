<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FinanceGuBatch extends Model {
 protected $guarded=[];protected $casts=['gu_date'=>'date','amount'=>'decimal:2'];
 public function allocations(){return $this->hasMany(FinanceGuAllocation::class);}
 public function creator(){return $this->belongsTo(User::class,'created_by');}
}