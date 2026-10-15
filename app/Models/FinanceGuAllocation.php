<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FinanceGuAllocation extends Model {
 protected $guarded=[];protected $casts=['amount'=>'decimal:2'];
 public function batch(){return $this->belongsTo(FinanceGuBatch::class,'finance_gu_batch_id');}
 public function budgetLine(){return $this->belongsTo(FinanceBudgetLine::class,'finance_budget_line_id');}
}