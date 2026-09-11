<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AssetRentalRate extends Model {protected $guarded=[];protected $casts=['hourly_rate'=>'decimal:2'];public function asset(){return $this->belongsTo(Asset::class);}}