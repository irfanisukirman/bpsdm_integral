<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DailyReportItem extends Model { protected $guarded=[]; public function report(){return $this->belongsTo(DailyReport::class,'daily_report_id');} }
