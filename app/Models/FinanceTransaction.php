<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FinanceTransaction extends Model {
 protected $guarded=[];protected $casts=['transaction_date'=>'date','gross_amount'=>'decimal:2','tax_amount'=>'decimal:2','net_amount'=>'decimal:2','realized_amount'=>'decimal:2','realized_at'=>'date','reviewed_at'=>'datetime','paid_at'=>'datetime'];
 public function budgetLine(){return $this->belongsTo(FinanceBudgetLine::class,'finance_budget_line_id');}
 public function schedule(){return $this->belongsTo(Schedule::class);}
 public function creator(){return $this->belongsTo(User::class,'created_by');}
 public function reviewer(){return $this->belongsTo(User::class,'reviewed_by');}
 public function getStatusLabelAttribute(){return match($this->status){'draft'=>'Draft','submitted'=>'Diajukan','verified'=>'Diverifikasi','ready'=>'Siap Dibayar','paid'=>'Sudah Dibayar','revision'=>'Perlu Perbaikan','rejected'=>'Ditolak','cancelled'=>'Dibatalkan',default=>ucfirst($this->status)};}
}
