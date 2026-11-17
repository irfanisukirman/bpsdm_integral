<?php
namespace App\Services;

use App\Models\{Training,Folder,File};
use Illuminate\Support\Facades\{Auth,DB,Storage};
use Illuminate\Support\Str;

class TrainingDocumentArchiveService
{
 public function root(Training $training): Folder
 {
  return Folder::firstOrCreate(['training_id'=>$training->id,'parent_id'=>null],['name'=>$training->nama_pelatihan.' - Angkatan '.$training->angkatan,'bidang'=>$training->bidang,'user_id'=>$training->created_by ?: Auth::id(),'is_public'=>false]);
 }
 public function store(Training $training,string $category,string $name,string $content,?string $extension=null): File
 {
  return DB::transaction(function() use($training,$category,$name,$content,$extension){
   Training::whereKey($training->id)->lockForUpdate()->firstOrFail();
   $root=$this->root($training);$hash=hash('sha256',$content);$name=mb_substr(basename(str_replace('\\','/',$name)),0,255);
   $existing=File::whereHas('folder',fn($q)=>$q->where('training_id',$training->id))->where('display_name',$name)->where('content_hash',$hash)->first();
   if($existing && Storage::disk($existing->storage_disk ?: 'public')->exists($existing->file_path))return $existing;
   $folder=Folder::firstOrCreate(['training_id'=>$training->id,'parent_id'=>$root->id,'name'=>Str::upper($category)],['bidang'=>$training->bidang,'user_id'=>$root->user_id,'is_public'=>false]);
   $extension=strtolower($extension ?: pathinfo($name,PATHINFO_EXTENSION));
   $path='documents/trainings/'.$training->id.'/'.Str::uuid().($extension?'.'.$extension:'');
   if(!Storage::disk('local')->put($path,$content))throw new \RuntimeException('Arsip dokumen pelatihan gagal disimpan.');
   try{return File::create(['folder_id'=>$folder->id,'display_name'=>$name,'file_path'=>$path,'file_type'=>$extension,'file_size'=>strlen($content),'user_id'=>$root->user_id,'storage_disk'=>'local','content_hash'=>$hash]);}
   catch(\Throwable $e){Storage::disk('local')->delete($path);throw $e;}
  });
 }
 public function copy(Training $training,string $category,string $path,string $disk='local',?string $name=null): void
 {
  if($path && Storage::disk($disk)->exists($path))$this->store($training,$category,$name ?: basename($path),Storage::disk($disk)->get($path));
 }
 public function sync(Training $training): void
 {
  $this->root($training);
  foreach(\App\Models\TrainingActivityDocumentation::where('training_id',$training->id)->get() as $photo)$this->copy($training,'DOKUMENTASI KEGIATAN',$photo->file_path,'local',$photo->id.' - '.$photo->title.'.'.pathinfo($photo->file_path,PATHINFO_EXTENSION));
  $report=\App\Models\TrainingActivityReport::where('training_id',$training->id)->first();
  if($report){
   if($report->template_path)$this->copy($training,'TEMPLATE LAPORAN',$report->template_path);
   foreach($report->versions as $version)foreach(['docx_path','pdf_path'] as $key)if($version->$key)$this->copy($training,'LAPORAN PENYELENGGARAAN PELATIHAN',$version->$key);
  }
  foreach(\App\Models\MonitoringResult::where('training_id',$training->id)->whereNotNull('evidence_file')->get() as $row)$this->copy($training,'BUKTI TINDAK LANJUT',$row->evidence_file,'public');
  $evidence=DB::table('post_evaluation_evidence')->join('post_evaluation_interviews','interview_id','=','post_evaluation_interviews.id')->where('post_evaluation_interviews.training_id',$training->id)->select('post_evaluation_evidence.*')->get();
  foreach($evidence as $row)$this->copy($training,'DOKUMEN PROPER DAN EVIDENCE',$row->path,'local',$row->id.' - '.$row->original_name);
  foreach(\App\Models\TrainingCertificateSetting::where('training_id',$training->id)->whereNotNull('template_path')->get() as $setting)$this->copy($training,'TEMPLATE SERTIFIKAT',$setting->template_path);
  foreach($training->schedules()->with('assetLoanRequest')->get() as $schedule)if($schedule->assetLoanRequest?->letter_path)$this->copy($training,'SURAT PEMINJAMAN SARANA',$schedule->assetLoanRequest->letter_path);
  foreach(\App\Models\ParticipantCertificate::where('training_id',$training->id)->get() as $row)foreach(['generated_file_path','final_file_path'] as $key)if($row->$key)$this->copy($training,'SERTIFIKAT PESERTA',$row->$key);
 }
}
