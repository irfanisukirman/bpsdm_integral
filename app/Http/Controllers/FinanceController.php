<?php
namespace App\Http\Controllers;
use App\Models\FinanceBudgetLine;
use App\Models\FinanceGuAllocation;
use App\Models\FinanceGuBatch;
use App\Models\FinanceTransaction;
use App\Models\Schedule;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

class FinanceController extends Controller
{
 private const STATUSES=['draft','submitted','verified','ready','paid','revision','rejected','cancelled'];

 public function index(Request $request)
 {
  $user=$this->guard();$year=(int)($request->year?:now()->year);$bidang=$this->scopeBidang($request);
  $years=FinanceBudgetLine::select('year')->distinct()->orderByDesc('year')->pluck('year');
  $lines=FinanceBudgetLine::with('parent')->withCount(['children','transactions','guAllocations'])->where('year',$year)->where('bidang',$bidang)->orderByRaw("FIELD(level,'activity','subactivity','account')")->orderBy('account_code')->orderBy('description')->get();
  $codingLines=$lines->filter(fn($line)=>$line->level!=='activity'&&(int)$line->children_count===0)->values();$codingIds=$codingLines->pluck('id');
  $transactions=FinanceTransaction::with(['budgetLine','schedule.pengajar','schedule.training'])->where('year',$year)->where('bidang',$bidang)->when($request->status,fn($q,$v)=>$q->where('status',$v))->when($request->search,fn($q,$v)=>$q->where(fn($x)=>$x->where('payee','like',"%$v%")->orWhere('description','like',"%$v%")))->latest('transaction_date')->paginate(15)->withQueryString();
  $budget=(float)$lines->where('level','activity')->sum('allocated_amount');$prognosis=(float)FinanceTransaction::whereIn('finance_budget_line_id',$codingIds)->whereNotIn('status',['rejected','cancelled'])->sum('net_amount');$realization=(float)FinanceTransaction::whereIn('finance_budget_line_id',$codingIds)->sum('realized_amount');$gu=(float)FinanceGuAllocation::whereIn('finance_budget_line_id',$codingIds)->sum('amount');
  $stats=['budget'=>$budget,'gu'=>$gu,'prognosis'=>$prognosis,'realization'=>$realization,'remaining'=>$budget-$realization,'gu_remaining'=>$gu-$realization,'absorption'=>$budget>0?($realization/$budget)*100:0];
  $lineSummary=$codingLines->map(fn($line)=>$this->summarizeBudgetLine($line,$lines,$codingLines));$structureRows=collect();foreach($lines->where('level','activity') as $activity){$structureRows->push($this->summarizeBudgetLine($activity,$lines,$codingLines));foreach($lines->where('parent_id',$activity->id) as $coding){$structureRows->push($this->summarizeBudgetLine($coding,$lines,$codingLines));foreach($lines->where('parent_id',$coding->id) as $legacy)$structureRows->push($this->summarizeBudgetLine($legacy,$lines,$codingLines));}}
  $monthlyPrognosis=array_fill(1,12,0.0);$monthlyRealization=array_fill(1,12,0.0);foreach($lineSummary as $summary){foreach(range(1,12) as $month){$monthlyPrognosis[$month]+=(float)$summary->monthly_prognosis[$month];$monthlyRealization[$month]+=(float)$summary->monthly_realization[$month];}}
  $guBatches=FinanceGuBatch::with('allocations.budgetLine')->where('year',$year)->where('bidang',$bidang)->latest('gu_date')->get();
  $usedScheduleIds=FinanceTransaction::whereNotNull('schedule_id')->pluck('schedule_id');$teacherSchedules=Schedule::with(['training','pengajar.pengajar','pengajarDocuments'])->whereNotNull('pengajar_id')->where('schedule_type','learning')->whereDate('date','<=',today())->whereNotIn('id',$usedScheduleIds)->whereHas('training',fn($q)=>$q->where('bidang',$bidang))->orderBy('date')->limit(100)->get();
  $parentOptions=$lines->where('level','activity');$bidangOptions=UserController::$listBidang;
  return view('finance.index',compact('year','years','bidang','bidangOptions','lines','lineSummary','structureRows','transactions','teacherSchedules','stats','monthlyPrognosis','monthlyRealization','guBatches','parentOptions'));
 } public function storeBudget(Request $request)
 {
  $user=$this->guard();$d=$request->validate(['year'=>'required|integer|min:2000|max:'.(now()->year+5),'bidang'=>['required',Rule::in(UserController::$listBidang)],'level'=>'required|in:activity,subactivity,account','parent_id'=>'nullable|exists:finance_budget_lines,id','account_code'=>'nullable|string|max:100','description'=>'required|string|max:255','allocated_amount'=>'nullable|numeric|min:0']);
  $this->assertBidang($d['bidang']);$this->validateParent($d);
  $d['account_code']=filled($d['account_code']??null)?trim($d['account_code']):null;
  $d['description']=trim($d['description']);$d['allocated_amount']=$d['allocated_amount']??0;
  if($d['level']!=='activity'){$parent=FinanceBudgetLine::findOrFail($d['parent_id']);$used=(float)$parent->children()->sum('allocated_amount');abort_if($used+(float)$d['allocated_amount']>(float)$parent->allocated_amount,422,'Nilai Kodering melebihi sisa Anggaran Sub Kegiatan.');}
  $exists=FinanceBudgetLine::where('year',$d['year'])->where('bidang',$d['bidang'])->where('level',$d['level'])->where('parent_id',$d['parent_id']??null)->where('account_code',$d['account_code'])->where('description',$d['description'])->exists();
  if($exists)return back()->with('warning','Struktur yang sama sudah tersimpan. Data duplikat tidak dibuat.');
  FinanceBudgetLine::create($d+['created_by'=>$user->id]);
  return back()->with('success','Struktur anggaran berhasil ditambahkan.');
 }

