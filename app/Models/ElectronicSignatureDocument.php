<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ElectronicSignatureDocument extends Model {protected $guarded=[];protected $casts=['completed_at'=>'datetime'];protected static function booted(){static::creating(fn($model)=>$model->verification_token??=(string)\Illuminate\Support\Str::uuid());}public function request(){return $this->belongsTo(ElectronicSignatureRequest::class,'electronic_signature_request_id');}public function actions(){return $this->hasMany(ElectronicSignatureAction::class);}public function participantCertificate(){return $this->belongsTo(ParticipantCertificate::class);}public function internshipParticipant(){return $this->belongsTo(InternshipParticipant::class);} }
