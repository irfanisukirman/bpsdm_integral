<?php
namespace App\Http\Controllers;
use App\Models\Agenda;use App\Models\AgendaSchedule;use App\Models\Asset;use App\Models\AssetBooking;use Illuminate\Http\Request;use Illuminate\Support\Facades\Auth;use Illuminate\Support\Facades\DB;use Illuminate\Validation\ValidationException;
class AgendaController extends Controller {
 private function guard():void{abort_unless(in_array(Auth::user()->role,['superadmin','admin_aset','admin_bidang']),403);}
 private function scope($q){$u=Auth::user();return $q->when($u->role==='admin_bidang',fn($x)=>$x->where('bidang',$u->bidang));}
 public function index(){
  $this->guard();
  $agendas=$this->scope(Agenda::with(['creator','schedules'=>fn($q)=>$q->orderByDesc('starts_at'),'schedules.bookings.asset']))
   ->orderByDesc(AgendaSchedule::select('starts_at')->whereColumn('agenda_schedules.agenda_id','agendas.id')->latest('starts_at')->limit(1))
   ->paginate(20);
  return view('agendas.index',compact('agendas'));
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
  $d=$r->validate(['scope'=>'required|in:internal,external','agenda_type'=>'required|in:bidang,pimpinan','name'=>'required|string|max:255','start_date'=>'required|date','end_date'=>'required|date|after_or_equal:start_date','start_time'=>'required|date_format:H:i','end_time'=>'required|date_format:H:i','asset_id'=>'nullable|required_if:scope,internal|integer|exists:assets,id','external_place'=>'nullable|required_if:scope,external|string|max:255','description'=>'nullable|string','executor'=>'nullable|string|max:1000','is_public'=>'nullable|boolean']);
  $start=$d['start_date'].' '.$d['start_time'].':00';$end=$d['end_date'].' '.$d['end_time'].':00';
  if($end<=$start)throw ValidationException::withMessages(['end_time'=>'Tanggal dan jam selesai harus setelah waktu mulai.']);
  if($d['scope']==='internal'&&AssetBooking::hasConflict((int)$d['asset_id'],$start,$end))throw ValidationException::withMessages(['asset_id'=>'Ruangan '.Asset::find($d['asset_id'])?->name.' sudah digunakan pada waktu tersebut.']);
  DB::transaction(function()use($d,$start,$end){$u=Auth::user();$agenda=Agenda::create(['scope'=>$d['scope'],'agenda_type'=>$d['agenda_type'],'name'=>$d['name'],'description'=>$d['description']??null,'bidang'=>$u->role==='admin_bidang'?$u->bidang:($u->bidang?:'Pengelola Aset'),'is_public'=>(bool)($d['is_public']??false),'created_by'=>$u->id]);
   $schedule=$agenda->schedules()->create(['title'=>$d['name'],'starts_at'=>$start,'ends_at'=>$end,'external_place'=>$d['scope']==='external'?($d['external_place']??null):null,'participants_info'=>$d['executor']??null,'notes'=>$d['description']??null]);
   if($d['scope']==='internal')$schedule->bookings()->create(['asset_id'=>$d['asset_id'],'starts_at'=>$start,'ends_at'=>$end,'created_by'=>$u->id]);
  });return redirect()->route('agendas.index')->with('success','Agenda berhasil disimpan.');
 }
 public function edit(Agenda $agenda){$this->guard();$this->authorizeAgenda($agenda);$agenda->load(['schedules.bookings']);$assets=Asset::where('is_active',true)->where('type','ruangan')->orderBy('name')->get();return view('agendas.create',compact('agenda','assets'));}
 public function update(Request $r,Agenda $agenda){
  $this->guard();$this->authorizeAgenda($agenda);
  $d=$r->validate(['scope'=>'required|in:internal,external','agenda_type'=>'required|in:bidang,pimpinan','name'=>'required|string|max:255','start_date'=>'required|date','end_date'=>'required|date|after_or_equal:start_date','start_time'=>'required|date_format:H:i','end_time'=>'required|date_format:H:i','asset_id'=>'nullable|required_if:scope,internal|integer|exists:assets,id','external_place'=>'nullable|required_if:scope,external|string|max:255','description'=>'nullable|string','executor'=>'nullable|string|max:1000','is_public'=>'nullable|boolean']);
  $start=$d['start_date'].' '.$d['start_time'].':00';$end=$d['end_date'].' '.$d['end_time'].':00';if($end<=$start)throw ValidationException::withMessages(['end_time'=>'Tanggal dan jam selesai harus setelah waktu mulai.']);$schedule=$agenda->schedules()->firstOrFail();
  if($d['scope']==='internal'&&AssetBooking::hasConflict((int)$d['asset_id'],$start,$end,AgendaSchedule::class,$schedule->id))throw ValidationException::withMessages(['asset_id'=>'Ruangan '.Asset::find($d['asset_id'])?->name.' sudah digunakan pada waktu tersebut.']);
  DB::transaction(function()use($agenda,$schedule,$d,$start,$end){$agenda->update(['scope'=>$d['scope'],'agenda_type'=>$d['agenda_type'],'name'=>$d['name'],'description'=>$d['description']??null,'is_public'=>(bool)($d['is_public']??false)]);$schedule->update(['title'=>$d['name'],'starts_at'=>$start,'ends_at'=>$end,'external_place'=>$d['scope']==='external'?($d['external_place']??null):null,'participants_info'=>$d['executor']??null,'notes'=>$d['description']??null]);$schedule->bookings()->delete();if($d['scope']==='internal')$schedule->bookings()->create(['asset_id'=>$d['asset_id'],'starts_at'=>$start,'ends_at'=>$end,'created_by'=>Auth::id()]);});
  return redirect()->route('agendas.index')->with('success','Agenda berhasil diperbarui.');
 }
 public function destroy(Agenda $agenda){$this->guard();$this->authorizeAgenda($agenda);DB::transaction(function()use($agenda){foreach($agenda->schedules as $s)$s->bookings()->delete();$agenda->delete();});return back()->with('success','Agenda dihapus dan aset dilepaskan.');}
 private function authorizeAgenda(Agenda $agenda):void{$u=Auth::user();abort_unless($u->role!=='admin_bidang'||$agenda->bidang===$u->bidang,403);}
}