 public function updateBudget(Request $request,FinanceBudgetLine $budget)
 {
  $this->guard();$this->authorizeBidang($budget->bidang);$d=$request->validate(['account_code'=>'nullable|string|max:100','description'=>'required|string|max:255','allocated_amount'=>'nullable|numeric|min:0']);$amount=(float)($d['allocated_amount']??0);if($budget->level==='activity'){abort_if($amount<(float)$budget->children()->sum('allocated_amount'),422,'Anggaran tidak boleh lebih kecil dari total Kodering yang sudah dialokasikan.');}elseif($budget->parent){$used=(float)$budget->parent->children()->where('id','!=',$budget->id)->sum('allocated_amount');abort_if($used+$amount>(float)$budget->parent->allocated_amount,422,'Nilai Kodering melebihi sisa Anggaran Sub Kegiatan.');}$budget->update(['account_code'=>$d['account_code']??null,'description'=>$d['description'],'allocated_amount'=>$amount]);return back()->with('success','Anggaran berhasil diperbarui.');
 }

 public function destroyBudget(FinanceBudgetLine $budget)
 {
  $this->guard();$this->authorizeBidang($budget->bidang);abort_if($budget->children()->exists()||$budget->transactions()->exists()||$budget->guAllocations()->exists(),422,'Anggaran masih memiliki turunan atau transaksi.');$budget->delete();return back()->with('success','Baris anggaran berhasil dihapus.');
 }

 public function storeTransaction(Request $request)
 {
  $user=$this->guard();$d=$this->transactionData($request);$line=FinanceBudgetLine::findOrFail($d['finance_budget_line_id']);$selectedParent=(int)$d['parent_budget_line_id'];unset($d['parent_budget_line_id']);$this->authorizeBidang($line->bidang);abort_if($line->level==='activity'||$line->children()->exists(),422,'Pengeluaran harus menggunakan Kodering paling akhir.');$actualParent=$line->parent?->level==='activity'?(int)$line->parent_id:(int)$line->parent?->parent_id;abort_unless($actualParent===$selectedParent,422,'Kodering tidak berasal dari Sub Kegiatan yang dipilih.');$existingPlan=(float)$line->transactions()->whereNotIn('status',['rejected','cancelled'])->sum('net_amount');abort_if($existingPlan+(float)$d['gross_amount']>(float)$line->allocated_amount,422,'Nominal Pengeluaran melebihi sisa Anggaran Kodering.');$this->saveEvidence($request,$d);$d['bidang']=$line->bidang;$d['year']=$line->year;$d['type']='other';$d['status']='submitted';$d['created_by']=$user->id;FinanceTransaction::create($d);return back()->with('success','Pengeluaran masuk ke prognosis dan menunggu verifikasi.');
 }

