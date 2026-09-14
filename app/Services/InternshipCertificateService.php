<?php

namespace App\Services;

use App\Models\InternshipParticipant;
use App\Models\InternshipProgram;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\Process\Process;

class InternshipCertificateService
{
    public const CODES=['nomor_sertifikat','nama_lengkap','kampus_sekolah','tanggal_mulai','tanggal_selesai','predikat'];

    public function validateTemplate(string $path): array
    {
        try {
            $word=IOFactory::load($path,'Word2007');
            if($word->getSections()===[]) throw new \RuntimeException();
            $variables=(new TemplateProcessor($path))->getVariables();
            $unknown=array_values(array_diff($variables,self::CODES));
            $missing=array_values(array_diff(self::CODES,$variables));
            if($unknown!==[]) throw ValidationException::withMessages(['certificate_template'=>'Kode tidak dikenal: '.collect($unknown)->map(fn($x)=>'${'.$x.'}')->join(', ')]);
            if($missing!==[]) throw ValidationException::withMessages(['certificate_template'=>'Template wajib memuat '.collect($missing)->map(fn($x)=>'${'.$x.'}')->join(', ')]);
            return $variables;
        } catch(ValidationException $e){throw $e;} catch(\Throwable $e){
            throw ValidationException::withMessages(['certificate_template'=>'Template DOCX tidak dapat dibaca atau rusak.']);
        }
    }

    public function render(InternshipProgram $program,InternshipParticipant $participant,string $number,string $output): void
    {
        $templatePath=Storage::disk('local')->path($program->certificate_template_path);
        $this->validateTemplate($templatePath);
        $template=new TemplateProcessor($templatePath);
        $template->setValue('nomor_sertifikat',htmlspecialchars($number));
        $template->setValue('nama_lengkap',htmlspecialchars($participant->name));
        $template->setValue('kampus_sekolah',htmlspecialchars($participant->institution));
        $template->setValue('tanggal_mulai',htmlspecialchars($participant->start_date->translatedFormat('d F Y')));
        $template->setValue('tanggal_selesai',htmlspecialchars($participant->end_date->translatedFormat('d F Y')));
        $template->setValue('predikat',htmlspecialchars($participant->final_grade ?: $participant->recommended_grade ?: 'Baik'));
        $working=sys_get_temp_dir().DIRECTORY_SEPARATOR.'integral-intern-cert-'.bin2hex(random_bytes(8));
        if(!mkdir($working,0775,true)&&!is_dir($working)) throw new \RuntimeException('Folder sementara tidak dapat dibuat.');
        $docx=$working.DIRECTORY_SEPARATOR.'certificate.docx';$template->saveAs($docx);
        try {
            $binary=$this->libreOfficeBinary();$profile=str_replace('\\','/',$working.DIRECTORY_SEPARATOR.'profile');
            $process=new Process([$binary,'-env:UserInstallation=file:///'.ltrim($profile,'/'),'--headless','--convert-to','pdf:writer_pdf_Export','--outdir',$working,$docx],$working);
            $process->setEnv($this->libreOfficeEnvironment($binary));$process->setTimeout(120);$process->run();
            $pdf=$working.DIRECTORY_SEPARATOR.'certificate.pdf';
            if(!$process->isSuccessful()||!is_file($pdf)||filesize($pdf)===0) throw new \RuntimeException('LibreOffice gagal membuat PDF: '.trim($process->getErrorOutput().' '.$process->getOutput()));
            if(!is_dir(dirname($output)))mkdir(dirname($output),0775,true);
            if(!copy($pdf,$output))throw new \RuntimeException('PDF sertifikat tidak dapat disimpan.');
        } finally {$this->removeDirectory($working);}
    }

    public function recommendedGrade(InternshipParticipant $participant): string
    {
        $start=$participant->start_date->copy()->startOfDay();$end=$participant->end_date->copy()->startOfDay();$workDays=0;
        for($day=$start->copy();$day->lte($end);$day->addDay())if(!$day->isWeekend())$workDays++;
        $present=$participant->attendances()->whereIn('status',['present','permission','sick'])->count();$lateMinutes=(int)$participant->attendances()->sum('late_minutes');
        $attendanceScore=$workDays>0?min(100,($present/$workDays)*100):0;$score=max(0,$attendanceScore-min(15,$lateMinutes/30));
        return match(true){$score>=90=>'Sangat Baik',$score>=80=>'Baik',$score>=70=>'Cukup',default=>'Kurang'};
    }

    public function createSampleTemplate(string $path): void
    {
        $word=new \PhpOffice\PhpWord\PhpWord();$section=$word->addSection(['orientation'=>'landscape','pageSizeW'=>16838,'pageSizeH'=>11906,'marginTop'=>800,'marginBottom'=>800,'marginLeft'=>1000,'marginRight'=>1000]);
        $section->addText('SERTIFIKAT',['bold'=>true,'size'=>28],['alignment'=>'center','spaceAfter'=>300]);
        $section->addText('Nomor: ${nomor_sertifikat}',['size'=>13],['alignment'=>'center','spaceAfter'=>500]);
        $section->addText('Diberikan kepada',['size'=>13],['alignment'=>'center']);
        $section->addText('${nama_lengkap}',['bold'=>true,'size'=>24],['alignment'=>'center','spaceAfter'=>200]);
        $section->addText('dari ${kampus_sekolah}',['size'=>14],['alignment'=>'center','spaceAfter'=>250]);
        $section->addText('Telah melaksanakan kegiatan Magang/PKL pada tanggal ${tanggal_mulai} sampai dengan ${tanggal_selesai}.',['size'=>13],['alignment'=>'center','spaceAfter'=>200]);
        $section->addText('Dengan predikat',['size'=>13],['alignment'=>'center']);
        $section->addText('${predikat}',['bold'=>true,'size'=>20],['alignment'=>'center']);
        IOFactory::createWriter($word,'Word2007')->save($path);
    }

    private function libreOfficeBinary(): string
    {
        $configured=config('services.libreoffice.binary');$console=$configured&&str_ends_with(strtolower($configured),'soffice.exe')?substr($configured,0,-3).'com':null;
        foreach(array_filter([$console,$configured,'C:\\Program Files\\LibreOffice\\program\\soffice.com','C:\\Program Files\\LibreOffice\\program\\soffice.exe','/usr/bin/libreoffice','/usr/bin/soffice']) as $candidate)if(is_file($candidate)&&is_executable($candidate))return $candidate;
        throw new \RuntimeException('LibreOffice belum tersedia. Atur LIBREOFFICE_BINARY pada .env.');
    }
    private function libreOfficeEnvironment(string $binary): array
    {
        $env=[];foreach(['SystemRoot','WINDIR','USERPROFILE','APPDATA','LOCALAPPDATA','TEMP','TMP','PROGRAMFILES','PROGRAMDATA','COMSPEC'] as $key){$value=getenv($key);if($value!==false&&$value!=='')$env[$key]=$value;}
        $env['PATH']=dirname($binary).PATH_SEPARATOR.(getenv('PATH')?:'');$env['PYTHONHOME']=false;$env['PYTHONPATH']=false;$env['SAL_USE_VCLPLUGIN']='svp';return $env;
    }
    private function removeDirectory(string $dir): void {if(!is_dir($dir))return;foreach(array_diff(scandir($dir)?:[],['.','..']) as $item){$path=$dir.DIRECTORY_SEPARATOR.$item;is_dir($path)?$this->removeDirectory($path):@unlink($path);}@rmdir($dir);}
}
