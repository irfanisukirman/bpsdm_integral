<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PartnerSubmissionDocument extends Model {protected $fillable=['partner_submission_id','uploaded_by','version_number','display_name','file_path','file_type','file_size','change_note','is_final'];protected $casts=['is_final'=>'boolean'];public function submission(){return $this->belongsTo(PartnerSubmission::class,'partner_submission_id');}public function uploader(){return $this->belongsTo(User::class,'uploaded_by');}}