 public function storeTeacherPayment(Request $request,Schedule $schedule)
 {
  $user=$this->guard();$schedule->load(['training','pengajar.pengajar','pengajarDocuments']);$this->authorizeBidang($schedule->training->bidang);abort_if(FinanceTransaction::where('schedule_id',$schedule->id)->exists(),422,'Jadwal ini sudah masuk proses pembayaran.');$missing=$this->teacherMissing($schedule);abort_if($missing,422,'Kelengkapan pengajar belum lengkap: '.implode(', ',$missing).'.');
  $d=$request->validate(['finance_budget_line_id'=>'required|exists:finance_budget_lines,id','rate'=>'required|numeric|min:0','tax_amount'=>'nullable|numeric|min:0','note'=>'nullable|string|max:2000']);$line=FinanceBudgetLine::findOrFail($d['finance_budget_line_id']);abort_unless($line->level!=='activity'&&!$line->children()->exists()&&$line->bidang===$schedule->training->bidang&&$line->year==(int)date('Y',strtotime($schedule->date)),422,'Rincian anggaran tidak sesuai bidang/tahun atau bukan rincian paling akhir.');
  $gross=(float)$schedule->jp*(float)$d['rate'];$tax=(float)($d['tax_amount']??0);abort_if($tax>$gross,422,'Potongan tidak boleh melebihi honor bruto.');
  FinanceTransaction::create(['finance_budget_line_id'=>$line->id,'schedule_id'=>$schedule->id,'bidang'=>$line->bidang,'year'=>$line->year,'type'=>'teacher_payment','status'=>'submitted','transaction_date'=>$schedule->date,'payee'=>$schedule->pengajar->name,'description'=>'Honor '.$schedule->activity.' - '.$schedule->training->nama_pelatihan,'gross_amount'=>$gross,'tax_amount'=>$tax,'net_amount'=>$gross-$tax,'note'=>$d['note']??null,'created_by'=>$user->id]);
  return back()->with('success','Honor pengajar masuk ke prognosis pembayaran.');
 }

