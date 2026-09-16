<?php
namespace App\Services;
use App\Models\TicketSla;
use App\Models\Ticket;
use Carbon\Carbon;
class SlaService {
 public static function resolve(?int $serviceId,?int $categoryId,?int $bidangId):array{
  $q=TicketSla::where('is_active',true);
  $specific=$q->where(function($q2)use($serviceId,$categoryId,$bidangId){
   $q2->where('service_id',$serviceId)->where('category_id',$categoryId)->where('bidang_id',$bidangId);
  })->first();
  if($specific)return['respond_hours'=>(float)$specific->respond_hours,'resolve_hours'=>(float)$specific->resolve_hours,'working_start'=>$specific->working_hours_start,'working_end'=>$specific->working_hours_end];
  $partial=TicketSla::where('is_active',true)->where(function($q2)use($serviceId,$categoryId){
   $q2->where('service_id',$serviceId)->where('category_id',$categoryId)->whereNull('bidang_id');
  })->first();
  if($partial)return['respond_hours'=>(float)$partial->respond_hours,'resolve_hours'=>(float)$partial->resolve_hours,'working_start'=>$partial->working_hours_start,'working_end'=>$partial->working_hours_end];
  $fallback=TicketSla::where('is_active',true)->whereNull('service_id')->whereNull('category_id')->whereNull('bidang_id')->first();
  if($fallback)return['respond_hours'=>(float)$fallback->respond_hours,'resolve_hours'=>(float)$fallback->resolve_hours,'working_start'=>$fallback->working_hours_start,'working_end'=>$fallback->working_hours_end];
  return['respond_hours'=>48,'resolve_hours'=>72,'working_start'=>'08:00','working_end'=>'17:00'];
 }
 public static function calculateIndicator(Ticket $ticket):string{
  if(in_array($ticket->status,['RESOLVED','CLOSED']))return 'selesai';
  if(!$ticket->sla_resolve_hours||!$ticket->created_at)return 'tidak_ditentukan';
  $deadline=Carbon::parse($ticket->created_at)->addHours((float)$ticket->sla_resolve_hours);
  $remaining=now()->diffInHours($deadline,false);
  if($remaining<0)return 'terlewati';
  $total=(float)$ticket->sla_resolve_hours;
  $pct=max(0,$remaining/$total)*100;
  if($pct<=50)return 'mendekati';
  return 'aman';
 }
}