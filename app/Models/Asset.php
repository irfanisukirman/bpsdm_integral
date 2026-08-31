<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Asset extends Model {protected $guarded=[];protected $casts=['is_active'=>'boolean','is_public'=>'boolean'];public function images(){return $this->hasMany(AssetImage::class)->orderBy('sort_order');}public function bookings(){return $this->hasMany(AssetBooking::class);}}
