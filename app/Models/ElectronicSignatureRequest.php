<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ElectronicSignatureRequest extends Model {protected $guarded=[];protected $casts=['completed_at'=>'datetime'];public function documents(){return $this->hasMany(ElectronicSignatureDocument::class);}public function actors(){return $this->hasMany(ElectronicSignatureActor::class)->orderBy('sequence');}public function creator(){return $this->belongsTo(User::class,'created_by');}}