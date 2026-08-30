<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CertificationType extends Model {protected $guarded=[];public function events(){return $this->hasMany(CertificationEvent::class);}}
