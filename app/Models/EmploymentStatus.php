<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EmploymentStatus extends Model { protected $guarded=[]; protected $casts=['is_active'=>'boolean']; public function users(){return $this->hasMany(User::class);} }
