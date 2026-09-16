<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\TicketService;
use App\Models\TicketCategory;
use App\Models\TicketBidang;
use App\Models\TicketRoutingRule;
use App\Models\TicketSla;
use App\Models\User;
use Illuminate\Http\Request;
class TicketingMasterController extends Controller {
 public function services(){return view('admin.ticketing.master.services',['items'=>TicketService::orderBy('sort_order')->get()]);}
 public function storeService(Request $r){
  $r->validate(['name'=>'required|string|max:255','slug'=>'required|string|max:255|unique:ticket_services,slug']);
  TicketService::create($r->only('name','slug'));
  return back()->with('success','Layanan berhasil ditambahkan.');
 }
 public function updateService(Request $r,TicketService $service){
  $r->validate(['name'=>'required|string|max:255']);
  $service->update($r->only('name','is_active','sort_order'));
  return back()->with('success','Layanan berhasil diperbarui.');
 }
 public function destroyService(TicketService $service){
  if($service->routingRules()->exists()){return back()->with('error','Layanan masih memiliki routing rules.');}
  $service->delete();
  return back()->with('success','Layanan berhasil dihapus.');
 }
 public function categories(){return view('admin.ticketing.master.categories',['items'=>TicketCategory::orderBy('sort_order')->get()]);}
 public function storeCategory(Request $r){
  $r->validate(['name'=>'required|string|max:255','slug'=>'required|string|max:255|unique:ticket_categories,slug']);
  TicketCategory::create($r->only('name','slug'));
  return back()->with('success','Kategori berhasil ditambahkan.');
 }
 public function updateCategory(Request $r,TicketCategory $category){
  $r->validate(['name'=>'required|string|max:255']);
  $category->update($r->only('name','is_active','sort_order'));
  return back()->with('success','Kategori berhasil diperbarui.');
 }
 public function destroyCategory(TicketCategory $category){
  if($category->routingRules()->exists()){return back()->with('error','Kategori masih memiliki routing rules.');}
  $category->delete();
  return back()->with('success','Kategori berhasil dihapus.');
 }
 public function bidang(){return view('admin.ticketing.master.bidang',['items'=>TicketBidang::orderBy('name')->get(),'users'=>User::whereIn('role',['superadmin','admin_bidang'])->orderBy('name')->get()]);}
 public function storeBidang(Request $r){
  $r->validate(['name'=>'required|string|max:255|unique:ticket_bidang,name']);
  TicketBidang::create($r->only('name','default_handler_user_id'));
  return back()->with('success','Bidang berhasil ditambahkan.');
 }
 public function updateBidang(Request $r,TicketBidang $bidang){
  $r->validate(['name'=>'required|string|max:255']);
  $bidang->update($r->only('name','is_active','default_handler_user_id'));
  return back()->with('success','Bidang berhasil diperbarui.');
 }
 public function destroyBidang(TicketBidang $bidang){
  if($bidang->routingRules()->exists()){return back()->with('error','Bidang masih memiliki routing rules.');}
  $bidang->delete();
  return back()->with('success','Bidang berhasil dihapus.');
 }
 public function routing(){
  $rules=TicketRoutingRule::with(['service','category','bidang','defaultPic'])->get();
  $services=TicketService::where('is_active',true)->get();
  $categories=TicketCategory::where('is_active',true)->get();
  $bidangs=TicketBidang::where('is_active',true)->get();
  $pics=User::whereIn('role',['superadmin','admin_bidang'])->orderBy('name')->get();
  return view('admin.ticketing.master.routing',compact('rules','services','categories','bidangs','pics'));
 }
 public function storeRouting(Request $r){
  $r->validate(['service_id'=>'required|exists:ticket_services,id','category_id'=>'required|exists:ticket_categories,id','bidang_id'=>'required|exists:ticket_bidang,id','default_pic_user_id'=>'nullable|exists:users,id','sla_respond_hours'=>'nullable|numeric|min:0','sla_resolve_hours'=>'nullable|numeric|min:0']);
  TicketRoutingRule::updateOrCreate(['service_id'=>$r->service_id,'category_id'=>$r->category_id],$r->only('bidang_id','default_pic_user_id','sla_respond_hours','sla_resolve_hours'));
  return back()->with('success','Routing rule berhasil disimpan.');
 }
 public function destroyRouting(TicketRoutingRule $rule){
  $rule->delete();
  return back()->with('success','Routing rule berhasil dihapus.');
 }
 public function sla(){
  $slas=TicketSla::with(['service','category','bidang'])->get();
  $services=TicketService::where('is_active',true)->get();
  $categories=TicketCategory::where('is_active',true)->get();
  $bidangs=TicketBidang::where('is_active',true)->get();
  return view('admin.ticketing.master.sla',compact('slas','services','categories','bidangs'));
 }
 public function storeSla(Request $r){
  $r->validate(['service_id'=>'nullable|exists:ticket_services,id','category_id'=>'nullable|exists:ticket_categories,id','bidang_id'=>'nullable|exists:ticket_bidang,id','respond_hours'=>'required|numeric|min:0','resolve_hours'=>'required|numeric|min:0','working_hours_start'=>'required','working_hours_end'=>'required']);
  $exists=TicketSla::where('service_id',$r->service_id)->where('category_id',$r->category_id)->where('bidang_id',$r->bidang_id)->first();
  if($exists){$exists->update($r->only('respond_hours','resolve_hours','working_hours_start','working_hours_end','is_active'));}else{TicketSla::create($r->all());}
  return back()->with('success','Konfigurasi SLA berhasil disimpan.');
 }
 public function destroySla(TicketSla $sla){
  $sla->delete();
  return back()->with('success','Konfigurasi SLA berhasil dihapus.');
 }
}