<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Asset extends Model {protected $guarded=[];protected $casts=['is_active'=>'boolean','is_public'=>'boolean','is_rentable'=>'boolean','hourly_rate'=>'decimal:2'];public function images(){return $this->hasMany(AssetImage::class)->orderBy('sort_order');}public function bookings(){return $this->hasMany(AssetBooking::class);}public function publicReservations(){return $this->hasMany(AssetPublicReservation::class);}public function rentalRates(){return $this->hasMany(AssetRentalRate::class)->orderBy('sort_order')->orderBy('start_time');}}