 public function destroyTransaction(FinanceTransaction $transaction)
 {
  $this->guard();$this->authorizeBidang($transaction->bidang);
  if($transaction->evidence_path)Storage::disk('public')->delete($transaction->evidence_path);
  $transaction->delete();
  return back()->with('success','Transaksi berhasil dihapus dan rekap prognosis telah diperbarui.');
 }
 public function storeGu(Request $request)
 {
  $user=$this->guard();$d=$request->validate(['year'=>'required|integer','bidang'=>['required',Rule::in(UserController::$listBidang)],'parent_budget_line_id'=>'required|exists:finance_budget_lines,id','gu_date'=>'required|date','description'=>'required|string|max:255','amount'=>'required|numeric|min:0.01','allocations'=>'required|array','allocations.*'=>'nullable|numeric|min:0','note'=>'nullable|string|max:2000']);$this->authorizeBidang($d['bidang']);
  $parent=FinanceBudgetLine::findOrFail($d['parent_budget_line_id']);abort_unless($parent->level==='activity'&&$parent->year==(int)$d['year']&&$parent->bidang===$d['bidang'],422,'Sub Kegiatan/Rincian Belanja tidak valid.');
  $codingIds=FinanceBudgetLine::where('parent_id',$parent->id)->pluck('id');$legacyIds=FinanceBudgetLine::whereIn('parent_id',$codingIds)->pluck('id');$allowedIds=$codingIds->merge($legacyIds)->unique();$existingParentGu=(float)FinanceGuAllocation::whereIn('finance_budget_line_id',$allowedIds)->sum('amount');abort_if($existingParentGu+(float)$d['amount']>(float)$parent->allocated_amount,422,'Jumlah GU melebihi anggaran tersedia pada Sub Kegiatan yang dipilih.');
  $allocations=collect($d['allocations'])->filter(fn($amount)=>(float)$amount>0);abort_if($allocations->isEmpty(),422,'Pilih minimal satu Kodering untuk sumber GU.');abort_if(abs($allocations->sum()-$d['amount'])>0.01,422,'Total pembagian Kodering harus sama dengan jumlah GU.');abort_unless($allocations->keys()->every(fn($id)=>$allowedIds->contains((int)$id)),422,'Semua Kodering harus berasal dari Sub Kegiatan yang dipilih.');
  $lines=FinanceBudgetLine::whereIn('id',$allocations->keys())->get();foreach($lines as $line){$existing=(float)FinanceGuAllocation::where('finance_budget_line_id',$line->id)->sum('amount');abort_if($existing+(float)$allocations[$line->id]>(float)$line->allocated_amount,422,'Alokasi GU pada Kodering '.$line->account_code.' melebihi anggaran tersedia.');}
  DB::transaction(function()use($d,$allocations,$user){$batch=FinanceGuBatch::create(['year'=>$d['year'],'bidang'=>$d['bidang'],'gu_date'=>$d['gu_date'],'description'=>$d['description'],'amount'=>$d['amount'],'note'=>$d['note']??null,'created_by'=>$user->id]);foreach($allocations as $lineId=>$amount)$batch->allocations()->create(['finance_budget_line_id'=>$lineId,'amount'=>$amount]);});return back()->with('success','GU berhasil dicatat dan dibagikan ke Kodering.');
 } public function destroyGu(FinanceGuBatch $gu)
 {
  $this->guard();$this->authorizeBidang($gu->bidang);$gu->delete();return back()->with('success','Data GU berhasil dihapus.');
 }
 public function storeDirectRealization(Request $request)
 {
  $user=$this->guard();$d=$request->validate(['parent_budget_line_id'=>'required|exists:finance_budget_lines,id','finance_budget_line_id'=>'required|exists:finance_budget_lines,id','description'=>'required|string|max:1000','realized_amount'=>'required|numeric|min:0.01']);$line=FinanceBudgetLine::findOrFail($d['finance_budget_line_id']);$this->authorizeBidang($line->bidang);abort_if($line->level==='activity'||$line->children()->exists(),422,'Realisasi harus menggunakan Kodering paling akhir.');$actualParent=$line->parent?->level==='activity'?(int)$line->parent_id:(int)$line->parent?->parent_id;abort_unless($actualParent===(int)$d['parent_budget_line_id'],422,'Kodering tidak berasal dari Sub Kegiatan yang dipilih.');
  $realized=(float)$line->transactions()->sum('realized_amount');$budgetAvailable=(float)$line->allocated_amount-$realized;$guTotal=(float)$line->guAllocations()->sum('amount');$guAvailable=$guTotal-$realized;$amount=(float)$d['realized_amount'];abort_if($amount>$budgetAvailable,422,'Realisasi melebihi sisa Anggaran Kodering.');abort_if($amount>$guAvailable,422,'Realisasi melebihi sisa GU Kodering.');
  FinanceTransaction::create(['finance_budget_line_id'=>$line->id,'bidang'=>$line->bidang,'year'=>$line->year,'type'=>'direct_realization','status'=>'paid','transaction_date'=>today(),'payee'=>'Realisasi Langsung','description'=>$d['description'],'gross_amount'=>0,'tax_amount'=>0,'net_amount'=>0,'realized_amount'=>$amount,'realized_at'=>today(),'paid_at'=>now(),'created_by'=>$user->id,'reviewed_by'=>$user->id,'reviewed_at'=>now()]);return back()->with('success','Realisasi langsung berhasil dicatat dan saldo Anggaran serta GU telah diperbarui.');
 }
 public function realizeTransaction(Request $request,FinanceTransaction $transaction)
 {
  $this->guard();$this->authorizeBidang($transaction->bidang);$d=$request->validate(['realized_amount'=>'required|numeric|min:0|max:'.$transaction->net_amount,'realized_at'=>'required|date']);$transaction->update($d);return back()->with('success','Realisasi pengeluaran berhasil disimpan.');
 }
 public function updateStatus(Request $request,FinanceTransaction $transaction)
 {
  $user=$this->guard();$this->authorizeBidang($transaction->bidang);$d=$request->validate(['status'=>['required',Rule::in(self::STATUSES)],'reference_number'=>'nullable|string|max:100','note'=>'nullable|string|max:2000','evidence'=>'nullable|file|max:20480|mimes:pdf,jpg,jpeg,png']);
  if($request->hasFile('evidence')){$old=$transaction->evidence_path;$file=$request->file('evidence');$d['evidence_path']=$file->store('finance-evidence/'.$transaction->year,'public');$d['evidence_name']=$file->getClientOriginalName();if($old)Storage::disk('public')->delete($old);}
  abort_if($d['status']==='paid'&&!($d['evidence_path']??$transaction->evidence_path),422,'Bukti pembayaran wajib diunggah sebelum status Sudah Dibayar.');
  $d['reviewed_by']=$user->id;$d['reviewed_at']=now();$d['paid_at']=$d['status']==='paid'?now():null;$transaction->update($d);return back()->with('success','Status transaksi berhasil diperbarui.');
 }

