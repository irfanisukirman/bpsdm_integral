<?php
namespace App\Http\Middleware;

use App\Models\{Training,Schedule};
use App\Services\TrainingDocumentArchiveService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\{BinaryFileResponse,StreamedResponse};

class ArchiveTrainingDocuments
{
 public function handle(Request $request, Closure $next)
 {
  $response=$next($request);
  if($response->getStatusCode()>=400 || !$request->route())return $response;
  $training=$this->training($request);
  if(!$training)return $response;
  $archive=app(TrainingDocumentArchiveService::class);
  if(($request->allFiles() || $request->routeIs('training-certificates.generate')) && !$request->session()->has('errors')){
   try{
    foreach(\Illuminate\Support\Arr::flatten($request->allFiles()) as $upload)if($upload instanceof \Illuminate\Http\UploadedFile && $upload->isValid())$archive->store($training,'BERKAS UNGGAHAN',$upload->getClientOriginalName(),file_get_contents($upload->getRealPath()),$upload->getClientOriginalExtension());
    $archive->sync($training);
   }catch(\Throwable $e){report($e);}
  }
  $header=$response->headers->get('Content-Disposition','');
  if(!str_contains(strtolower($header),'attachment'))return $response;
  // Public report snapshots and document archive downloads must not create copies.
  if($request->routeIs('post-evaluation.public*','documents.*','training-activity-report.versions.download'))return $response;
  $disposition=\Symfony\Component\HttpFoundation\HeaderUtils::split($header,';=');
  $parts=\Symfony\Component\HttpFoundation\HeaderUtils::combine($disposition);
  $name=$parts['filename'] ?? 'Dokumen-'.$training->id;
  if(isset($parts['filename*']))$name=rawurldecode(preg_replace("/^UTF-8''/i",'',$parts['filename*']));
  $store=function(string $content)use($archive,$training,$name){if($content==='')return;try{$archive->store($training,'HASIL UNDUHAN',$name,$content);}catch(\Throwable $e){report($e);}};
  if($response instanceof BinaryFileResponse){$store(file_get_contents($response->getFile()->getPathname()));}
  elseif($response instanceof StreamedResponse){
   $callback=$response->getCallback();
   $response->setCallback(function()use($callback,$store){ob_start();try{$callback();$content=ob_get_clean();}catch(\Throwable $e){ob_end_clean();throw $e;}$store($content);echo $content;});
  }else{$store((string)$response->getContent());}
  return $response;
 }
 private function training(Request $request): ?Training
 {
  foreach($request->route()->parameters() as $value){
   if($value instanceof Training)return $value;
   if($value instanceof \Illuminate\Database\Eloquent\Model && $value->getAttribute('training_id'))return Training::find($value->training_id);
  }
  $uri=$request->route()->uri();
  if(preg_match('#^(?:trainings|monitoring)/#',$uri))return Training::find($request->route('id') ?: $request->route('training'));
  if($request->routeIs('attendance.pdf.daily','attendance.pdf.all','attendance.excel.all'))return Training::find($request->route('id'));
  if(str_contains($uri,'attendance') || str_contains($uri,'absensi')){
   $scheduleId=$request->route('schedule_id');
   if($scheduleId)return Schedule::find($scheduleId)?->training;
  }
  return null;
 }
}
