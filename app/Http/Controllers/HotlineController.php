<?php
namespace App\Http\Controllers;
use App\Models\Ticket;
use App\Models\TicketService;
use App\Models\TicketCategory;
use App\Models\TicketMessage;
use App\Models\User;
use App\Models\HotlineAvailabilitySetting;
use App\Mail\TicketUserReplyMail;
use App\Services\TicketingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
class HotlineController extends Controller {
private function guardPengguna():void{
   abort_if(auth()->check()&&in_array(auth()->user()->role,['superadmin','admin_bidang'],true),403,'Halaman ini khusus untuk pengguna biasa. Gunakan menu Kelola Layanan untuk administrasi tiket.');
  }
public function availability(){
   return response()->json(HotlineAvailabilitySetting::current()->statusInfo());
 }
public function store(Request $request){
  $this->guardPengguna();
  $validated=$request->validate([
   'user_type'=>'required|in:PNS,PPPK,Non-ASN',
   'name'=>'required|string|max:255',
   'nip_nik'=>'required|string|max:30',
   'perangkat_daerah'=>'required|string|max:255',
'email'=>'required|email|max:255',
    'service'=>'required|exists:ticket_services,slug',
   'category'=>'required|exists:ticket_categories,slug',
   'message'=>'required|string|max:5000',
   'attachment'=>'nullable|file|max:2048|mimes:jpg,jpeg,png,gif',
  ]);
  $validated['attachment_path']=$request->hasFile('attachment')?$request->file('attachment')->store('ticket-attachments','public'):null;
  $userId=auth()->check()?auth()->id():null;
  $validated['user_id']=$userId;
  $ticket=TicketingService::create($validated);
  if($request->wantsJson()){
   return response()->json([
    'success'=>true,
    'message'=>'Tiket berhasil dibuat.',
    'ticket_number'=>$ticket->ticket_number,
    'tracking_url'=>route('hotline.tracking',$ticket->tracking_token),
   ]);
  }
  return redirect()->route('hotline.success',$ticket->ticket_number)->with('success','Tiket berhasil dibuat.');
 }
 public function myTickets(){
   $tickets=Ticket::where('created_by_user_id',auth()->id())
    ->with(['assignee'])
    ->orderByDesc('created_at')
    ->paginate(10);
   return view('hotline.my',compact('tickets'));
  }
  public function success(string $ticketNumber){
  $ticket=Ticket::where('ticket_number',$ticketNumber)->firstOrFail();
  return view('hotline.success',compact('ticket'));
 }
 public function tracking(string $token){
  $ticket=Ticket::where('tracking_token',$token)->firstOrFail();
  $messages=$ticket->messages()->where('is_internal',false)->orderBy('created_at')->get();
  $statusHistories=$ticket->statusHistories()->orderBy('created_at')->get();
  $canReply=in_array($ticket->status,['BARU','DIPROSES','MENUNGGU_PENGGUNA','RESOLVED']);
  return view('hotline.tracking',compact('ticket','messages','statusHistories','canReply'));
 }
 public function replyTracking(Request $request,string $token){
  $ticket=Ticket::where('tracking_token',$token)->firstOrFail();
  if(!in_array($ticket->status,['BARU','DIPROSES','MENUNGGU_PENGGUNA','RESOLVED'])){
   return back()->with('error','Tiket tidak dapat dibalas.');
  }
  $validated=$request->validate([
   'message'=>'required|string|max:5000',
  ]);
  $msg=TicketMessage::create([
   'ticket_id'=>$ticket->id,
   'sender_name'=>$ticket->submitter_name,
   'sender_role'=>'user',
   'sender_user_id'=>$ticket->created_by_user_id,
   'message'=>$validated['message'],
  ]);
if($ticket->status==='MENUNGGU_PENGGUNA'){
    TicketingService::changeStatus($ticket,'DIPROSES',$ticket->submitter_name,$ticket->created_by_user_id,'Pengguna merespons');
   }elseif($ticket->status==='RESOLVED'){
    TicketingService::changeStatus($ticket,'DIPROSES',$ticket->submitter_name,$ticket->created_by_user_id,'Pengguna membuka kembali tiket');
   }
   $this->notifyPicsOfUserReply($ticket,$msg);
   return back()->with('success','Balasan berhasil dikirim.');
  }
  private function notifyPicsOfUserReply(Ticket $ticket,TicketMessage $msg):void{
   $recipients=collect();
   if($ticket->assigned_to){
    $pic=User::find($ticket->assigned_to);
    if($pic&&$pic->email)$recipients->push($pic);
   }
   User::where('role','admin_bidang')->where('bidang',$ticket->bidang)->whereNotNull('email')->get()->each(function($u)use($recipients){$recipients->push($u);});
   $recipients=$recipients->filter(fn($u)=>filter_var($u->email,FILTER_VALIDATE_EMAIL))->unique('email');
   $recipients->each(function($u)use($ticket,$msg){
    try{Mail::to($u->email)->send(new TicketUserReplyMail($ticket,$msg));}catch(\Exception $e){}
   });
  }
 }