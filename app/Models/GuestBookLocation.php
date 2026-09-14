<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GuestBookLocation extends Model {protected $guarded=[];protected $casts=['is_active'=>'boolean'];public function visits(){return $this->hasMany(GuestVisit::class);}public function creator(){return $this->belongsTo(User::class,'created_by');}public function getPublicUrlAttribute():string{return route('guest-book.public.show',$this->public_token);}}