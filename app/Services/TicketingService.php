<?php
namespace App\Services;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketStatusHistory;
use App\Models\TicketRoutingRule;
use App\Models\User;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketCreatedMail;
use App\Mail\TicketResponseMail;
use App\Mail\TicketResolvedMail;
class TicketingService {
 public static function create(array $data):Ticket{
  return DB::transaction(function()use($data){
   $ticketNumber=TicketNumberGenerator::generate();
   $trackingToken=TrackingTokenService::generate();
   $serviceModel=\App\Models\TicketService::where('slug',$data['service'])->first();
   $categoryModel=\App\Models\TicketCategory::where('slug',$data['category'])->first();
   $rule=TicketRoutingRule::where('service_id',$serviceModel->id)->where('category_id',$categoryModel->id)->where('is_active',true)->first();
   $bidang=$rule?->bidang?->name??'Sekretariat';
   $defaultPic=$rule?->default_pic_user_id;
   $sla=SlaService::resolve($serviceModel?->id,$categoryModel?->id,$rule?->bidang_id);
$ticket=Ticket::create([
     'ticket_number'=>$ticketNumber,'tracking_token'=>$trackingToken,
     'user_type'=>$data['user_type'],'submitter_name'=>$data['name'],
     'nip_nik'=>$data['nip_nik'],'perangkat_daerah'=>$data['perangkat_daerah']??null,
     'email'=>$data['email'],'phone'=>$data['phone'],
     'service'=>$data['service'],'category'=>$data['category'],
     'message'=>$data['message'],'attachment_path'=>$data['attachment_path']??null,
     'status'=>'BARU','bidang'=>$bidang,
     'assigned_to'=>$defaultPic,'created_by_user_id'=>$data['user_id']??null,
     'sla_respond_hours'=>$sla['respond_hours'],'sla_resolve_hours'=>$sla['resolve_hours'],
    ]);
   TicketStatusHistory::create(['ticket_id'=>$ticket->id,'from_status'=>null,'to_status'=>'BARU','changed_by_name'=>'System','note'=>'Tiket dibuat']);
   LogHelper::record('Hotline','Tiket baru dibuat: '.$ticketNumber);
   try{Mail::to($ticket->email)->send(new TicketCreatedMail($ticket));}catch(\Exception $e){}
   if($defaultPic){
    $pic=User::find($defaultPic);
    if($pic&&$pic->email){try{Mail::to($pic->email)->send(new TicketCreatedMail($ticket));}catch(\Exception $e){}}
   }
   return $ticket;
  });
 }
public static function respond(Ticket $ticket,string $message,User $sender,bool $isInternal=false):TicketMessage{
   $msg=TicketMessage::create([
    'ticket_id'=>$ticket->id,'sender_name'=>$sender->name,
    'sender_role'=>$sender->role,'sender_user_id'=>$sender->id,
    'message'=>$message,'is_internal'=>$isInternal,
   ]);
  if($ticket->first_response_at===null&&$sender->role!=='participant'){
   $ticket->update(['first_response_at'=>now()]);
  }
  if($ticket->status==='MENUNGGU_PENGGUNA'&&$sender->role!=='participant'){
   self::changeStatus($ticket,'DIPROSES',$sender->name,$sender->id,'Pengguna merespons');
  }
   if($ticket->email){try{Mail::to($ticket->email)->send(new TicketResponseMail($ticket,$msg));}catch(\Exception $e){}}
   LogHelper::record('Hotline','Respons tiket: '.$ticket->ticket_number);
   return $msg;
 }
public static function changeStatus(Ticket $ticket,string $newStatus,string $changedByName,?int $changedByUserId=null,?string $note=null):void{
   $old=$ticket->status;
   $updates=['status'=>$newStatus];
   if($newStatus==='DIPROSES'&&$old==='BARU'){$updates['first_response_at']=$ticket->first_response_at??now();}
   if($newStatus==='RESOLVED'){
    $updates['resolved_at']=now();
    if(!$ticket->auto_close_at){$updates['auto_close_at']=now()->addHours((int)config('ticketing.auto_close_hours',72));}
   }
   if($newStatus==='CLOSED'){$updates['closed_at']=now();$updates['auto_close_at']=null;}
   if($newStatus==='DIPROSES'&&$old==='RESOLVED'){$updates['is_reopened']=true;$updates['reopen_count']=$ticket->reopen_count+1;$updates['auto_close_at']=null;}
  $ticket->update($updates);
  TicketStatusHistory::create(['ticket_id'=>$ticket->id,'from_status'=>$old,'to_status'=>$newStatus,'changed_by_name'=>$changedByName,'changed_by_user_id'=>$changedByUserId,'note'=>$note]);
  if($newStatus==='RESOLVED'||$newStatus==='CLOSED'){
   try{Mail::to($ticket->email)->send(new TicketResolvedMail($ticket));}catch(\Exception $e){}
  }
  LogHelper::record('Hotline','Status tiket '.$ticket->ticket_number.' diubah: '.$old.' → '.$newStatus);
 }
 public static function assign(Ticket $ticket,int $userId,string $assignedByName,?int $assignedById=null):void{
  $pic=User::find($userId);
  if(!$pic)abort(404);
  $ticket->update(['assigned_to'=>$userId]);
  TicketStatusHistory::create(['ticket_id'=>$ticket->id,'from_status'=>$ticket->status,'to_status'=>$ticket->status,'changed_by_name'=>$assignedByName,'changed_by_user_id'=>$assignedById,'note'=>'Ditugaskan ke: '.$pic->name]);
  LogHelper::record('Hotline','Tiket '.$ticket->ticket_number.' ditugaskan ke: '.$pic->name);
 }
 public static function transfer(Ticket $ticket,string $newBidang,?int $newUserId,string $transferredByName,?int $transferredById=null,?string $note=null):void{
  $oldBidang=$ticket->bidang;
  $updates=['bidang'=>$newBidang];
  if($newUserId)$updates['assigned_to']=$newUserId;
  $ticket->update($updates);
  TicketStatusHistory::create(['ticket_id'=>$ticket->id,'from_status'=>$ticket->status,'to_status'=>$ticket->status,'changed_by_name'=>$transferredByName,'changed_by_user_id'=>$transferredById,'note'=>$note??'Dialihkan dari '.$oldBidang.' ke '.$newBidang]);
  LogHelper::record('Hotline','Tiket '.$ticket->ticket_number.' dialihkan dari '.$oldBidang.' ke '.$newBidang);
 }
 public static function getTicketForUser(Ticket $ticket,?User $user):?Ticket{
  if(!$user){
   return null;
  }
  if($user->role==='superadmin')return $ticket;
  if($user->role==='admin_bidang'&&$ticket->bidang===$user->bidang)return $ticket;
  return null;
 }
}