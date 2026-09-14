<?php

namespace App\Http\Controllers;

use App\Models\ActivityAttendanceForm;
use App\Models\ActivityAttendanceResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublicActivityAttendanceController extends Controller
{
    public function show(string $token)
    {
        $form=ActivityAttendanceForm::where('public_token',$token)->with('questions')->firstOrFail();
        return view('activity-attendance.public-form',compact('form'));
    }

    public function store(Request $request,string $token)
    {
        $form=ActivityAttendanceForm::where('public_token',$token)->with('questions')->firstOrFail();
        abort_unless($form->isOpen(),422,'Presensi belum dibuka atau sudah ditutup.');
        abort_if($request->filled('website'),422,'Permintaan tidak valid.');

        $rules=[];
        $messages=[];
        $attributes=[];
        foreach($form->questions as $question){
            if($question->type==='info')continue;
            $key='answers.'.$question->id;
            $required=$question->is_required?'required':'nullable';
            $extensions = collect(explode(',', $question->allowed_extensions ?: 'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip'))
                ->map(fn ($extension) => strtolower(ltrim(trim($extension), '.')))
                ->filter(fn ($extension) => preg_match('/^[a-z0-9]+$/', $extension))
                ->unique()
                ->implode(',');
            $maxFileSize = $question->max_file_size_kb ?: 5120;
            $maxFileSizeLabel = $maxFileSize >= 1024
                ? rtrim(rtrim(number_format($maxFileSize / 1024, 1), '0'), '.').' MB'
                : $maxFileSize.' KB';
            $rules[$key]=match($question->type){
                'short_text'=>[$required,'string','max:500'],
                'long_text'=>[$required,'string','max:5000'],
                'dropdown','radio'=>[$required,Rule::in($question->options?:[])],
                'checkbox'=>[$required,'array'],
                'file'=>[$required,'file','mimes:'.$extensions,'max:'.$maxFileSize],
                'photo'=>[$required,'image','mimes:jpg,jpeg,png,webp','max:'.$maxFileSize],
                'signature'=>[$required,'string','regex:/^data:image\/png;base64,/'],
                default=>[$required,'string','max:5000'],
            };
            $attributes[$key] = $question->label;
            if($question->is_required)$messages[$key.'.required']=$question->label.' wajib diisi.';
            if(in_array($question->type, ['file', 'photo'], true)) {
                $messages[$key.'.max'] = 'Ukuran file pada '.$question->label.' melebihi batas. Maksimal '.$maxFileSizeLabel.'.';
                $messages[$key.'.uploaded'] = 'File pada '.$question->label.' gagal diunggah. Pastikan ukurannya maksimal '.$maxFileSizeLabel.'.';
                $messages[$key.'.mimes'] = 'Format file pada '.$question->label.' tidak didukung.';
                $messages[$key.'.image'] = $question->label.' harus berupa file gambar.';
            }
        }
        $data=$request->validate($rules,$messages,$attributes);
        $stored=[];
        try{
            $response=DB::transaction(function()use($request,$form,$data,&$stored){
                $response=$form->responses()->create([
                    'response_token'=>(string)Str::uuid(),'submitted_at'=>now(),
                    'ip_hash'=>hash('sha256',(string)$request->ip().config('app.key')),
                    'user_agent'=>Str::limit((string)$request->userAgent(),500,''),
                ]);
                foreach($form->questions as $question){
                    if($question->type==='info')continue;
                    $value=data_get($data,'answers.'.$question->id);
                    $answer=['activity_attendance_question_id'=>$question->id];
                    if(in_array($question->type,['file','photo'],true)&&$request->hasFile('answers.'.$question->id)){
                        $file=$request->file('answers.'.$question->id);
                        $path=$file->store('activity-attendance/'.$form->id.'/'.$response->id,'local');$stored[]=$path;
                        $answer+=['file_path'=>$path,'original_name'=>$file->getClientOriginalName(),'mime_type'=>$file->getMimeType(),'file_size'=>$file->getSize()];
                    }elseif($question->type==='signature'&&$value){
                        $binary=base64_decode(substr($value,strpos($value,',')+1),true);
                        abort_if($binary===false||strlen($binary)>2*1024*1024,422,'Tanda tangan tidak valid atau terlalu besar.');
                        $path='activity-attendance/'.$form->id.'/'.$response->id.'/signature-'.$question->id.'.png';
                        Storage::disk('local')->put($path,$binary);$stored[]=$path;
                        $answer+=['file_path'=>$path,'original_name'=>'tanda-tangan.png','mime_type'=>'image/png','file_size'=>strlen($binary)];
                    }elseif($question->type==='checkbox'){
                        $allowed=collect($question->options?:[]);$selected=collect($value?:[])->filter(fn($v)=>$allowed->contains($v))->values()->all();
                        $answer['value_json']=$selected;
                    }else{$answer['value_text']=is_scalar($value)?(string)$value:null;}
                    $response->answers()->create($answer);
                }
                return $response;
            });
        }catch(\Throwable $e){foreach($stored as $path)Storage::disk('local')->delete($path);throw $e;}
        return redirect()->route('activity-attendance.public.success',[$form->public_token,$response->response_token]);
    }

    public function success(string $token,string $responseToken)
    {
        $form=ActivityAttendanceForm::where('public_token',$token)->firstOrFail();
        $response=$form->responses()->where('response_token',$responseToken)->firstOrFail();
        return view('activity-attendance.public-success',compact('form','response'));
    }
}
