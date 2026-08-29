<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AssetBooking extends Model {
 protected $guarded=[];protected $casts=['starts_at'=>'datetime','ends_at'=>'datetime'];
 public function asset(){return $this->belongsTo(Asset::class);}public function bookable(){return $this->morphTo();}
 public static function hasConflict(int $assetId,$start,$end,?string $ignoreType=null,?int $ignoreId=null):bool{return static::where('asset_id',$assetId)->where('starts_at','<',$end)->where('ends_at','>',$start)->when($ignoreType&&$ignoreId,fn($q)=>$q->where(fn($x)=>$x->where('bookable_type','!=',$ignoreType)->orWhere('bookable_id','!=',$ignoreId)))->exists();}
}
