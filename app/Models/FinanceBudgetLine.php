<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FinanceBudgetLine extends Model {
 protected $guarded=[]; protected $casts=['year'=>'integer','allocated_amount'=>'decimal:2'];
 public function parent(){return $this->belongsTo(self::class,'parent_id');}
 public function children(){return $this->hasMany(self::class,'parent_id');}
 public function transactions(){return $this->hasMany(FinanceTransaction::class);}
 public function guAllocations(){return $this->hasMany(FinanceGuAllocation::class);}
 public function creator(){return $this->belongsTo(User::class,'created_by');}
}
