<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\ParticipantCertificate;
use App\Models\Training;
use App\Models\TrainingCertificateSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use ZipArchive;

class TrainingCertificateController extends Controller
{
    public function index(Training $training)
    {
        $this->authorizeTraining($training);
        $setting = TrainingCertificateSetting::where('training_id',$training->id)->first();
        $participants = Participant::with(['user','pasFotoFile'])
            ->where('training_id',$training->id)->where('registration_status','approved')->orderBy('name')->get();
        $certificates = ParticipantCertificate::where('training_id',$training->id)->get()->keyBy('participant_id');
        $preview = $participants->take(5)->values()->map(fn($p,$i)=>$this->formatNumber($setting?->number_format ?: '222.{X}/KPG.03.01.03/BPSDM/{TAHUN}',($setting?->start_sequence ?: 1)+$i,$setting?->issued_at?->year ?: now()->year));
        return view('trainings.certificates.index',compact('training','setting','participants','certificates','preview'));
    }

    public function storeSetting(Request $request, Training $training)
    {
        $this->authorizeTraining($training);
        $data=$request->validate([
            'name'=>'required|string|max:255','number_format'=>['required','string','max:255','regex:/\{X(?::[1-9][0-9]?)?\}/'],
            'start_sequence'=>'required|integer|min:0|max:999999','issued_at'=>'required|date','photo_size'=>'required|in:2x3,3x4','template'=>'nullable|file|mimes:docx|max:10240',
        ],['number_format.regex'=>'Format nomor wajib memuat {X} atau {X:3}.']);
        $setting=TrainingCertificateSetting::firstOrNew(['training_id'=>$training->id]);
        $setting->fill(collect($data)->except('template')->all()+['created_by'=>Auth::id()]);
        if($request->hasFile('template')){
            if($setting->template_path)Storage::disk('local')->delete($setting->template_path);
            $setting->template_path=$request->file('template')->store('certificate-templates','local');
        }
        $setting->save();
        return back()->with('success','Pengaturan dan template sertifikat berhasil disimpan.');
    }

    public function downloadTemplate(Training $training)
    {
        $this->authorizeTraining($training);
        $word=new PhpWord();$section=$word->addSection(['orientation'=>'landscape']);
        $section->addText('TEMPLATE SERTIFIKAT PELATIHAN',['bold'=>true,'size'=>20],['alignment'=>'center']);
        $section->addText('Nomor: ${nomor_sertifikat}',['size'=>12],['alignment'=>'center']);
        $section->addTextBreak();$section->addText('Diberikan kepada',['size'=>12],['alignment'=>'center']);
        $section->addText('${nama}',['bold'=>true,'size'=>24],['alignment'=>'center']);
        $section->addText('NIP/NIK: ${nip_nik}',['size'=>12],['alignment'=>'center']);
        $section->addText('${jabatan} - ${instansi}',['size'=>12],['alignment'=>'center']);
        $section->addText('Sebagai peserta ${nama_pelatihan} pada ${tanggal_mulai} s.d. ${tanggal_selesai}.',['size'=>12],['alignment'=>'center']);
        $section->addTextBreak();$section->addText('${foto}',['italic'=>true],['alignment'=>'center']);
        $section->addPageBreak();$section->addText('PANDUAN KODE TEMPLATE',['bold'=>true,'size'=>16]);
        $table=$section->addTable(['borderSize'=>6,'cellMargin'=>100]);
        foreach([['Kode','Data'],['${nama}','Nama lengkap peserta'],['${nip_nik}','NIP/NIK peserta'],['${jabatan}','Jabatan peserta'],['${instansi}','Instansi peserta'],['${foto}','Pas foto peserta'],['${nomor_sertifikat}','Nomor sertifikat permanen'],['${nama_pelatihan}','Nama pelatihan'],['${tanggal_mulai}','Tanggal mulai'],['${tanggal_selesai}','Tanggal selesai'],['${tanggal_sertifikat}','Tanggal penerbitan']] as $row){$table->addRow();$table->addCell(3500)->addText($row[0]);$table->addCell(6500)->addText($row[1]);}
        $path=tempnam(sys_get_temp_dir(),'cert-template-').'.docx';IOFactory::createWriter($word,'Word2007')->save($path);
        return response()->download($path,'template_sertifikat_'.$training->id.'.docx')->deleteFileAfterSend(true);
    }

