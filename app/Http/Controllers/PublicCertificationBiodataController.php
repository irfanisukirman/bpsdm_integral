<?php
namespace App\Http\Controllers;
use App\Models\CertificationEvent;use App\Models\CertificationParticipant;use App\Models\File;use App\Models\Folder;
use Illuminate\Http\Request;use Illuminate\Support\Facades\Storage;use Illuminate\Support\Str;use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\IOFactory;use PhpOffice\PhpWord\Settings;use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\Process\Process;

class PublicCertificationBiodataController extends Controller {
 public function index(string $token){$event=$this->event($token);return view('certifications.public_lookup',compact('event'));}
 public function verify(Request $r,string $token){$event=$this->event($token);$d=$r->validate(['nip_nik'=>'required|string|max:80']);$nip=trim(ltrim($d['nip_nik'],"'"));$participant=$event->participants()->where('nip_nik',$nip)->first();if(!$participant)return back()->withErrors(['nip_nik'=>'NIP/NIK tidak ditemukan pada kegiatan sertifikasi ini.'])->withInput();return redirect()->route('certifications.public.form',[$event->public_token,$participant->biodata_token]);}
 public function form(string $token,string $participantToken){$event=$this->event($token);$participant=$event->participants()->where('biodata_token',$participantToken)->firstOrFail();return view('certifications.public_form',compact('event','participant'));}
 public function submit(Request $r,string $token,string $participantToken){$event=$this->event($token);$participant=$event->participants()->where('biodata_token',$participantToken)->firstOrFail();$d=$r->validate(['name'=>'required|string|max:255','birth_place_date'=>'required|string|max:255','nip_nik'=>['required','string','max:80',Rule::unique('certification_participants','nip_nik')->where('certification_event_id',$event->id)->ignore($participant->id)],'rank_grade'=>'nullable|string|max:255','position'=>'required|string|max:255','institution'=>'required|string|max:255','religion'=>'required|string|max:50','gender'=>'required|in:Laki-laki,Perempuan','education'=>'required|string|max:255','office_address'=>'required|string|max:2000','phone'=>'required|string|max:30','email'=>'required|email|max:255','trainings'=>'nullable|string|max:5000','signature_data'=>[$participant->signature_path?'nullable':'required','nullable','string','max:2000000']]);
  if(!empty($d['signature_data']))$participant->signature_path=$this->saveSignature($d['signature_data'],$participant);
  $participant->fill(collect($d)->except('signature_data')->all());$participant->nip_nik=trim(ltrim($d['nip_nik'],"'"));$participant->save();
  $old=$participant->biodataFile;$file=$this->generatePdf($event,$participant);if($old&&$old->id!==$file->id){Storage::disk('public')->delete($old->file_path);$old->delete();}$participant->update(['biodata_file_id'=>$file->id,'biodata_submitted_at'=>now()]);
  return redirect()->route('certifications.public.form',[$event->public_token,$participant->biodata_token])->with('success','Biodata berhasil disimpan dan dokumen PDF telah dibuat.');
 }
 private function event(string $token):CertificationEvent{return CertificationEvent::with('type')->where('public_token',$token)->firstOrFail();}
 private function saveSignature(string $data,CertificationParticipant $participant):string{abort_unless(preg_match('/^data:image\/png;base64,(.+)$/',$data,$m),422,'Format tanda tangan tidak valid.');$binary=base64_decode(str_replace(' ','+',$m[1]),true);abort_if($binary===false||strlen($binary)>1500000,422,'Data tanda tangan tidak valid.');$path='certifications/signatures/'.$participant->id.'-'.Str::random(8).'.png';if($participant->signature_path)Storage::disk('public')->delete($participant->signature_path);Storage::disk('public')->put($path,$binary);return $path;}
 private function generatePdf(CertificationEvent $event,CertificationParticipant $participant):File{
  $template=new TemplateProcessor(public_path('templates/template_biodata_peserta_sertifikasi.docx'));$values=['nama_kegiatan'=>$event->title,'nama'=>$participant->name,'tempat_tanggal'=>$participant->birth_place_date,'nip'=>$participant->nip_nik,'pangkat_gol'=>$participant->rank_grade?:'-','jabatan'=>$participant->position,'instansi'=>$participant->institution,'agama'=>$participant->religion,'jenis_kelamin'=>$participant->gender,'pendidikan'=>$participant->education,'alamat_kantor'=>$participant->office_address,'nomor_wa'=>$participant->phone,'email'=>$participant->email,'diklat'=>$participant->trainings?:'-','tanggal_buat'=>now()->translatedFormat('d F Y')];foreach($values as $key=>$value)$template->setValue($key,$this->safe($value));$template->setImageValue('tandatangan',['path'=>Storage::disk('public')->path($participant->signature_path),'width'=>150,'height'=>65,'ratio'=>true]);
  $tempDir=storage_path('app/temp-certifications');if(!is_dir($tempDir))mkdir($tempDir,0775,true);$base=Str::uuid()->toString();$docx=$tempDir.'/'.$base.'.docx';$pdf=$tempDir.'/'.$base.'.pdf';$template->saveAs($docx);
  // Microsoft Word/ActiveX tidak tersedia pada identity web server Windows.
  // DOCX tetap diisi dari template sebagai sumber, kemudian PDF dirender
  // langsung oleh aplikasi agar proses simpan stabil tanpa instalasi Office.
  $signatureData=null;if($participant->signature_path&&Storage::disk('public')->exists($participant->signature_path))$signatureData='data:image/png;base64,'.base64_encode(Storage::disk('public')->get($participant->signature_path));
  $logoPath=public_path('templates/assets/logo-biodata-sertifikasi.png');$logoData=is_file($logoPath)?'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)):null;
  Pdf::loadView('certifications.biodata_pdf',compact('values','signatureData','logoData'))->setPaper([0,0,609.45,935.43],'portrait')->save($pdf);
  if(!is_file($pdf)||filesize($pdf)<1000)throw new \RuntimeException('Dokumen PDF biodata gagal dibuat oleh renderer aplikasi.');
  $folder=Folder::firstOrCreate(['name'=>'Biodata Peserta','parent_id'=>$event->folder_id,'bidang'=>'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan'],['user_id'=>$event->created_by,'is_public'=>false]);$filename='Biodata - '.$this->filename($participant->name).' - '.$this->filename($participant->nip_nik).'.pdf';$path='documents/certification-biodata/'.$base.'.pdf';Storage::disk('public')->put($path,file_get_contents($pdf));@unlink($docx);@unlink($pdf);
  return File::create(['folder_id'=>$folder->id,'display_name'=>$filename,'file_path'=>$path,'file_type'=>'pdf','file_size'=>Storage::disk('public')->size($path),'user_id'=>$event->created_by]);
 }
 private function safe($value):string{return str_replace(['&','<','>'],['&amp;','&lt;','&gt;'],(string)$value);}
 private function filename(string $value):string{return trim(preg_replace('/[^\pL\pN._-]+/u',' ',$value));}
 private function convertDocxToPdf(string $docx,string $pdf):void{
  if(PHP_OS_FAMILY==='Windows'){
   $vbs=dirname($docx).'/convert-'.Str::random(12).'.vbs';$script=<<<'VBS'
On Error Resume Next
Set word = CreateObject("Word.Application")
If Err.Number <> 0 Then
  WScript.StdErr.WriteLine "Tidak dapat membuka Microsoft Word: " & Err.Description
  WScript.Quit 2
End If
word.Visible = False
word.DisplayAlerts = 0
Set document = word.Documents.Open(WScript.Arguments(0), False, True)
If Err.Number <> 0 Then
  WScript.StdErr.WriteLine "Tidak dapat membuka template: " & Err.Description
  word.Quit
  WScript.Quit 3
End If
document.ExportAsFixedFormat WScript.Arguments(1), 17
If Err.Number <> 0 Then
  WScript.StdErr.WriteLine "Tidak dapat membuat PDF: " & Err.Description
  document.Close False
  word.Quit
  WScript.Quit 4
End If
document.Close False
word.Quit
WScript.Quit 0
VBS;
   file_put_contents($vbs,$script);try{$process=new Process(['cscript.exe','//NoLogo',$vbs,realpath($docx),$pdf]);$process->setTimeout(90);$process->run();if($process->isSuccessful()&&is_file($pdf)&&filesize($pdf)>10000)return;throw new \RuntimeException('Konversi Microsoft Word gagal: '.trim($process->getErrorOutput().' '.$process->getOutput()));}finally{@unlink($vbs);}
  }
  Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));$word=IOFactory::load($docx,'Word2007');IOFactory::createWriter($word,'PDF')->save($pdf);
  if(!is_file($pdf)||filesize($pdf)<1000)throw new \RuntimeException('Dokumen PDF biodata gagal dibuat.');
 }
}
