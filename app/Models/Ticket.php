<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Ticket extends Model {
 use HasFactory;
 protected $fillable=['ticket_number','tracking_token','user_type','submitter_name','nip_nik','perangkat_daerah','email','service','category','message','attachment_path','status','bidang','assigned_to','created_by_user_id','sla_respond_hours','sla_resolve_hours','first_response_at','resolved_at','closed_at','is_reopened','reopen_count','auto_close_at'];
 protected $casts=['first_response_at'=>'datetime','resolved_at'=>'datetime','closed_at'=>'datetime','auto_close_at'=>'datetime','is_reopened'=>'boolean'];
 public function messages(){return $this->hasMany(TicketMessage::class)->orderBy('created_at');}
 public function statusHistories(){return $this->hasMany(TicketStatusHistory::class)->orderBy('created_at');}
 public function assignee(){return $this->belongsTo(User::class,'assigned_to');}
 public function creator(){return $this->belongsTo(User::class,'created_by_user_id');}
 public function getStatusLabelAttribute(){return match($this->status){'BARU'=>'Baru','DIPROSES'=>'Diproses','MENUNGGU_PENGGUNA'=>'Menunggu Respons Pengguna','RESOLVED'=>'Resolved','CLOSED'=>'Closed',default=>ucfirst($this->status)};}
 public function getStatusColorAttribute(){return match($this->status){'BARU'=>'primary','DIPROSES'=>'info','MENUNGGU_PENGGUNA'=>'warning','RESOLVED'=>'success','CLOSED'=>'secondary',default=>'secondary'};}
 public function getSlaIndicatorAttribute(){
  if(!$this->sla_resolve_hours)return 'tidak_ditentukan';
  $deadline=$this->created_at->copy()->addHours((float)$this->sla_resolve_hours);
  $remaining=now()->diffInHours($deadline,false);
  if($this->status==='RESOLVED'||$this->status==='CLOSED')return 'selesai';
  if($remaining<0)return 'terlewati';
  $total=(float)$this->sla_resolve_hours;
  $pct=max(0,$remaining/$total)*100;
  if($pct<=50)return 'mendekati';
  return 'aman';
 }
 public function isAccessibleBy(\App\Models\User $user):bool{
  if($user->role==='superadmin')return true;
  if($user->role==='admin_bidang')return $this->bidang===$user->bidang;
  return false;
 }
}