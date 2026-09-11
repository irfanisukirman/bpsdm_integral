<?php
namespace App\Http\Controllers;
use App\Models\Agenda;use App\Models\AgendaSchedule;use App\Models\Asset;use App\Models\AssetBooking;use App\Models\Schedule;use Illuminate\Http\Request;use Illuminate\Pagination\LengthAwarePaginator;use Illuminate\Support\Facades\Auth;use Illuminate\Support\Facades\DB;use Illuminate\Support\Facades\Storage;use Illuminate\Validation\ValidationException;
class AgendaController extends Controller {
 private function guard():void{abort_unless(in_array(Auth::user()->role,['superadmin','admin_aset','admin_bidang']),403);}
 private function scope($q){$u=Auth::user();return $q->when($u->role==='admin_bidang',fn($x)=>$x->where('bidang',$u->bidang));}
 public function index(){
  $this->guard();
  $agendaEvents=$this->scope(Agenda::with(['creator','schedules'=>fn($q)=>$q->orderByDesc('starts_at'),'schedules.bookings.asset','schedules.assetLoanRequest']))
   ->get()->map(function($agenda){$schedule=$agenda->schedules->first();if(!$schedule)return null;
    return ['type'=>'agenda','id'=>$agenda->id,'starts_at'=>$schedule->starts_at,'ends_at'=>$schedule->ends_at,'title'=>$agenda->name,
     'subtitle'=>$agenda->agenda_type==='pimpinan'?'Agenda Pimpinan':'Agenda Bidang','bidang'=>$agenda->bidang,
     'location'=>$agenda->scope==='internal'?$schedule->bookings->pluck('asset.name')->filter()->join(', '):$schedule->external_place,
     'executor'=>$schedule->participants_info,'description'=>$agenda->description,'creator'=>$agenda->creator?->name,
     'approval_status'=>$agenda->scope==='internal'?($schedule->assetLoanRequest?->status??'pending'):'not_required',
     'approval_note'=>$schedule->assetLoanRequest?->review_note,'approval_purpose'=>$schedule->assetLoanRequest?->purpose,
     'edit_url'=>route('agendas.edit',$agenda),'delete_url'=>route('agendas.destroy',$agenda)];
   })->filter();
  $trainingEvents=Schedule::with(['training.creator','pengajar','bookings.asset','assetLoanRequest'])
   ->whereHas('training',fn($q)=>$q->when(Auth::user()->role==='admin_bidang',fn($x)=>$x->where('bidang',Auth::user()->bidang)))
   ->get()->map(function($schedule){$start=\Carbon\Carbon::parse($schedule->date.' '.$schedule->start_time);$end=\Carbon\Carbon::parse($schedule->date.' '.$schedule->end_time);
    return ['type'=>'training','id'=>$schedule->id,'starts_at'=>$start,'ends_at'=>$end,
     'title'=>$schedule->training->nama_pelatihan,'subtitle'=>$schedule->activity,'bidang'=>$schedule->training->bidang,
     'location'=>$schedule->venue_type==='internal'?$schedule->bookings->pluck('asset.name')->filter()->join(', '):($schedule->external_place?:$schedule->training->lokasi),
     'executor'=>$schedule->pengajar?->name?:$schedule->pic,'description'=>'Sesi pelatihan'.($schedule->jp?' · '.$schedule->jp.' JP':''),
     'creator'=>$schedule->training->creator?->name,'manage_url'=>route('trainings.schedules',$schedule->training_id),
     'approval_status'=>$schedule->venue_type==='internal'?($schedule->assetLoanRequest?->status??'not_submitted'):'not_required',
     'approval_note'=>$schedule->assetLoanRequest?->review_note,'approval_purpose'=>$schedule->assetLoanRequest?->purpose];
   });
  $allEvents=$agendaEvents->concat($trainingEvents)->sortByDesc('starts_at')->values();$page=LengthAwarePaginator::resolveCurrentPage();$perPage=20;
  $events=new LengthAwarePaginator($allEvents->forPage($page,$perPage)->values(),$allEvents->count(),$perPage,$page,['path'=>request()->url(),'query'=>request()->query()]);
  $publicDailyScheduleUrl=\App\Support\PublicScheduleAccess::url();return view('agendas.index',compact('events','publicDailyScheduleUrl'));
 }
 public function create(){ $this->guard();$assets=Asset::where('is_active',true)->where('type','ruangan')->orderBy('name')->get();return view('agendas.create',compact('assets')); }
 public function availability(Request $r){
  $this->guard();
  $d=$r->validate(['start_date'=>'required|date','end_date'=>'required|date|after_or_equal:start_date','start_time'=>'required|date_format:H:i','end_time'=>'required|date_format:H:i','agenda_id'=>'nullable|integer|exists:agendas,id']);
  $start=$d['start_date'].' '.$d['start_time'].':00';$end=$d['end_date'].' '.$d['end_time'].':00';
  if($end<=$start)return response()->json(['message'=>'Tanggal dan jam selesai harus setelah waktu mulai.','assets'=>[]],422);
  $ignoreScheduleId=null;
  if(!empty($d['agenda_id'])){$agenda=Agenda::findOrFail($d['agenda_id']);$this->authorizeAgenda($agenda);$ignoreScheduleId=$agenda->schedules()->value('id');}
  $assets=Asset::where('is_active',true)->where('type','ruangan')->orderBy('name')->get()->map(function($asset)use($start,$end,$ignoreScheduleId){
   $conflict=AssetBooking::with('bookable')->where('asset_id',$asset->id)->where('starts_at','<',$end)->where('ends_at','>',$start)
    ->when($ignoreScheduleId,fn($q)=>$q->where(fn($x)=>$x->where('bookable_type','!=',AgendaSchedule::class)->orWhere('bookable_id','!=',$ignoreScheduleId)))->orderBy('starts_at')->first();
   $activity=null;if($conflict){$activity=class_basename($conflict->bookable_type)==='Schedule'?($conflict->bookable->activity??'Pelatihan'):($conflict->bookable->agenda->name??'Agenda');}
   return ['id'=>$asset->id,'available'=>!$conflict,'message'=>$conflict?'Ruangan '.$asset->name.' sudah digunakan untuk '.$activity.' pukul '.$conflict->starts_at->format('H:i').'–'.$conflict->ends_at->format('H:i').'.':null];
  });
  return response()->json(['assets'=>$assets]);
 }
 public function store(Request $r){
  $this->guard();
  $d=$r->validate(['scope'=>'required|in:internal,external','agenda_type'=>'required|in:bidang,pimpinan','name'=>'required|string|max:255','start_date'=>'required|date','end_date'=>'required|date|after_or_equal:start_date','start_time'=>'required|date_format:H:i','end_time'=>'required|date_format:H:i','asset_id'=>'nullable|required_if:scope,internal|integer|exists:assets,id','external_place'=>'nullable|required_if:scope,external|string|max:255','description'=>'nullable|string','executor'=>'nullable|string|max:1000','is_public'=>'nullable|boolean','loan_letter'=>'nullable|required_if:scope,internal|file|mimes:pdf|max:5120','loan_purpose'=>'nullable|string|max:2000','loan_contact'=>'nullable|string|max:255','attendee_count'=>'nullable|integer|min:1']);
  $start=$d['start_date'].' '.$d['start_time'].':00';$end=$d['end_date'].' '.$d['end_time'].':00';
  if($end<=$start)throw ValidationException::withMessages(['end_time'=>'Tanggal dan jam selesai harus setelah waktu mulai.']);
  if($d['scope']==='internal'&&AssetBooking::hasConflict((int)$d['asset_id'],$start,$end))throw ValidationException::withMessages(['asset_id'=>'Ruangan '.Asset::find($d['asset_id'])?->name.' sudah digunakan pada waktu tersebut.']);
  DB::transaction(function()use($d,$start,$end,$r){$u=Auth::user();$agenda=Agenda::create(['scope'=>$d['scope'],'agenda_type'=>$d['agenda_type'],'name'=>$d['name'],'description'=>$d['description']??null,'bidang'=>$u->role==='admin_bidang'?$u->bidang:($u->bidang?:'Pengelola Aset'),'is_public'=>(bool)($d['is_public']??false),'created_by'=>$u->id]);
   $schedule=$agenda->schedules()->create(['title'=>$d['name'],'starts_at'=>$start,'ends_at'=>$end,'external_place'=>$d['scope']==='external'?($d['external_place']??null):null,'participants_info'=>$d['executor']??null,'notes'=>$d['description']??null]);
   if($d['scope']==='internal')$schedule->assetLoanRequest()->create(['asset_ids'=>[(int)$d['asset_id']],'letter_path'=>$r->file('loan_letter')->store('asset-loan-letters'),'purpose'=>$d['loan_purpose']??$d['description']??null,'contact_person'=>$d['loan_contact']??$u->name,'attendee_count'=>$d['attendee_count']??null,'status'=>'pending','submitted_by'=>$u->id]);
  });return redirect()->route('agendas.index')->with('success',$d['scope']==='internal'?'Agenda disimpan dan peminjaman aset dikirim untuk persetujuan.':'Agenda berhasil disimpan.');
 }
 public function edit(Agenda $agenda){$this->guard();$this->authorizeAgenda($agenda);$agenda->load(['schedules.bookings','schedules.assetLoanRequest']);$assets=Asset::where('is_active',true)->where('type','ruangan')->orderBy('name')->get();return view('agendas.create',compact('agenda','assets'));}
 public function update(Request $r,Agenda $agenda){
  $this->guard();$this->authorizeAgenda($agenda);
  $d=$r->validate(['scope'=>'required|in:internal,external','agenda_type'=>'required|in:bidang,pimpinan','name'=>'required|string|max:255','start_date'=>'required|date','end_date'=>'required|date|after_or_equal:start_date','start_time'=>'required|date_format:H:i','end_time'=>'required|date_format:H:i','asset_id'=>'nullable|required_if:scope,internal|integer|exists:assets,id','external_place'=>'nullable|required_if:scope,external|string|max:255','description'=>'nullable|string','executor'=>'nullable|string|max:1000','is_public'=>'nullable|boolean','loan_letter'=>'nullable|file|mimes:pdf|max:5120','loan_purpose'=>'nullable|string|max:2000','loan_contact'=>'nullable|string|max:255','attendee_count'=>'nullable|integer|min:1']);
  $start=$d['start_date'].' '.$d['start_time'].':00';$end=$d['end_date'].' '.$d['end_time'].':00';if($end<=$start)throw ValidationException::withMessages(['end_time'=>'Tanggal dan jam selesai harus setelah waktu mulai.']);$schedule=$agenda->schedules()->firstOrFail();
  if($d['scope']==='internal'&&AssetBooking::hasConflict((int)$d['asset_id'],$start,$end,AgendaSchedule::class,$schedule->id))throw ValidationException::withMessages(['asset_id'=>'Ruangan '.Asset::find($d['asset_id'])?->name.' sudah digunakan pada waktu tersebut.']);
  if($d['scope']==='internal'&&!$schedule->assetLoanRequest&&!$r->hasFile('loan_letter'))throw ValidationException::withMessages(['loan_letter'=>'Surat peminjaman PDF wajib diunggah.']);
  DB::transaction(function()use($agenda,$schedule,$d,$start,$end,$r){$agenda->update(['scope'=>$d['scope'],'agenda_type'=>$d['agenda_type'],'name'=>$d['name'],'description'=>$d['description']??null,'is_public'=>(bool)($d['is_public']??false)]);$schedule->update(['title'=>$d['name'],'starts_at'=>$start,'ends_at'=>$end,'external_place'=>$d['scope']==='external'?($d['external_place']??null):null,'participants_info'=>$d['executor']??null,'notes'=>$d['description']??null]);$schedule->bookings()->delete();
   if($d['scope']==='internal'){$loan=$schedule->assetLoanRequest;$path=$loan?->letter_path;if($r->hasFile('loan_letter')){if($path)Storage::disk('local')->delete($path);$path=$r->file('loan_letter')->store('asset-loan-letters');}$schedule->assetLoanRequest()->updateOrCreate([],['asset_ids'=>[(int)$d['asset_id']],'letter_path'=>$path,'purpose'=>$d['loan_purpose']??$d['description']??null,'contact_person'=>$d['loan_contact']??Auth::user()->name,'attendee_count'=>$d['attendee_count']??null,'status'=>'pending','review_note'=>null,'submitted_by'=>Auth::id(),'reviewed_by'=>null,'reviewed_at'=>null]);}
   else{if($schedule->assetLoanRequest?->letter_path)Storage::disk('local')->delete($schedule->assetLoanRequest->letter_path);$schedule->assetLoanRequest()->delete();}
  });
  return redirect()->route('agendas.index')->with('success','Agenda berhasil diperbarui.');
 }
 public function destroy(Agenda $agenda){$this->guard();$this->authorizeAgenda($agenda);$agenda->load('schedules.assetLoanRequest');DB::transaction(function()use($agenda){foreach($agenda->schedules as $s){$s->bookings()->delete();if($s->assetLoanRequest?->letter_path)Storage::disk('local')->delete($s->assetLoanRequest->letter_path);$s->assetLoanRequest()->delete();}$agenda->delete();});return back()->with('success','Agenda dihapus dan aset dilepaskan.');}
 private function authorizeAgenda(Agenda $agenda):void{$u=Auth::user();abort_unless($u->role!=='admin_bidang'||$agenda->bidang===$u->bidang,403);}
}
