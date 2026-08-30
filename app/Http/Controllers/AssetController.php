<?php
namespace App\Http\Controllers;
use App\Models\Asset;use App\Models\AssetBooking;use Illuminate\Http\Request;use Illuminate\Support\Facades\Auth;use Illuminate\Support\Facades\Storage;
class AssetController extends Controller {
 private function guard():void{abort_unless(in_array(Auth::user()->role,['superadmin','admin_aset']),403);}
 public function dashboard(){ $this->guard();$today=now()->toDateString();$stats=['assets'=>Asset::count(),'rooms'=>Asset::where('type','ruangan')->count(),'used'=>AssetBooking::whereDate('starts_at','<=',$today)->whereDate('ends_at','>=',$today)->distinct('asset_id')->count('asset_id')];$upcoming=AssetBooking::with('asset')->where('ends_at','>=',now())->orderBy('starts_at')->limit(8)->get();return view('assets.dashboard',compact('stats','upcoming')); }
 public function index(){ $this->guard();$assets=Asset::with('images')->latest()->paginate(9);return view('assets.index',compact('assets')); }
 public function store(Request $r){
  $this->guard();
  $d=$r->validate(['name'=>'required|max:255','type'=>'required|in:ruangan,kendaraan,peralatan,lainnya','facilities'=>'nullable','location'=>'required|max:255','capacity'=>'nullable|integer|min:1','description'=>'nullable','images'=>'nullable|array|max:10','images.*'=>'image|max:5120']);
  unset($d['images']);
  $d['created_by']=Auth::id();
  $d['is_public']=$r->boolean('is_public');
  $a=Asset::create($d);
  foreach($r->file('images',[]) as $i=>$f)$a->images()->create(['path'=>$f->store('assets','public'),'sort_order'=>$i]);
  return back()->with('success','Aset berhasil ditambahkan.');
 }
 public function update(Request $r, Asset $asset){
  $this->guard();
  $d=$r->validate(['name'=>'required|max:255','type'=>'required|in:ruangan,kendaraan,peralatan,lainnya','facilities'=>'nullable','location'=>'required|max:255','capacity'=>'nullable|integer|min:1','description'=>'nullable','images'=>'nullable|array|max:10','images.*'=>'image|max:5120']);
  unset($d['images']);
  $d['is_public']=$r->boolean('is_public');
  $asset->update($d);
  $nextSort=(int)$asset->images()->max('sort_order')+1;
  foreach($r->file('images',[]) as $i=>$f)$asset->images()->create(['path'=>$f->store('assets','public'),'sort_order'=>$nextSort+$i]);
  return redirect()->route('assets.index',['page'=>$r->integer('page',1)])->with('success','Aset berhasil diperbarui.');
 }
 public function destroy(Asset $asset){$this->guard();abort_if($asset->bookings()->where('ends_at','>=',now())->exists(),422,'Aset masih memiliki jadwal pemakaian.');foreach($asset->images as $i)Storage::disk('public')->delete($i->path);$asset->delete();return back()->with('success','Aset dihapus.');}
 public function monitoring(Request $r){$this->guard();$date=$r->date('date')?->toDateString()??now()->toDateString();$assets=Asset::with(['bookings'=>fn($q)=>$q->whereDate('starts_at','<=',$date)->whereDate('ends_at','>=',$date)->orderBy('starts_at'),'bookings.bookable'])->orderBy('name')->get();return view('assets.monitoring',compact('assets','date'));}
}
