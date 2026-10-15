<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DailyReport extends Model { protected $guarded=[]; protected $casts=['report_date'=>'date','submitted_at'=>'datetime','reviewed_at'=>'datetime']; public function assignment(){return $this->belongsTo(DailyReportAssignment::class);} public function items(){return $this->hasMany(DailyReportItem::class)->orderBy('sort_order');} public function reviewer(){return $this->belongsTo(User::class,'reviewed_by');} }
