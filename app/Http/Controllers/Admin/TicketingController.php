<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Models\TicketBidang;
use App\Services\TicketingService;
use App\Services\SlaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class TicketingController extends Controller {
 private function authorizeTicketAccess(Ticket $ticket){
  $user=Auth::user();
  if($user->role==='superadmin')return true;
  if($user->role==='admin_bidang'&&$ticket->bidang===$user->bidang)return true;
  abort(403,'Anda tidak memiliki akses ke tiket ini.');
 }
 private function getScopeQuery(){
  $user=Auth::user();
  $q=Ticket::query();
  if($user->role!=='superadmin'){$q->where('bidang',$user->bidang);}
  return $q;
 }
 public function dashboard(){
  $user=Auth::user();
  $q=$this->getScopeQuery();
  $stats=[
   'total'=>(clone $q)->count(),
   'open'=>(clone $q)->where('status','!=','CLOSED')->count(),
   'closed'=>(clone $q)->where('status','CLOSED')->count(),
  ];
  $tickets=(clone $q)->with('assignee')->latest()->get();
  $slaApproaching=0;$slaOverdue=0;
  foreach($tickets as $t){
   if($t->status==='RESOLVED'||$t->status==='CLOSED')continue;
   if(!$t->sla_resolve_hours||!$t->created_at)continue;
   $indicator=SlaService::calculateIndicator($t);
   if($indicator==='mendekati')$slaApproaching++;
   if($indicator==='terlewati')$slaOverdue++;
  }
  $stats['sla_approaching']=$slaApproaching;
  $stats['sla_overdue']=$slaOverdue;
  $monthlyData=collect(range(1,12))->map(function($month)use($q,$tickets){
   $year=now()->year;
   return $tickets->filter(fn($t)=>$t->created_at->year===$year&&$t->created_at->month===$month)->count();
  })->values();
  $categoryData=$tickets->groupBy('category')->map(fn($g)=>$g->count())->sortDesc();
  $bidangData=$tickets->groupBy('bidang')->map(fn($g)=>$g->count())->sortDesc();
  $availability=\App\Models\HotlineAvailabilitySetting::current();
  return view('admin.ticketing.dashboard',compact('stats','monthlyData','categoryData','bidangData','availability'));
 }
 public function updateAvailability(Request $request){
  abort_unless(Auth::user()->role==='superadmin',403,'Hanya superadmin yang dapat mengubah status ketersediaan hotline.');
  $validated=$request->validate([
   'mode'=>'required|in:auto,manual_online,manual_offline',
   'opens_at'=>['nullable','date_format:H:i'],
   'closes_at'=>['nullable','date_format:H:i'],
   'workdays'=>'nullable|array',
   'workdays.*'=>'integer|between:0,6',
   'note'=>'nullable|string|max:255',
  ]);
  $setting=\App\Models\HotlineAvailabilitySetting::current();
  $setting->update([
   'mode'=>$validated['mode'],
   'opens_at'=>$validated['opens_at']?:null,
   'closes_at'=>$validated['closes_at']?:null,
   'workdays'=>(array)($validated['workdays']??\App\Models\HotlineAvailabilitySetting::DEFAULT_WORKDAYS),
   'note'=>$validated['note']?:null,
   'updated_by'=>Auth::id(),
  ]);
  return back()->with('success','Status ketersediaan Hotline berhasil diperbarui.');
 }
 public function index(Request $request){
  $q=$this->getScopeQuery()->with('assignee');
  if($request->filled('status')){
   if($request->status==='closed'){$q->where('status','CLOSED');}
   elseif($request->status==='open'){$q->where('status','!=','CLOSED');}
  }
  if($request->filled('service'))$q->where('service',$request->service);
  if($request->filled('category'))$q->where('category',$request->category);
  if($request->filled('bidang')&&Auth::user()->role==='superadmin')$q->where('bidang',$request->bidang);
  if($request->filled('assigned_to'))$q->where('assigned_to',$request->assigned_to);
  if($request->filled('search')){$s=$request->search;$q->where(function($q2)use($s){$q2->where('ticket_number','like',"%{$s}%")->orWhere('submitter_name','like',"%{$s}%")->orWhere('email','like',"%{$s}%")->orWhere('nip_nik','like',"%{$s}%");});}
  if($request->filled('date_from'))$q->whereDate('created_at','>=',$request->date_from);
  if($request->filled('date_to'))$q->whereDate('created_at','<=',$request->date_to);
  $tickets=$q->latest()->paginate(20)->withQueryString();
  $services=\App\Models\TicketService::where('is_active',true)->get();
  $categories=\App\Models\TicketCategory::where('is_active',true)->get();
  $bidangs=\App\Models\TicketBidang::where('is_active',true)->get();
  $pics=$this->getPics();
  return view('admin.ticketing.index',compact('tickets','services','categories','bidangs','pics'));
 }
public function show(Ticket $ticket){
   $this->authorizeTicketAccess($ticket);
   $ticket->load(['assignee','creator','messages.sender','statusHistories.changedBy']);
   $userMessages=$ticket->messages()->where('is_internal',false)->orderBy('created_at')->get();
   $internalMessages=Auth::user()->role==='superadmin'||$ticket->bidang===Auth::user()->bidang
    ?$ticket->messages()->where('is_internal',true)->orderBy('created_at')->get():collect();
   $bidangs=\App\Models\TicketBidang::where('is_active',true)->get();
   $pics=$this->getPics();
   $canManage=Auth::user()->role==='superadmin'||(Auth::user()->role==='admin_bidang'&&$ticket->bidang===Auth::user()->bidang);
   return view('admin.ticketing.show',compact('ticket','userMessages','internalMessages','bidangs','pics','canManage'));
  }
  public function messages(Ticket $ticket){
   $this->authorizeTicketAccess($ticket);
   $user=Auth::user();
   $userMessages=$ticket->messages()->where('is_internal',false)->orderBy('created_at')->get();
   $internalMessages=($user->role==='superadmin'||$ticket->bidang===$user->bidang)
    ?$ticket->messages()->where('is_internal',true)->orderBy('created_at')->get():collect();
   $allMessages=$userMessages->concat($internalMessages)->sortBy('created_at');
   return response()->json([
    'count'=>$allMessages->count(),
    'last_id'=>$allMessages->last()?->id,
    'html'=>view('admin.ticketing.partials.messages',['messages'=>$allMessages,'user'=>$user])->render(),
   ]);
  }
public function updateStatus(Request $request,Ticket $ticket){
   $this->authorizeTicketAccess($ticket);
   if($ticket->status==='CLOSED'){
    return back()->with('error','Tiket CLOSED bersifat final dan tidak dapat diubah.');
   }
   $validated=$request->validate(['status'=>'required|in:CLOSED','note'=>'nullable|string|max:500']);
   TicketingService::changeStatus($ticket,$validated['status'],Auth::user()->name,Auth::id(),$validated['note']??null);
   return back()->with('success','Status tiket berhasil diperbarui.');
  }
public function reply(Request $request,Ticket $ticket){
   $this->authorizeTicketAccess($ticket);
   if($ticket->status==='CLOSED'){
    return back()->with('error','Tiket CLOSED bersifat final dan tidak dapat direspons lagi.');
   }
   $validated=$request->validate([
    'message'=>'required|string|max:5000',
    'is_internal'=>'nullable|boolean',
   ]);
   TicketingService::respond($ticket,$validated['message'],Auth::user(),$validated['is_internal']??false);
   return back()->with('success','Respons berhasil dikirim.');
  }
 public function assign(Request $request,Ticket $ticket){
  $this->authorizeTicketAccess($ticket);
  $validated=$request->validate(['assigned_to'=>'required|exists:users,id']);
  TicketingService::assign($ticket,$validated['assigned_to'],Auth::user()->name,Auth::id());
  return back()->with('success','PIC berhasil ditugaskan.');
 }
 public function transfer(Request $request,Ticket $ticket){
  $this->authorizeTicketAccess($ticket);
  $validated=$request->validate(['bidang'=>'required|string','note'=>'nullable|string|max:500']);
  $newBidang=\App\Models\TicketBidang::where('name',$validated['bidang'])->firstOrFail();
  $newUserId=$newBidang->default_handler_user_id;
  TicketingService::transfer($ticket,$validated['bidang'],$newUserId,Auth::user()->name,Auth::id(),$validated['note']??null);
  return back()->with('success','Tiket berhasil dialihkan.');
 }
 public function exportExcel(Request $request){
  $q=$this->getScopeQuery();
  $this->applyFilters($q,$request);
  $tickets=$q->latest()->get();
  $spreadsheet=new \PhpOffice\PhpSpreadsheet\Spreadsheet();
  $sheet=$spreadsheet->getActiveSheet();
  $headers=['No','Nomor Tiket','Tanggal','Pengaju','Email','Layanan','Kategori','Status','Bidang','PIC','SLA'];
  foreach($headers as $i=>$h){$sheet->setCellValueByColumnAndRow($i+1,1,$h);}
  foreach($tickets as $i=>$t){
   $row=$i+2;
   $sheet->setCellValueByColumnAndRow(1,$row,$i+1);
   $sheet->setCellValueByColumnAndRow(2,$row,$t->ticket_number);
   $sheet->setCellValueByColumnAndRow(3,$row,$t->created_at->format('d/m/Y H:i'));
   $sheet->setCellValueByColumnAndRow(4,$row,$t->submitter_name);
   $sheet->setCellValueByColumnAndRow(5,$row,$t->email);
   $sheet->setCellValueByColumnAndRow(6,$row,$t->service);
   $sheet->setCellValueByColumnAndRow(7,$row,$t->category);
   $sheet->setCellValueByColumnAndRow(8,$row,$t->status==='CLOSED'?'Sudah Ditutup':'Dalam Proses');
   $sheet->setCellValueByColumnAndRow(9,$row,$t->bidang);
   $sheet->setCellValueByColumnAndRow(10,$row,$t->assignee?->name??'-');
   $sheet->setCellValueByColumnAndRow(11,$row,$t->sla_indicator);
  }
  $writer=new \PhpOffice\PhpSpreadsheet\Writer_Xlsx($spreadsheet);
  $path=storage_path('app/tickets_export.xlsx');
  $writer->save($path);
  return response()->download($path,'tickets_'.date('Y-m-d').'.xlsx')->deleteFileAfterSend(true);
 }
 public function exportPdf(Request $request){
  $q=$this->getScopeQuery();
  $this->applyFilters($q,$request);
  $tickets=$q->latest()->get();
  $pdf=\Barryvdh\DomPDF\Facade\Pdf::loadView('admin.ticketing.export-pdf',compact('tickets'));
  return $pdf->download('tickets_'.date('Y-m-d').'.pdf');
 }
 private function applyFilters($q,$request){
  if($request->filled('status')){
   if($request->status==='closed'){$q->where('status','CLOSED');}
   elseif($request->status==='open'){$q->where('status','!=','CLOSED');}
  }
  if($request->filled('service'))$q->where('service',$request->service);
  if($request->filled('category'))$q->where('category',$request->category);
  if($request->filled('date_from'))$q->whereDate('created_at','>=',$request->date_from);
  if($request->filled('date_to'))$q->whereDate('created_at','<=',$request->date_to);
 }
 private function getPics(){
  $user=Auth::user();
  $q=User::whereIn('role',['superadmin','admin_bidang']);
  if($user->role==='admin_bidang'){$q->where('bidang',$user->bidang);}
  return $q->get();
 }
}