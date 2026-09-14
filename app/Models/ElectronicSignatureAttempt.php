<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ElectronicSignatureAttempt extends Model {protected $guarded=[];protected $casts=['successful'=>'boolean'];public function action(){return $this->belongsTo(ElectronicSignatureAction::class,'electronic_signature_action_id');}public function user(){return $this->belongsTo(User::class);} }