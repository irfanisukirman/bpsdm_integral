<?php
namespace App\Http\Controllers;
use App\Models\Asset;use App\Models\AssetBooking;use App\Models\AssetPublicReservation;use App\Models\Schedule;use App\Models\AgendaSchedule;use App\Models\NotificationRead;use Illuminate\Http\Request;use Illuminate\Support\Facades\Auth;use Illuminate\Support\Facades\Storage;use Illuminate\Validation\ValidationException;
class AssetController extends Controller {
 private function guard():void{abort_unless(in_array(Auth::user()->role,['superadmin','admin_aset']),403);}
 public function dashboard(Request $request)
 {
  $this->guard();
  $date=$request->date('date')?->toDateString()??now()->toDateString();
  $dayStart=\Carbon\Carbon::parse($date)->startOfDay();$dayEnd=$dayStart->copy()->endOfDay();
  $assets=Asset::orderBy('name')->get();
  $bookings=AssetBooking::with(['asset','bookable'])
   ->where('starts_at','<=',$dayEnd)->where('ends_at','>=',$dayStart)->orderBy('starts_at')->get();
  $bookings->loadMorph('bookable',[Schedule::class=>['training','pengajar'],AgendaSchedule::class=>['agenda.creator'],AssetPublicReservation::class=>[]]);
  $now=now();$isToday=$date===$now->toDateString();
  $usage=$bookings->map(function($booking)use($dayStart,$dayEnd,$now,$isToday){
   $isTraining=$booking->bookable instanceof Schedule;$isPublic=$booking->bookable instanceof AssetPublicReservation;$source=$booking->bookable;
   $start=$booking->starts_at->greaterThan($dayStart)?$booking->starts_at:$dayStart;
   $end=$booking->ends_at->lessThan($dayEnd)?$booking->ends_at:$dayEnd;
   $status=$isToday?($now->lt($booking->starts_at)?'Akan Datang':($now->gt($booking->ends_at)?'Selesai':'Sedang Dipakai')):'Terjadwal';
   return [
    'asset'=>$booking->asset,'type'=>$isTraining?'Pelatihan':($isPublic?'Reservasi Publik':'Agenda'),
    'activity'=>$isTraining?($source?->activity??'Sesi Pelatihan'):($isPublic?(($source?->usage_type==='Lainnya'?$source?->usage_other:$source?->usage_type)?:'Sewa fasilitas'):($source?->title??$source?->agenda?->name??'Agenda')),
    'parent'=>$isTraining?($source?->training?->nama_pelatihan??'-'):($isPublic?($source?->organization?:$source?->full_name):($source?->agenda?->name??'-')),
    'bidang'=>$isTraining?($source?->training?->bidang??'-'):($isPublic?'Umum / Publik':($source?->agenda?->bidang??'-')),
    'pic'=>$isTraining?($source?->pengajar?->name??$source?->pic??'-'):($isPublic?($source?->full_name??'-'):($source?->agenda?->creator?->name??'-')),
    'start'=>$start,'end'=>$end,'status'=>$status,'duration'=>round($start->diffInMinutes($end)/60,1),
   ];
  })->sortBy('start')->values();
  $usedAssetIds=$usage->pluck('asset.id')->filter()->unique();
  $activeAssets=$assets->where('is_active',true);
  $stats=['total'=>$assets->count(),'active'=>$activeAssets->count(),'used'=>$usedAssetIds->count(),
   'available'=>$activeAssets->whereNotIn('id',$usedAssetIds)->count(),'bookings'=>$usage->count(),
   'hours'=>round($usage->sum('duration'),1)];
  $byField=$usage->groupBy('bidang')->map->count()->sortDesc();
  $byType=$usage->groupBy(fn($item)=>ucfirst($item['asset']?->type??'lainnya'))->map->count()->sortDesc();
  $forecast=collect(range(0,6))->map(function($offset)use($dayStart){
   $day=$dayStart->copy()->addDays($offset);return [
    'date'=>$day->toDateString(),'label'=>$day->translatedFormat('D, d M'),
    'count'=>AssetBooking::where('starts_at','<=',$day->copy()->endOfDay())->where('ends_at','>=',$day->copy()->startOfDay())->count(),
    'assets'=>AssetBooking::where('starts_at','<=',$day->copy()->endOfDay())->where('ends_at','>=',$day->copy()->startOfDay())->distinct()->count('asset_id'),
   ];
  });
  return view('assets.dashboard',compact('date','usage','stats','byField','byType','forecast','isToday'));
 }
 public function index(){ $this->guard();$assets=Asset::with(['images','rentalRates'])->latest()->paginate(9);return view('assets.index',compact('assets')); }
 public function store(Request $r){
  $this->guard();
  $d=$r->validate(['name'=>'required|max:255','type'=>'required|in:ruangan,kendaraan,peralatan,lainnya','facilities'=>'nullable','location'=>'required|max:255','capacity'=>'nullable|integer|min:1','description'=>'nullable','is_rentable'=>'nullable|boolean','hourly_rate'=>'nullable|numeric|min:0','rental_open_time'=>'nullable|date_format:H:i','rental_close_time'=>'nullable|date_format:H:i|after:rental_open_time','rental_rates'=>'nullable|array|max:20','rental_rates.*.start_time'=>'required_if:is_rentable,1|date_format:H:i','rental_rates.*.end_time'=>'required_if:is_rentable,1|date_format:H:i','rental_rates.*.hourly_rate'=>'required_if:is_rentable,1|numeric|min:0|max:999999999','rental_min_hours'=>'nullable|integer|min:1|max:24','rental_max_hours'=>'nullable|integer|min:1|max:24|gte:rental_min_hours','images'=>'nullable|array|max:10','images.*'=>'image|max:5120']);
  unset($d['images'],$d['rental_rates']);
  $d['created_by']=Auth::id();
  $d['is_public']=$r->boolean('is_public');
  $d['is_rentable']=$r->boolean('is_rentable');
  $rates=$d['is_rentable']?$this->normalizeRentalRates($r):collect();
  if($d['is_rentable']){$d['hourly_rate']=$rates->min('hourly_rate');$d['rental_open_time']=$rates->first()['start_time'];$d['rental_close_time']=$rates->last()['end_time'];}else{$d['hourly_rate']=null;$d['rental_open_time']=null;$d['rental_close_time']=null;}
  $a=Asset::create($d);
  $a->rentalRates()->createMany($rates->all());
  foreach($r->file('images',[]) as $i=>$f)$a->images()->create(['path'=>$f->store('assets','public'),'sort_order'=>$i]);
  return back()->with('success','Aset berhasil ditambahkan.');
 }
 public function update(Request $r, Asset $asset){
  $this->guard();
  $d=$r->validate(['name'=>'required|max:255','type'=>'required|in:ruangan,kendaraan,peralatan,lainnya','facilities'=>'nullable','location'=>'required|max:255','capacity'=>'nullable|integer|min:1','description'=>'nullable','is_rentable'=>'nullable|boolean','hourly_rate'=>'nullable|numeric|min:0','rental_open_time'=>'nullable|date_format:H:i','rental_close_time'=>'nullable|date_format:H:i|after:rental_open_time','rental_rates'=>'nullable|array|max:20','rental_rates.*.start_time'=>'required_if:is_rentable,1|date_format:H:i','rental_rates.*.end_time'=>'required_if:is_rentable,1|date_format:H:i','rental_rates.*.hourly_rate'=>'required_if:is_rentable,1|numeric|min:0|max:999999999','rental_min_hours'=>'nullable|integer|min:1|max:24','rental_max_hours'=>'nullable|integer|min:1|max:24|gte:rental_min_hours','images'=>'nullable|array|max:10','images.*'=>'image|max:5120']);
  unset($d['images'],$d['rental_rates']);
  $d['is_public']=$r->boolean('is_public');
  $d['is_rentable']=$r->boolean('is_rentable');
  $rates=$d['is_rentable']?$this->normalizeRentalRates($r):collect();
  if($d['is_rentable']){$d['hourly_rate']=$rates->min('hourly_rate');$d['rental_open_time']=$rates->first()['start_time'];$d['rental_close_time']=$rates->last()['end_time'];}else{$d['hourly_rate']=null;$d['rental_open_time']=null;$d['rental_close_time']=null;}
  $asset->update($d);
  $asset->rentalRates()->delete();
  $asset->rentalRates()->createMany($rates->all());
  $nextSort=(int)$asset->images()->max('sort_order')+1;
  foreach($r->file('images',[]) as $i=>$f)$asset->images()->create(['path'=>$f->store('assets','public'),'sort_order'=>$nextSort+$i]);
  return redirect()->route('assets.index',['page'=>$r->integer('page',1)])->with('success','Aset berhasil diperbarui.');
 }
 private function normalizeRentalRates(Request $request):\Illuminate\Support\Collection
 {
  $rates=collect($request->input('rental_rates',[]))->map(function($rate,$index){
   $start=substr((string)($rate['start_time']??''),0,5);$end=substr((string)($rate['end_time']??''),0,5);
   if($start===''||$end===''||$end<=$start)throw ValidationException::withMessages(["rental_rates.$index.end_time"=>'Jam selesai tarif harus setelah jam mulai.']);
   return ['start_time'=>$start,'end_time'=>$end,'hourly_rate'=>(float)($rate['hourly_rate']??0)];
  })->sortBy('start_time')->values();
  if($rates->isEmpty())throw ValidationException::withMessages(['rental_rates'=>'Tambahkan minimal satu rentang tarif untuk aset yang dapat disewa.']);
  foreach($rates as $index=>$rate){if($index>0&&$rate['start_time']<$rates[$index-1]['end_time'])throw ValidationException::withMessages(["rental_rates.$index.start_time"=>'Rentang tarif tidak boleh saling tumpang tindih.']);}
  return $rates->map(fn($rate,$index)=>array_merge($rate,['sort_order'=>$index]));
 }
 public function destroy(Asset $asset){$this->guard();abort_if($asset->bookings()->where('ends_at','>=',now())->exists(),422,'Aset masih memiliki jadwal pemakaian.');foreach($asset->images as $i)Storage::disk('public')->delete($i->path);$asset->delete();return back()->with('success','Aset dihapus.');}
 public function monitoring(Request $r){$this->guard();$date=$r->date('date')?->toDateString()??now()->toDateString();NotificationRead::updateOrCreate(['user_id'=>Auth::id(),'notification_key'=>'asset-usage-upcoming-'.Auth::id().'-'.$date],['read_at'=>now()]);$assets=Asset::with(['bookings'=>fn($q)=>$q->whereDate('starts_at','<=',$date)->whereDate('ends_at','>=',$date)->orderBy('starts_at'),'bookings.bookable'])->orderBy('name')->get();return view('assets.monitoring',compact('assets','date'));}

