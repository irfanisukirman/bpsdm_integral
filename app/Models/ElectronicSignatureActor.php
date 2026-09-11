<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ElectronicSignatureActor extends Model {protected $guarded=[];public function request(){return $this->belongsTo(ElectronicSignatureRequest::class,'electronic_signature_request_id');}public function user(){return $this->belongsTo(User::class);}public function actions(){return $this->hasMany(ElectronicSignatureAction::class);} }