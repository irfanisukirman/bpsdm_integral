<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ElectronicSignatureAction extends Model {protected $guarded=[];protected $casts=['signed_at'=>'datetime'];public function document(){return $this->belongsTo(ElectronicSignatureDocument::class,'electronic_signature_document_id');}public function actor(){return $this->belongsTo(ElectronicSignatureActor::class,'electronic_signature_actor_id');}public function attempts(){return $this->hasMany(ElectronicSignatureAttempt::class);} }