 public function dailySchedule(Request $request)
 {
  abort_unless(in_array(Auth::user()->role,['superadmin','admin_bidang','admin_aset'],true),403);
  $date=$request->date('date')?->toDateString()??now()->toDateString();
  $isAssetAdmin=Auth::user()->role==='admin_aset';$assetCatalog=Asset::orderBy('name')->get()->keyBy('id');
  $trainingSchedules=Schedule::with(['training.participants','pengajar','bookings.asset','assetLoanRequest'])
   ->whereDate('date',$date)->orderBy('start_time')->get()->map(function($schedule)use($isAssetAdmin,$assetCatalog){
   $loan=$schedule->assetLoanRequest;$requested=$loan?$assetCatalog->only($loan->asset_ids??[])->values():collect();
    $isBreak=($schedule->schedule_type??'learning')==='break';
    return [
     'type'=>$isBreak?'Istirahat':'Pelatihan','is_break'=>$isBreak,'title'=>$schedule->activity,'parent'=>$schedule->training?->nama_pelatihan??'Pelatihan',
     'bidang'=>$schedule->training?->bidang??'-','start'=>substr($schedule->start_time,0,5),
     'end'=>substr($schedule->end_time,0,5),'pic'=>$schedule->pengajar?->name??$schedule->pic??'-',
     'participants'=>$isBreak?'-':($schedule->training?->participants?->count()??0),'assets'=>$schedule->bookings->pluck('asset')->filter()->values(),
     'requested_assets'=>$requested,'loan_status'=>$loan?->status,
     'place'=>$schedule->venue_type==='external'?($schedule->external_place?:'Lokasi eksternal'):'',
     'zoom'=>$schedule->link_zoom,'manage_url'=>$isAssetAdmin?($loan?route('asset-loans.index',['status'=>$loan->status]).'#loan-'.$loan->id:null):route('trainings.schedules',$schedule->training_id),
     'manage_label'=>$isAssetAdmin?'Buka persetujuan':'Buka pengelolaan',
    ];
   });
  $agendaSchedules=AgendaSchedule::with(['agenda.creator','bookings.asset','assetLoanRequest'])
   ->whereDate('starts_at','<=',$date)->whereDate('ends_at','>=',$date)->orderBy('starts_at')->get()->map(function($schedule) use($date,$isAssetAdmin,$assetCatalog){
    $start=$schedule->starts_at->toDateString()===$date?$schedule->starts_at->format('H:i'):'00:00';
    $end=$schedule->ends_at->toDateString()===$date?$schedule->ends_at->format('H:i'):'24:00';
    $loan=$schedule->assetLoanRequest;$requested=$loan?$assetCatalog->only($loan->asset_ids??[])->values():collect();
    return [
     'type'=>'Agenda','title'=>$schedule->title?:$schedule->agenda?->name?:'Agenda',
     'is_break'=>false,
     'parent'=>$schedule->agenda?->name??'Agenda Kegiatan','bidang'=>$schedule->agenda?->organizer_unit??$schedule->agenda?->creator?->bidang??'-',
     'start'=>$start,'end'=>$end,'pic'=>$schedule->agenda?->creator?->name??'-','participants'=>$schedule->participants_info?:'-',
     'assets'=>$schedule->bookings->pluck('asset')->filter()->values(),'requested_assets'=>$requested,'loan_status'=>$loan?->status,'place'=>$schedule->external_place?:'',
     'zoom'=>$schedule->zoom_link,'manage_url'=>$isAssetAdmin?($loan?route('asset-loans.index',['status'=>$loan->status]).'#loan-'.$loan->id:null):route('agendas.edit',$schedule->agenda_id),
     'manage_label'=>$isAssetAdmin?'Buka persetujuan':'Buka pengelolaan',
    ];
   });
  $publicSchedules=AssetPublicReservation::with('asset')->whereDate('rental_date',$date)->whereIn('status', ['confirmed','completed'])->orderBy('start_time')->get()->map(function($reservation)use($isAssetAdmin){ return ['type'=>'Reservasi Publik','is_break'=>false,'title'=>(($reservation->usage_type==='Lainnya'?$reservation->usage_other:$reservation->usage_type)?:'Sewa fasilitas'),'parent'=>($reservation->organization?:$reservation->full_name),'bidang'=>'Umum / Publik','start'=>substr($reservation->start_time,0,5),'end'=>substr($reservation->end_time,0,5),'pic'=>$reservation->full_name,'participants'=>'Penyewa umum','assets'=>collect([$reservation->asset])->filter()->values(),'requested_assets'=>collect(),'loan_status'=>$reservation->status,'place'=>($reservation->asset?->location?:''),'zoom'=>null,'manage_url'=>$isAssetAdmin?route('asset-rentals.admin.index', ['date'=>$reservation->rental_date->toDateString()]):null,'manage_label'=>'Buka reservasi publik']; });
  $schedules=$trainingSchedules->concat($agendaSchedules)->concat($publicSchedules)->sortBy('start')->values();
  $assetIds=$schedules->flatMap(fn($item)=>$item['assets']->pluck('id'))->unique();
  $stats=['total'=>$schedules->count(),'trainings'=>$trainingSchedules->count(),'agendas'=>$agendaSchedules->count(),
   'public'=>$publicSchedules->count(),'assets'=>$assetIds->count(),'unassigned'=>$schedules->filter(fn($item)=>!$item['is_break']&&$item['assets']->isEmpty()&&$item['requested_assets']->isEmpty()&&blank($item['place'])&&blank($item['zoom']))->count()];
  $now=now();$isToday=$date===$now->toDateString();$currentTime=$now->format('H:i');
  return view('monitoring.daily_schedule',compact('date','schedules','stats','isToday','currentTime'));
 }
}
