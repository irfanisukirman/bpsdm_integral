<?php
namespace App\Http\Controllers;
use App\Models\Asset;
use App\Models\AssetBooking;
use App\Models\AssetLoanRequest;
use App\Models\Schedule;
use App\Models\AgendaSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
class AssetLoanRequestController extends Controller {
 private function assetAdmin():void{abort_unless(in_array(Auth::user()->role,['superadmin','admin_aset'],true),403);}
 public function index(Request $request){
  $this->assetAdmin();$status=$request->input('status','pending');
  $query=AssetLoanRequest::with(['requestable','submitter','reviewer'])->latest();
  if($status!=='all')$query->where('status',$status);$requests=$query->paginate(15)->withQueryString();
  $requests->getCollection()->loadMorph('requestable',[Schedule::class=>['training'],AgendaSchedule::class=>['agenda']]);
  $counts=AssetLoanRequest::selectRaw('status, count(*) total')->groupBy('status')->pluck('total','status');
  $assets=Asset::orderBy('name')->get()->keyBy('id');
  return view('assets.loan_requests',compact('requests','counts','assets','status'));
 }
 public function document(AssetLoanRequest $loan){
  abort_unless(in_array(Auth::user()->role,['superadmin','admin_aset','admin_bidang'],true),403);
  if(Auth::user()->role==='admin_bidang'){
   $loan->load('requestable');$source=$loan->requestable;
   $field=$source instanceof Schedule?$source->training?->bidang:$source?->agenda?->bidang;
   abort_unless($field===Auth::user()->bidang,403);
  }
  abort_unless(Storage::disk('local')->exists($loan->letter_path),404);
  return Storage::disk('local')->response($loan->letter_path,null,['Content-Type'=>'application/pdf']);
 }
 public function review(Request $request,AssetLoanRequest $loan){
  $this->assetAdmin();$data=$request->validate(['decision'=>'required|in:approved,revision,rejected','review_note'=>'nullable|required_unless:decision,approved|string|max:2000']);
  $loan->load('requestable');$source=$loan->requestable;abort_if(!$source,422,'Jadwal sumber sudah tidak tersedia.');
  $start=$source instanceof Schedule?$source->date.' '.$source->start_time:$source->starts_at;
  $end=$source instanceof Schedule?$source->date.' '.$source->end_time:$source->ends_at;
  if($data['decision']==='approved'){
   foreach($loan->asset_ids as $assetId)if(AssetBooking::hasConflict((int)$assetId,$start,$end,get_class($source),$source->id))
    throw ValidationException::withMessages(['decision'=>'Aset '.Asset::find($assetId)?->name.' sudah digunakan pada waktu tersebut.']);
  }
  DB::transaction(function()use($loan,$source,$start,$end,$data){
   $source->bookings()->delete();
   if($data['decision']==='approved')foreach($loan->asset_ids as $assetId)$source->bookings()->create(['asset_id'=>$assetId,'starts_at'=>$start,'ends_at'=>$end,'created_by'=>$loan->submitted_by]);
   $loan->update(['status'=>$data['decision'],'review_note'=>$data['review_note']??null,'reviewed_by'=>Auth::id(),'reviewed_at'=>now()]);
  });
  return back()->with('success','Pengajuan telah '.match($data['decision']){'approved'=>'disetujui','revision'=>'dikembalikan untuk perbaikan',default=>'ditolak'}.'.');
 }
}
