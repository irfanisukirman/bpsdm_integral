<?php
namespace App\Http\Controllers;
use App\Models\File;
use App\Models\Folder;
use App\Models\PartnerSubmission;
use App\Models\PartnerSubmissionComment;
use App\Models\PartnerSubmissionDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
class PartnerSubmissionController extends Controller
{
 public const TRAINING_FIELDS=['Bidang Pengembangan Kompetensi Teknis Inti','Bidang Pengembangan Kompetensi Teknis Umum','Bidang Pengembangan Kompetensi Manajerial'];
 public const COOP_FIELD='Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan';
 public function index(Request $request)
 {
  $this->mitra();
  $base=PartnerSubmission::where('user_id',Auth::id());
  $stats=['all'=>(clone $base)->count(),'draft'=>(clone $base)->where('status','draft')->count(),'process'=>(clone $base)->whereNotIn('status',['draft','final','rejected'])->count(),'revision'=>(clone $base)->where('status','revision_requested')->count(),'final'=>(clone $base)->where('status','final')->count()];
  $submissions=(clone $base)->withCount('documents')
   ->when($request->type,fn($q,$v)=>$q->where('type',$v))
   ->when($request->status,fn($q,$v)=>$v==='process'?$q->whereNotIn('status',['draft','final','rejected']):$q->where('status',$v))
   ->when($request->search,fn($q,$v)=>$q->where('title','like',"%$v%"))
   ->latest()->paginate(10)->withQueryString();
  return view('mitra.submissions.index',compact('submissions','stats'));
 }
 public function create(string $type){$this->mitra();abort_unless(in_array($type,['training','cooperation']),404);$trainingFields=self::TRAINING_FIELDS;return view('mitra.submissions.create',compact('type','trainingFields'));}
 public function store(Request $r)
 {
  $this->mitra();
  $r->validate(['request_letter'=>'required|file|max:20480|mimes:pdf,doc,docx']);
  $d=$this->data($r);$d['user_id']=Auth::id();$d['target_bidang']=$d['type']==='cooperation'?self::COOP_FIELD:$d['target_bidang'];$d['status']='draft';
  $upload=$r->file('request_letter');$path=$upload->store('partner-submissions/request-letters','public');
  try {
   $s=DB::transaction(function()use($d,$upload,$path){$submission=PartnerSubmission::create($d);$submission->documents()->create(['uploaded_by'=>Auth::id(),'version_number'=>1,'display_name'=>$upload->getClientOriginalName(),'file_path'=>$path,'file_type'=>$upload->getClientOriginalExtension(),'file_size'=>$upload->getSize(),'change_note'=>'Surat permohonan awal']);return $submission;});
  } catch (\Throwable $e) {Storage::disk('public')->delete($path);throw $e;}
  return redirect()->route('mitra.submissions.show',$s)->with('success','Pengajuan dan surat permohonan berhasil disimpan sebagai draft.');
 }
 public function update(Request $r,PartnerSubmission $submission){$this->owner($submission);abort_unless(in_array($submission->status,['draft','revision_requested']),422,'Pengajuan tidak dapat diedit pada status ini.');$d=$this->data($r,$submission->type);$d['target_bidang']=$submission->type==='cooperation'?self::COOP_FIELD:$d['target_bidang'];$submission->update($d);return back()->with('success','Data pengajuan berhasil diperbarui.');}
 public function show(PartnerSubmission $submission){$this->access($submission);$submission->load(['partner','assignee','documents.uploader','comments.user','folder']);$admin=$this->isAdmin();$trainingFields=self::TRAINING_FIELDS;$admins=User::where('role','admin_bidang')->where('bidang',$submission->target_bidang)->orderBy('name')->get();return view('mitra.submissions.show',compact('submission','admin','trainingFields','admins'));}
 public function submit(PartnerSubmission $submission){$this->owner($submission);abort_unless(in_array($submission->status,['draft','revision_requested']),422);$submission->update(['status'=>$submission->status==='revision_requested'?'revision_submitted':'submitted','submitted_at'=>now()]);return back()->with('success','Pengajuan berhasil dikirim kepada '.$submission->target_bidang.'.');}
 public function comments(Request $r,PartnerSubmission $submission)
 {
  $this->access($submission);$after=max(0,(int)$r->query('after',0));
  $comments=$submission->comments()->with('user')->where('id','>',$after)->orderBy('id')->get()->map(fn($comment)=>$this->commentPayload($comment));
  return response()->json(['comments'=>$comments]);
 }
 public function comment(Request $r,PartnerSubmission $submission)
 {
  $this->access($submission);$d=$r->validate(['message'=>'required|string|max:3000']);$comment=$submission->comments()->create(['user_id'=>Auth::id(),'message'=>$d['message']]);$comment->load('user');
  if($r->expectsJson())return response()->json(['comment'=>$this->commentPayload($comment)],201);
  return back()->with('success','Tanggapan berhasil dikirim.');
 }
 public function upload(Request $r,PartnerSubmission $submission){$this->access($submission);abort_if(in_array($submission->status,['final','rejected']),422,'Pengajuan sudah ditutup.');$d=$r->validate(['document'=>'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx','change_note'=>'nullable|string|max:2000']);$upload=$r->file('document');$version=(int)$submission->documents()->max('version_number')+1;$path=$upload->store('partner-submissions/'.$submission->id,'public');$submission->documents()->create(['uploaded_by'=>Auth::id(),'version_number'=>$version,'display_name'=>$upload->getClientOriginalName(),'file_path'=>$path,'file_type'=>$upload->getClientOriginalExtension(),'file_size'=>$upload->getSize(),'change_note'=>$d['change_note']??null]);return back()->with('success','Draft dokumen versi '.$version.' berhasil diunggah.');}
 public function download(PartnerSubmissionDocument $document){$this->access($document->submission);abort_unless(Storage::disk('public')->exists($document->file_path),404);return Storage::disk('public')->download($document->file_path,$document->display_name);}
 public function adminIndex(Request $r){$this->admin();$q=PartnerSubmission::with(['partner','assignee'])->withCount('documents')->when(Auth::user()->role!=='superadmin',fn($q)=>$q->where('target_bidang',Auth::user()->bidang))->when($r->type,fn($q,$v)=>$q->where('type',$v))->when($r->status,fn($q,$v)=>$v==='process'?$q->whereNotIn('status',['draft','final','rejected']):$q->where('status',$v))->when($r->search,fn($q,$v)=>$q->where(fn($s)=>$s->where('title','like',"%$v%")->orWhereHas('partner',fn($u)=>$u->where('name','like',"%$v%"))));$submissions=$q->latest()->paginate(15)->withQueryString();return view('mitra.admin.index',compact('submissions'));}
 public function review(Request $r,PartnerSubmission $submission){$this->adminAccess($submission);$d=$r->validate(['status'=>['required',Rule::in(['under_review','revision_requested','waiting_approval','rejected'])],'assigned_to'=>'nullable|exists:users,id','message'=>'nullable|string|max:3000']);if($d['assigned_to']??null){abort_unless(User::whereKey($d['assigned_to'])->where('role','admin_bidang')->where('bidang',$submission->target_bidang)->exists(),422,'PIC tidak sesuai bidang tujuan.');}$submission->update(['status'=>$d['status'],'assigned_to'=>$d['assigned_to']??$submission->assigned_to]);if(filled($d['message']??null))$submission->comments()->create(['user_id'=>Auth::id(),'message'=>$d['message']]);return back()->with('success','Status pengajuan berhasil diperbarui.');}
 public function reopen(PartnerSubmission $submission)
 {
  $this->adminAccess($submission);abort_unless($submission->status==='final',422,'Hanya pengajuan final yang dapat dikembalikan menjadi draft.');
  DB::transaction(function()use($submission){$paths=$submission->documents()->pluck('file_path');if($submission->folder_id){$final=Folder::where('parent_id',$submission->folder_id)->where('name','Final')->first();if($final){File::where('folder_id',$final->id)->whereIn('file_path',$paths)->delete();if(!$final->files()->exists()&&!$final->children()->exists())$final->delete();}}$submission->documents()->update(['is_final'=>false]);$submission->update(['status'=>'draft','folder_id'=>null,'finalized_at'=>null]);});
  return back()->with('success','Status final dibatalkan. Pengajuan dikembalikan menjadi draft dan ruang chat dibuka kembali.');
 }
 public function destroy(PartnerSubmission $submission)
 {
  $this->adminAccess($submission);$submission->load('documents');$paths=$submission->documents->pluck('file_path');$folderIds=$submission->folder_id?$this->folderTreeIds($submission->folder_id):collect();if($folderIds->isNotEmpty())$paths=$paths->merge(File::whereIn('folder_id',$folderIds)->pluck('file_path'))->unique();
  DB::transaction(function()use($submission,$folderIds){if($folderIds->isNotEmpty()){File::whereIn('folder_id',$folderIds)->delete();foreach($folderIds->reverse() as $folderId)Folder::whereKey($folderId)->delete();}$submission->delete();});
  foreach($paths as $path){$used=File::where('file_path',$path)->exists()||PartnerSubmissionDocument::where('file_path',$path)->exists();if(!$used&&Storage::disk('public')->exists($path))Storage::disk('public')->delete($path);}
  return redirect()->route('mitra.admin.index')->with('success','Pengajuan beserta chat, riwayat draft, dan dokumen terkait berhasil dihapus.');
 } public function finalize(PartnerSubmission $submission){$this->adminAccess($submission);abort_if($submission->status==='final',422,'Pengajuan sudah final.');$document=$submission->documents()->latest('version_number')->first();abort_unless($document,422,'Unggah minimal satu dokumen sebelum finalisasi.');DB::transaction(function()use($submission,$document){$root=Folder::firstOrCreate(['name'=>'Pengajuan Mitra','parent_id'=>null,'bidang'=>$submission->target_bidang],['user_id'=>Auth::id(),'is_public'=>false]);$partner=Folder::firstOrCreate(['name'=>$submission->partner->instansi?:$submission->partner->name,'parent_id'=>$root->id,'bidang'=>$submission->target_bidang],['user_id'=>Auth::id(),'is_public'=>false]);$folder=Folder::firstOrCreate(['name'=>$submission->title,'parent_id'=>$partner->id,'bidang'=>$submission->target_bidang],['user_id'=>Auth::id(),'is_public'=>false]);$final=Folder::firstOrCreate(['name'=>'Final','parent_id'=>$folder->id,'bidang'=>$submission->target_bidang],['user_id'=>Auth::id(),'is_public'=>false]);File::firstOrCreate(['folder_id'=>$final->id,'file_path'=>$document->file_path],['display_name'=>$document->display_name,'file_type'=>$document->file_type,'file_size'=>$document->file_size,'user_id'=>$document->uploaded_by]);$document->update(['is_final'=>true]);$submission->update(['status'=>'final','folder_id'=>$folder->id,'finalized_at'=>now()]);});return back()->with('success','Dokumen versi terakhir telah ditetapkan sebagai final dan masuk ke Manajemen Dokumen.');}
 private function folderTreeIds(int $rootId)
 {
  $ids=collect([$rootId]);$frontier=collect([$rootId]);do{$children=Folder::whereIn('parent_id',$frontier)->pluck('id');$frontier=$children->diff($ids);$ids=$ids->merge($frontier)->unique()->values();}while($frontier->isNotEmpty());return $ids;
 } private function commentPayload(PartnerSubmissionComment $comment):array
 {
  return ['id'=>$comment->id,'user_id'=>$comment->user_id,'name'=>$comment->user->name,'role'=>$comment->user->role,'role_label'=>match($comment->user->role){'mitra'=>'Mitra','superadmin'=>'Superadmin','admin_bidang'=>'Admin Bidang',default=>'Pengelola'},'message'=>$comment->message,'time'=>$comment->created_at->translatedFormat('d M, H:i')];
 } private function data(Request $r,?string $fixedType=null):array{$type=$fixedType?:$r->input('type');$rules=['type'=>['required',Rule::in(['training','cooperation'])],'title'=>'required|string|max:255','background'=>'nullable|string','objective'=>'nullable|string','scope'=>'nullable|string','pic_name'=>'required|string|max:255','pic_contact'=>'required|string|max:30'];if($type==='training')$rules+=['target_bidang'=>['required',Rule::in(self::TRAINING_FIELDS)],'participant_target'=>'nullable|string|max:255','estimated_participants'=>'nullable|integer|min:1','competency'=>'nullable|string','preferred_start'=>'nullable|date','preferred_end'=>'nullable|date|after_or_equal:preferred_start','method'=>'nullable|string|max:100','location'=>'nullable|string|max:255'];else $rules+=[];$d=$r->validate($rules);$d['type']=$type;return $d;}
 private function mitra(){abort_unless(Auth::user()->role==='mitra',403);}private function admin(){abort_unless($this->isAdmin(),403);}private function isAdmin(){return in_array(Auth::user()->role,['superadmin','admin_bidang'],true);}private function owner($s){$this->mitra();abort_unless((int)$s->user_id===Auth::id(),403);}private function access($s){if($this->isAdmin()){$this->adminAccess($s);return;}$this->owner($s);}private function adminAccess($s){$this->admin();abort_unless(Auth::user()->role==='superadmin'||Auth::user()->bidang===$s->target_bidang,403);}
}