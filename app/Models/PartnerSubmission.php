<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PartnerSubmission extends Model {
 protected $fillable=['user_id','type','target_bidang','title','background','objective','scope','participant_target','estimated_participants','competency','preferred_start','preferred_end','method','location','period_start','period_end','pic_name','pic_contact','status','assigned_to','folder_id','submitted_at','finalized_at'];
 protected $casts=['preferred_start'=>'date','preferred_end'=>'date','period_start'=>'date','period_end'=>'date','submitted_at'=>'datetime','finalized_at'=>'datetime'];
 public function partner(){return $this->belongsTo(User::class,'user_id');} public function assignee(){return $this->belongsTo(User::class,'assigned_to');} public function documents(){return $this->hasMany(PartnerSubmissionDocument::class);} public function comments(){return $this->hasMany(PartnerSubmissionComment::class);} public function folder(){return $this->belongsTo(Folder::class);}
 public function getTypeLabelAttribute(){return $this->type==='training'?'Pengajuan Pelatihan':'Pengajuan Kerja Sama';}
 public function getStatusLabelAttribute(){return match($this->status){'draft'=>'Draft','submitted'=>'Dikirim','under_review'=>'Sedang Ditinjau','revision_requested'=>'Perlu Revisi','revision_submitted'=>'Revisi Dikirim','waiting_approval'=>'Menunggu Persetujuan','final'=>'Final','rejected'=>'Ditolak',default=>ucfirst($this->status)};}
}