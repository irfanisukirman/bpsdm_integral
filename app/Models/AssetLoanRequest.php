<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AssetLoanRequest extends Model {
 protected $guarded=[];protected $casts=['asset_ids'=>'array','reviewed_at'=>'datetime'];
 public function requestable(){return $this->morphTo();}
 public function submitter(){return $this->belongsTo(User::class,'submitted_by');}
 public function reviewer(){return $this->belongsTo(User::class,'reviewed_by');}
 public function assets(){return Asset::whereIn('id',$this->asset_ids??[])->get();}
}
