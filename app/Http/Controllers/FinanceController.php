<?php
namespace App\Http\Controllers;
use App\Models\FinanceBudgetLine;
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
  $lines=FinanceBudgetLine::with('parent')->withCount(['children','transactions'])->where('year',$year)->where('bidang',$bidang)->orderByRaw("FIELD(level,'activity','subactivity','account')")->orderBy('account_code')->orderBy('description')->get();
  $leafLines=$lines->filter(fn($line)=>$line->level!=='activity'&&(int)$line->children_count===0)->values();
  $leafIds=$leafLines->pluck('id');
  $transactions=FinanceTransaction::with(['budgetLine','schedule.pengajar','schedule.training'])->where('year',$year)->where('bidang',$bidang)
   ->when($request->status,fn($q,$v)=>$q->where('status',$v))->when($request->search,fn($q,$v)=>$q->where(fn($x)=>$x->where('payee','like',"%$v%")->orWhere('description','like',"%$v%")))->latest('transaction_date')->paginate(15)->withQueryString();
  $paid=FinanceTransaction::whereIn('finance_budget_line_id',$leafIds)->where('status','paid')->sum('net_amount');
  $prognosis=FinanceTransaction::whereIn('finance_budget_line_id',$leafIds)->whereIn('status',['submitted','verified','ready'])->sum('net_amount');
  $budget=(float)$leafLines->sum('allocated_amount');
  $stats=['budget'=>$budget,'realization'=>(float)$paid,'prognosis'=>(float)$prognosis,'remaining'=>$budget-(float)$paid,'unabsorbed'=>max(0,$budget-(float)$paid-(float)$prognosis),'projected'=>(float)$paid+(float)$prognosis];
  $lineSummary=$leafLines->map(fn($line)=>$this->summarizeBudgetLine($line,$lines,$leafLines));
  $monthlyTotals=array_fill(1,12,0.0);foreach($lineSummary as $summary){foreach(range(1,12) as $month)$monthlyTotals[$month]+=(float)$summary->monthly_prognosis[$month];}$monthlyGrandTotal=(float)array_sum($monthlyTotals);
  $structureRows=collect();
  foreach($lines->where('level','activity') as $activity){$structureRows->push($this->summarizeBudgetLine($activity,$lines,$leafLines));foreach($lines->where('parent_id',$activity->id) as $sub){$structureRows->push($this->summarizeBudgetLine($sub,$lines,$leafLines));foreach($lines->where('parent_id',$sub->id) as $account)$structureRows->push($this->summarizeBudgetLine($account,$lines,$leafLines));}}
  $usedScheduleIds=FinanceTransaction::whereNotNull('schedule_id')->pluck('schedule_id');
  $teacherSchedules=Schedule::with(['training','pengajar.pengajar','pengajarDocuments'])->whereNotNull('pengajar_id')->where('schedule_type','learning')->whereDate('date','<=',today())->whereNotIn('id',$usedScheduleIds)->whereHas('training',fn($q)=>$q->where('bidang',$bidang))->orderBy('date')->limit(100)->get();
  $parentOptions=$lines->whereIn('level',['activity','subactivity']);$bidangOptions=UserController::$listBidang;
  return view('finance.index',compact('year','years','bidang','bidangOptions','lines','lineSummary','monthlyTotals','monthlyGrandTotal','structureRows','parentOptions','transactions','teacherSchedules','stats'));
 }
 public function storeBudget(Request $request)
 {
  $user=$this->guard();$d=$request->validate(['year'=>'required|integer|min:2000|max:'.(now()->year+5),'bidang'=>['required',Rule::in(UserController::$listBidang)],'level'=>'required|in:activity,subactivity,account','parent_id'=>'nullable|exists:finance_budget_lines,id','account_code'=>'nullable|string|max:100','description'=>'required|string|max:255','allocated_amount'=>'nullable|numeric|min:0']);
  $this->assertBidang($d['bidang']);$this->validateParent($d);
  $d['account_code']=filled($d['account_code']??null)?trim($d['account_code']):null;
  $d['description']=trim($d['description']);$d['allocated_amount']=$d['level']==='activity'?0:($d['allocated_amount']??0);
  $exists=FinanceBudgetLine::where('year',$d['year'])->where('bidang',$d['bidang'])->where('level',$d['level'])->where('parent_id',$d['parent_id']??null)->where('account_code',$d['account_code'])->where('description',$d['description'])->exists();
  if($exists)return back()->with('warning','Struktur yang sama sudah tersimpan. Data duplikat tidak dibuat.');
  FinanceBudgetLine::create($d+['created_by'=>$user->id]);
  return back()->with('success','Struktur anggaran berhasil ditambahkan.');
 }

 public function updateBudget(Request $request,FinanceBudgetLine $budget)
 {
  $this->guard();$this->authorizeBidang($budget->bidang);$d=$request->validate(['account_code'=>'nullable|string|max:100','description'=>'required|string|max:255','allocated_amount'=>'nullable|numeric|min:0']);$budget->update(['account_code'=>$d['account_code']??null,'description'=>$d['description'],'allocated_amount'=>$d['allocated_amount']??0]);return back()->with('success','Anggaran berhasil diperbarui.');
 }

 public function destroyBudget(FinanceBudgetLine $budget)
 {
  $this->guard();$this->authorizeBidang($budget->bidang);abort_if($budget->children()->exists()||$budget->transactions()->exists(),422,'Anggaran masih memiliki turunan atau transaksi.');$budget->delete();return back()->with('success','Baris anggaran berhasil dihapus.');
 }

 public function storeTransaction(Request $request)
 {
  $user=$this->guard();$d=$this->transactionData($request);$line=FinanceBudgetLine::findOrFail($d['finance_budget_line_id']);$this->authorizeBidang($line->bidang);abort_if($line->level==='activity'||$line->children()->exists(),422,'Pengeluaran harus menggunakan subkegiatan atau kode rekening paling akhir.');$this->saveEvidence($request,$d);$d['bidang']=$line->bidang;$d['year']=$line->year;$d['type']='other';$d['status']='submitted';$d['created_by']=$user->id;FinanceTransaction::create($d);return back()->with('success','Pengeluaran masuk ke prognosis dan menunggu verifikasi.');
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
  $this->guard();$year=(int)($request->year?:now()->year);$bidang=$this->scopeBidang($request);
  $lines=FinanceBudgetLine::with('parent')->withCount(['children','transactions'])->where('year',$year)->where('bidang',$bidang)->orderByRaw("FIELD(level,'activity','subactivity','account')")->orderBy('account_code')->get();
  $leafLines=$lines->filter(fn($line)=>$line->level!=='activity'&&(int)$line->children_count===0)->values();
  $rows=[array_merge(['Jenis','Kode Rekening','Uraian','Jumlah Anggaran'],['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],['Total Prognosis/Pengeluaran','Sisa Anggaran','Perkiraan Tidak Terserap'])];
  foreach($lines->where('level','activity') as $activity){
   $ordered=collect([$activity]);foreach($lines->where('parent_id',$activity->id) as $sub){$ordered->push($sub);foreach($lines->where('parent_id',$sub->id) as $account)$ordered->push($account);}
   foreach($ordered as $line){$line=$this->summarizeBudgetLine($line,$lines,$leafLines);$rows[]=array_merge([$line->level==='activity'?'Kegiatan':($line->level==='subactivity'?'Subkegiatan':'Rincian'),$line->account_code,$line->description,$line->display_budget],array_values($line->monthly_prognosis),[$line->monthly_total,$line->remaining,$line->unabsorbed]);}
  }
  return Excel::download(new class($rows)implements FromArray,\Maatwebsite\Excel\Concerns\ShouldAutoSize,\Maatwebsite\Excel\Concerns\WithStyles{public function __construct(private array $rows){}public function array():array{return $this->rows;}public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $s):array{$s->freezePane('A2');return[1=>['font'=>['bold'=>true,'color'=>['argb'=>'FFFFFFFF']],'fill'=>['fillType'=>'solid','startColor'=>['argb'=>'FF284B9B']]]];}},'rekap-keuangan-'.\Illuminate\Support\Str::slug($bidang).'-'.$year.'.xlsx');
 } private function summarizeBudgetLine(FinanceBudgetLine $line,$lines,$leafLines):FinanceBudgetLine
 {
  $leafIds=$leafLines->filter(function($leaf)use($line,$lines){if((int)$leaf->id===(int)$line->id)return true;$cursor=$leaf;while($cursor&&$cursor->parent_id){if((int)$cursor->parent_id===(int)$line->id)return true;$cursor=$lines->firstWhere('id',$cursor->parent_id);}return false;})->pluck('id');
  $budget=(float)$leafLines->whereIn('id',$leafIds)->sum('allocated_amount');
  $activeTransactions=FinanceTransaction::whereIn('finance_budget_line_id',$leafIds)->whereIn('status',['submitted','verified','ready','paid'])->get(['transaction_date','net_amount','status']);
  $monthly=array_fill(1,12,0.0);foreach($activeTransactions as $transaction){$month=(int)$transaction->transaction_date->format('n');$monthly[$month]+=(float)$transaction->net_amount;}
  $monthlyTotal=(float)array_sum($monthly);$real=(float)$activeTransactions->where('status','paid')->sum('net_amount');$prog=$monthlyTotal-$real;
  $line->setAttribute('display_budget',$budget);$line->setAttribute('monthly_prognosis',$monthly);$line->setAttribute('monthly_total',$monthlyTotal);$line->setAttribute('realization',$real);$line->setAttribute('prognosis',$prog);$line->setAttribute('remaining',$budget-$monthlyTotal);$line->setAttribute('unabsorbed',max(0,$budget-$monthlyTotal));return $line;
 } private function teacherMissing(Schedule $schedule):array
 {
  $user=$schedule->pengajar;$profile=$user?->pengajar;$checks=['NIP/NIK'=>$user?->nip_nik,'jabatan'=>$user?->jabatan,'instansi'=>$user?->instansi,'NPWP'=>$profile?->npwp,'nomor rekening'=>$profile?->nomor_rekening,'bank'=>$profile?->nama_bank,'nama pemilik rekening'=>$profile?->nama_rekening,'CV'=>$profile?->cv_path,'sertifikat TOT'=>$profile?->sertifikat_path,'surat tugas'=>$profile?->surat_tugas_path,'dokumen materi/RBPMP'=>$schedule->pengajarDocuments];
  return collect($checks)->filter(fn($value)=>blank($value))->keys()->all();
 } private function transactionData(Request $r):array{$d=$r->validate(['finance_budget_line_id'=>'required|exists:finance_budget_lines,id','transaction_date'=>'required|date','description'=>'required|string|max:1000','gross_amount'=>'required|numeric|min:0','note'=>'nullable|string|max:2000']);$d['payee']='Pengeluaran Bidang';$d['tax_amount']=0;$d['net_amount']=$d['gross_amount'];return $d;} private function saveEvidence(Request $r,array &$d):void{if(!$r->hasFile('evidence'))return;$f=$r->file('evidence');$d['evidence_path']=$f->store('finance-evidence/'.date('Y',strtotime($d['transaction_date'])),'public');$d['evidence_name']=$f->getClientOriginalName();unset($d['evidence']);}
 private function validateParent(array $d):void{if($d['level']==='activity'){abort_if(!empty($d['parent_id']),422,'Kegiatan tidak memiliki induk.');return;}abort_if(empty($d['parent_id']),422,'Subkegiatan/kode rekening wajib memiliki induk.');$p=FinanceBudgetLine::findOrFail($d['parent_id']);abort_unless($p->year==(int)$d['year']&&$p->bidang===$d['bidang'],422,'Induk harus pada tahun dan bidang yang sama.');abort_if($d['level']==='subactivity'&&$p->level!=='activity',422,'Induk subkegiatan harus kegiatan.');abort_if($d['level']==='account'&&$p->level!=='subactivity',422,'Induk kode rekening harus subkegiatan.');}
 private function scopeBidang(Request $r):string{$u=Auth::user();$b=$u->role==='superadmin'?($r->bidang?:UserController::$listBidang[0]):$u->bidang;abort_unless(in_array($b,UserController::$listBidang,true),422,'Bidang tidak valid.');return $b;}
 private function guard(){ $u=Auth::user();abort_unless($u&&in_array($u->role,['superadmin','pengelola_keuangan'],true),403);return $u;}
 private function assertBidang(string $b):void{$this->authorizeBidang($b);}
 private function authorizeBidang(string $b):void{$u=Auth::user();abort_unless($u->role==='superadmin'||$u->bidang===$b,403);}
}