 public function downloadEvidence(FinanceTransaction $transaction){$this->guard();$this->authorizeBidang($transaction->bidang);abort_unless($transaction->evidence_path&&Storage::disk('public')->exists($transaction->evidence_path),404);return Storage::disk('public')->download($transaction->evidence_path,$transaction->evidence_name);}

 public function export(Request $request)
 {
  $this->guard();$year=(int)($request->year?:now()->year);$bidang=$this->scopeBidang($request);$lines=FinanceBudgetLine::with('parent')->withCount(['children','transactions','guAllocations'])->where('year',$year)->where('bidang',$bidang)->orderByRaw("FIELD(level,'activity','subactivity','account')")->orderBy('account_code')->get();$codingLines=$lines->filter(fn($line)=>$line->level!=='activity'&&(int)$line->children_count===0)->values();
  $months=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];$monthlyHeaders=[];foreach($months as $month){$monthlyHeaders[]=$month.' Prognosis';$monthlyHeaders[]=$month.' Realisasi';}$rows=[array_merge(['Jenis','Kode','Uraian','Anggaran','GU'],$monthlyHeaders,['Total Prognosis','Total Realisasi','Sisa Anggaran','Sisa GU'])];
  foreach($lines->where('level','activity') as $parent){$ordered=collect([$parent]);foreach($lines->where('parent_id',$parent->id) as $coding){$ordered->push($coding);foreach($lines->where('parent_id',$coding->id) as $legacy)$ordered->push($legacy);}foreach($ordered as $line){$line=$this->summarizeBudgetLine($line,$lines,$codingLines);$monthly=[];foreach(range(1,12) as $month){$monthly[]=$line->monthly_prognosis[$month];$monthly[]=$line->monthly_realization[$month];}$rows[]=array_merge([$line->level==='activity'?'Sub Kegiatan/Rincian Belanja':'Kodering',$line->account_code,$line->description,$line->display_budget,$line->gu_amount],$monthly,[$line->prognosis_total,$line->realization_total,$line->remaining_budget,$line->remaining_gu]);}}
  return Excel::download(new class($rows)implements FromArray,\Maatwebsite\Excel\Concerns\ShouldAutoSize,\Maatwebsite\Excel\Concerns\WithStyles{public function __construct(private array $rows){}public function array():array{return $this->rows;}public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $s):array{$s->freezePane('A2');return[1=>['font'=>['bold'=>true,'color'=>['argb'=>'FFFFFFFF']],'fill'=>['fillType'=>'solid','startColor'=>['argb'=>'FF284B9B']]]];}},'rekap-keuangan-'.\Illuminate\Support\Str::slug($bidang).'-'.$year.'.xlsx');
 } private function summarizeBudgetLine(FinanceBudgetLine $line,$lines,$codingLines):FinanceBudgetLine
 {
  $codingIds=$codingLines->filter(function($coding)use($line,$lines){if((int)$coding->id===(int)$line->id)return true;$cursor=$coding;while($cursor&&$cursor->parent_id){if((int)$cursor->parent_id===(int)$line->id)return true;$cursor=$lines->firstWhere('id',$cursor->parent_id);}return false;})->pluck('id');
  $transactions=FinanceTransaction::whereIn('finance_budget_line_id',$codingIds)->whereNotIn('status',['rejected','cancelled'])->get(['transaction_date','net_amount','realized_amount','realized_at']);
  $monthlyPrognosis=array_fill(1,12,0.0);$monthlyRealization=array_fill(1,12,0.0);foreach($transactions as $transaction){$monthlyPrognosis[(int)$transaction->transaction_date->format('n')]+=(float)$transaction->net_amount;if($transaction->realized_amount!==null){$date=$transaction->realized_at?:$transaction->transaction_date;$monthlyRealization[(int)$date->format('n')]+=(float)$transaction->realized_amount;}}
  $budget=(float)$line->allocated_amount;$gu=(float)FinanceGuAllocation::whereIn('finance_budget_line_id',$codingIds)->sum('amount');$prognosis=(float)array_sum($monthlyPrognosis);$realization=(float)array_sum($monthlyRealization);
  $line->setAttribute('display_budget',$budget);$line->setAttribute('gu_amount',$gu);$line->setAttribute('monthly_prognosis',$monthlyPrognosis);$line->setAttribute('monthly_realization',$monthlyRealization);$line->setAttribute('prognosis_total',$prognosis);$line->setAttribute('realization_total',$realization);$line->setAttribute('remaining_budget',$budget-$realization);$line->setAttribute('remaining_gu',$gu-$realization);return $line;
 } private function teacherMissing(Schedule $schedule):array
 {
  $user=$schedule->pengajar;$profile=$user?->pengajar;$checks=['NIP/NIK'=>$user?->nip_nik,'jabatan'=>$user?->jabatan,'instansi'=>$user?->instansi,'NPWP'=>$profile?->npwp,'nomor rekening'=>$profile?->nomor_rekening,'bank'=>$profile?->nama_bank,'nama pemilik rekening'=>$profile?->nama_rekening,'CV'=>$profile?->cv_path,'sertifikat TOT'=>$profile?->sertifikat_path,'surat tugas'=>$profile?->surat_tugas_path,'dokumen materi/RBPMP'=>$schedule->pengajarDocuments];
  return collect($checks)->filter(fn($value)=>blank($value))->keys()->all();
 } private function transactionData(Request $r):array{$d=$r->validate(['parent_budget_line_id'=>'required|exists:finance_budget_lines,id','finance_budget_line_id'=>'required|exists:finance_budget_lines,id','transaction_date'=>'required|date','description'=>'required|string|max:1000','gross_amount'=>'required|numeric|min:0','note'=>'nullable|string|max:2000']);$d['payee']='Pengeluaran Bidang';$d['tax_amount']=0;$d['net_amount']=$d['gross_amount'];return $d;} private function saveEvidence(Request $r,array &$d):void{if(!$r->hasFile('evidence'))return;$f=$r->file('evidence');$d['evidence_path']=$f->store('finance-evidence/'.date('Y',strtotime($d['transaction_date'])),'public');$d['evidence_name']=$f->getClientOriginalName();unset($d['evidence']);}
 private function validateParent(array $d):void{if($d['level']==='activity'){abort_if(!empty($d['parent_id']),422,'Kegiatan tidak memiliki induk.');return;}abort_if(empty($d['parent_id']),422,'Subkegiatan/kode rekening wajib memiliki induk.');$p=FinanceBudgetLine::findOrFail($d['parent_id']);abort_unless($p->year==(int)$d['year']&&$p->bidang===$d['bidang'],422,'Induk harus pada tahun dan bidang yang sama.');abort_if($d['level']==='subactivity'&&$p->level!=='activity',422,'Induk subkegiatan harus kegiatan.');abort_if($d['level']==='account'&&$p->level!=='subactivity',422,'Induk kode rekening harus subkegiatan.');}
 private function scopeBidang(Request $r):string{$u=Auth::user();$b=$u->role==='superadmin'?($r->bidang?:UserController::$listBidang[0]):$u->bidang;abort_unless(in_array($b,UserController::$listBidang,true),422,'Bidang tidak valid.');return $b;}
 private function guard(){ $u=Auth::user();abort_unless($u&&in_array($u->role,['superadmin','pengelola_keuangan'],true),403);return $u;}
 private function assertBidang(string $b):void{$this->authorizeBidang($b);}
 private function authorizeBidang(string $b):void{$u=Auth::user();abort_unless($u->role==='superadmin'||$u->bidang===$b,403);}
}
