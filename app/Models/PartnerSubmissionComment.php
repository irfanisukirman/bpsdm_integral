<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PartnerSubmissionComment extends Model {protected $fillable=['partner_submission_id','user_id','message'];public function submission(){return $this->belongsTo(PartnerSubmission::class,'partner_submission_id');}public function user(){return $this->belongsTo(User::class);}}