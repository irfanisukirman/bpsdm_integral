<?php
namespace App\Http\Controllers;
use App\Models\EmploymentStatus;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
class WorkforceMasterController extends Controller {
 private function authorizeAdmin(){abort_unless(auth()->user()->role==='superadmin',403);}
 public function index(){ $this->authorizeAdmin(); return view('workforce-masters.index',['institutions'=>Institution::orderBy('sort_order')->orderBy('name')->get(),'statuses'=>EmploymentStatus::orderBy('sort_order')->orderBy('name')->get()]); }
 public function storeInstitution(Request $r){$this->authorizeAdmin();$d=$r->validate(['name'=>'required|max:255|unique:institutions,name','is_internal_bpsdm'=>'nullable|boolean','sort_order'=>'nullable|integer|min:0']);if($r->boolean('is_internal_bpsdm'))Institution::query()->update(['is_internal_bpsdm'=>false]);Institution::create($d+['code'=>$this->institutionCode($d['name']),'is_active'=>true,'is_internal_bpsdm'=>$r->boolean('is_internal_bpsdm'),'sort_order'=>$d['sort_order']??0]);return back()->with('success','Instansi berhasil ditambahkan dengan kode otomatis.');}
 public function updateInstitution(Request $r,Institution $institution){$this->authorizeAdmin();$d=$r->validate(['name'=>['required','max:255',Rule::unique('institutions')->ignore($institution)],'is_active'=>'required|boolean','is_internal_bpsdm'=>'required|boolean','sort_order'=>'nullable|integer|min:0']);if($r->boolean('is_internal_bpsdm'))Institution::where('id','!=',$institution->id)->update(['is_internal_bpsdm'=>false]);$d['code']=$this->institutionCode($d['name'],$institution->id);$institution->update($d);return back()->with('success','Instansi dan kode otomatis berhasil diperbarui.');}
 public function storeStatus(Request $r){$this->authorizeAdmin();$d=$r->validate(['name'=>'required|max:100|unique:employment_statuses,name','code'=>'required|max:50|unique:employment_statuses,code','description'=>'nullable|max:500','sort_order'=>'nullable|integer|min:0']);EmploymentStatus::create($d+['is_active'=>true,'sort_order'=>$d['sort_order']??0]);return back()->with('success','Status kepegawaian ditambahkan.');}
 public function updateStatus(Request $r,EmploymentStatus $status){$this->authorizeAdmin();$d=$r->validate(['name'=>['required','max:100',Rule::unique('employment_statuses')->ignore($status)],'code'=>['required','max:50',Rule::unique('employment_statuses')->ignore($status)],'description'=>'nullable|max:500','is_active'=>'required|boolean','sort_order'=>'nullable|integer|min:0']);$status->update($d);return back()->with('success','Status kepegawaian diperbarui.');}

 public function destroyInstitution(Institution $institution){$this->authorizeAdmin();if($institution->users()->exists()||$institution->is_internal_bpsdm)return back()->with('error','Instansi sudah digunakan atau merupakan instansi internal. Nonaktifkan instansi agar riwayat tetap aman.');$institution->delete();return back()->with('success','Instansi berhasil dihapus.');}
 public function destroyStatus(EmploymentStatus $status){$this->authorizeAdmin();if($status->users()->exists())return back()->with('error','Status sudah digunakan oleh akun. Nonaktifkan status agar riwayat tetap aman.');$status->delete();return back()->with('success','Status kepegawaian berhasil dihapus.');}

 public function institutionTemplate(){
  $this->authorizeAdmin();
  $rows=[['Nama Perangkat Daerah','Urutan','Status Aktif'],['Dinas Pendidikan Provinsi Jawa Barat',10,'Ya'],['Dinas Kesehatan Provinsi Jawa Barat',20,'Ya']];
  return Excel::download(new class($rows) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithStyles {
   public function __construct(private array $rows){}
   public function array():array{return $this->rows;}
   public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet):array{$sheet->freezePane('A2');return[1=>['font'=>['bold'=>true,'color'=>['argb'=>'FFFFFFFF']],'fill'=>['fillType'=>'solid','startColor'=>['argb'=>'FF284B9B']]]];}
  },'template-import-perangkat-daerah.xlsx');
 }

 public function importInstitutions(Request $r){
  $this->authorizeAdmin();$r->validate(['file'=>'required|file|mimes:xlsx,xls|max:10240']);
  $rows=collect(\PhpOffice\PhpSpreadsheet\IOFactory::load($r->file('file')->getRealPath())->getActiveSheet()->toArray());$header=$rows->shift();
  if(!$header)return back()->with('error','File Excel tidak memiliki header. Gunakan template resmi.');
  $keys=collect($header)->map(fn($v)=>Str::slug((string)$v,'_'))->all();$created=0;$updated=0;$skipped=[];
  foreach($rows as $index=>$row){$data=array_combine($keys,array_pad(array_values($row),count($keys),null));$name=trim((string)($data['nama_perangkat_daerah']??''));if($name===''){if(collect($row)->filter(fn($v)=>filled($v))->isNotEmpty())$skipped[]=$index+2;continue;}$active=in_array(Str::lower(trim((string)($data['status_aktif']??'ya'))),['ya','y','1','aktif','true'],true);$sort=max(0,(int)($data['urutan']??0));$institution=Institution::whereRaw('LOWER(name)=?',[Str::lower($name)])->first();if($institution){$institution->update(['name'=>$name,'code'=>$this->institutionCode($name,$institution->id),'sort_order'=>$sort,'is_active'=>$active]);$updated++;}else{Institution::create(['name'=>$name,'code'=>$this->institutionCode($name),'sort_order'=>$sort,'is_active'=>$active,'is_internal_bpsdm'=>false]);$created++;}}
  $message="Import selesai: {$created} ditambahkan, {$updated} diperbarui".($skipped?', '.count($skipped).' baris dilewati (baris '.implode(', ',$skipped).')':'').'.';return back()->with('success',$message);
 }

 private function institutionCode(string $name, ?int $ignoreId=null):string {
  $base=Str::upper(Str::slug($name,'-'));$base=substr($base?:'INSTANSI',0,40);$code=$base;$number=2;
  while(Institution::where('code',$code)->when($ignoreId,fn($q)=>$q->where('id','!=',$ignoreId))->exists()){$suffix='-'.$number++;$code=substr($base,0,50-strlen($suffix)).$suffix;}
  return $code;
 }
}