    public function generate(Request $request, Training $training)
    {
        $this->authorizeTraining($training);
        $setting=TrainingCertificateSetting::where('training_id',$training->id)->firstOrFail();
        abort_unless($setting->template_path&&Storage::disk('local')->exists($setting->template_path),422,'Template DOCX belum diunggah.');
        $ids=collect($request->input('participant_ids',[]))->map(fn($id)=>(int)$id)->filter();
        $query=Participant::with(['user','pasFotoFile'])->where('training_id',$training->id)->where('registration_status','approved');
        if($ids->isNotEmpty())$query->whereIn('id',$ids);
        $participants=$query->orderBy('name')->get();abort_if($participants->isEmpty(),422,'Tidak ada peserta yang dipilih.');
        Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
        Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));
        $zipPath=tempnam(sys_get_temp_dir(),'certificate-bundle-');$zip=new ZipArchive();$zip->open($zipPath,ZipArchive::OVERWRITE);
        $temporaryFiles=[];
        DB::transaction(function()use($participants,$training,$setting,$zip,&$temporaryFiles){
            $next=max((int)$setting->start_sequence,(int)ParticipantCertificate::where('training_id',$training->id)->max('sequence_number')+1);
            foreach($participants as $participant){
                abort_if(blank($participant->nip_nik),422,'NIP/NIK peserta '.$participant->name.' belum tersedia.');
                $certificate=ParticipantCertificate::firstOrNew(['training_id'=>$training->id,'participant_id'=>$participant->id]);
                if(!$certificate->exists){$certificate->fill(['training_certificate_setting_id'=>$setting->id,'sequence_number'=>$next,'certificate_number'=>$this->formatNumber($setting->number_format,$next,$setting->issued_at->year)]);$next++;}
                $template=new TemplateProcessor(Storage::disk('local')->path($setting->template_path));$vars=$template->getVariables();
                $values=['nama'=>$participant->name,'nip_nik'=>$participant->nip_nik,'jabatan'=>$participant->jabatan?:$participant->user?->jabatan?:'-','instansi'=>$participant->instansi?:$participant->user?->instansi?:'-','nomor_sertifikat'=>$certificate->certificate_number,'nama_pelatihan'=>$training->nama_pelatihan,'tanggal_mulai'=>$training->tgl_mulai?\Carbon\Carbon::parse($training->tgl_mulai)->translatedFormat('d F Y'):'-','tanggal_selesai'=>$training->tgl_selesai?\Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d F Y'):'-','tanggal_sertifikat'=>$setting->issued_at->translatedFormat('d F Y')];
                foreach($values as $key=>$value)if(in_array($key,$vars,true))$template->setValue($key,htmlspecialchars((string)$value));
                if(in_array('foto',$vars,true)){if($participant->pasFotoFile&&Storage::disk('public')->exists($participant->pasFotoFile->file_path)){$photoSize=$setting->photo_size==='2x3'?['width'=>76,'height'=>113]:['width'=>113,'height'=>151];$template->setImageValue('foto',['path'=>Storage::disk('public')->path($participant->pasFotoFile->file_path),'width'=>$photoSize['width'],'height'=>$photoSize['height'],'ratio'=>false]);}else $template->setValue('foto','Foto belum tersedia');}
                $safeNip=preg_replace('/[^A-Za-z0-9_-]/','',(string)$participant->nip_nik);
                $tmpDocx=tempnam(sys_get_temp_dir(),'certificate-').'.docx';$tmpPdf=tempnam(sys_get_temp_dir(),'certificate-').'.pdf';
                $template->saveAs($tmpDocx);$temporaryFiles[]=$tmpDocx;$temporaryFiles[]=$tmpPdf;
                try{$document=IOFactory::load($tmpDocx,'Word2007');IOFactory::createWriter($document,'PDF')->save($tmpPdf);}catch(\Throwable $exception){throw new \RuntimeException('Template gagal dikonversi ke PDF untuk peserta '.$participant->name.'. Sederhanakan elemen Word atau pasang LibreOffice pada server.',0,$exception);}
                abort_unless(is_file($tmpPdf)&&filesize($tmpPdf)>0,422,'PDF gagal dibuat untuk peserta '.$participant->name.'.');
                $stored='certificates/generated/'.$training->id.'/'.$safeNip.'.pdf';Storage::disk('local')->put($stored,file_get_contents($tmpPdf));$zip->addFile($tmpPdf,$safeNip.'.pdf');
                $certificate->fill(['generated_file_path'=>$stored,'generated_at'=>now()])->save();
            }
        });$zip->close();foreach($temporaryFiles as $temporaryFile)@unlink($temporaryFile);
        return response()->download($zipPath,'BUNDEL_SERTIFIKAT_'.Str::slug($training->nama_pelatihan,'_').'.zip')->deleteFileAfterSend(true);
    }

    public function uploadFinal(Request $request, ParticipantCertificate $certificate)
    {
        $certificate->load('training');$this->authorizeTraining($certificate->training);$request->validate(['certificate_file'=>'required|file|mimes:pdf|max:10240']);
        if($certificate->final_file_path)Storage::disk('local')->delete($certificate->final_file_path);
        $path=$request->file('certificate_file')->storeAs('certificates/final/'.$certificate->training_id,$this->safeNip($certificate->participant->nip_nik).'.pdf','local');
        $certificate->update(['final_file_path'=>$path,'uploaded_at'=>now(),'downloaded_at'=>null,'uploaded_by'=>Auth::id()]);return back()->with('success','Sertifikat final berhasil diunggah dan peserta akan menerima notifikasi.');
    }

    public function uploadFinalZip(Request $request, Training $training)
    {
        $this->authorizeTraining($training);$request->validate(['certificate_zip'=>'required|file|mimes:zip|max:102400']);
        $zip=new ZipArchive();abort_unless($zip->open($request->file('certificate_zip')->getRealPath())===true,422,'ZIP tidak dapat dibaca.');
        $participants=Participant::where('training_id',$training->id)->get()->keyBy(fn($p)=>$this->safeNip($p->nip_nik));$matched=0;$unmatched=[];
        for($i=0;$i<$zip->numFiles;$i++){ $name=$zip->getNameIndex($i);if(strtolower(pathinfo($name,PATHINFO_EXTENSION))!=='pdf')continue;$nip=$this->safeNip(pathinfo($name,PATHINFO_FILENAME));$participant=$participants->get($nip);$certificate=$participant?ParticipantCertificate::where('training_id',$training->id)->where('participant_id',$participant->id)->first():null;if(!$certificate){$unmatched[]=$name;continue;}$stream=$zip->getStream($name);$content=stream_get_contents($stream);fclose($stream);$path='certificates/final/'.$training->id.'/'.$nip.'.pdf';Storage::disk('local')->put($path,$content);$certificate->update(['final_file_path'=>$path,'uploaded_at'=>now(),'downloaded_at'=>null,'uploaded_by'=>Auth::id()]);$matched++;} $zip->close();
        return back()->with('success',$matched.' sertifikat cocok dan tersimpan.'.(count($unmatched)?' '.count($unmatched).' file tidak cocok: '.implode(', ',array_slice($unmatched,0,5)):''));
    }

    public function downloadFinal(ParticipantCertificate $certificate)
    {
        abort_unless($certificate->final_file_path&&Storage::disk('local')->exists($certificate->final_file_path),404);
        $user=Auth::user();if($user->role==='participant'){abort_unless(Participant::whereKey($certificate->participant_id)->where(function($query)use($user){$query->where('user_id',$user->id);if(filled($user->nip_nik))$query->orWhere('nip_nik',$user->nip_nik);})->exists(),403);$certificate->update(['downloaded_at'=>now()]);}else{$certificate->load('training');$this->authorizeTraining($certificate->training);}
        return Storage::disk('local')->download($certificate->final_file_path,$this->safeNip($certificate->participant->nip_nik).'.pdf');
    }

    private function formatNumber(string $format,int $sequence,int $year):string{$result=preg_replace_callback('/\{X(?::([1-9][0-9]?))?\}/',fn($m)=>isset($m[1])?str_pad((string)$sequence,(int)$m[1],'0',STR_PAD_LEFT):(string)$sequence,$format);return str_replace(['{TAHUN}','{BULAN}','{BULAN_ROMAWI}'],[(string)$year,str_pad((string)now()->month,2,'0',STR_PAD_LEFT),[1=>'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][now()->month]],$result);}
    private function safeNip($nip):string{return preg_replace('/[^A-Za-z0-9_-]/','',(string)$nip);}
    private function authorizeTraining(Training $training):void{$user=Auth::user();abort_unless($user->role==='superadmin'||($user->role==='admin_bidang'&&$user->bidang===$training->bidang),403);}
}
