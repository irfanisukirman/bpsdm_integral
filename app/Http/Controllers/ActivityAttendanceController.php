<?php

namespace App\Http\Controllers;

use App\Models\ActivityAttendanceAnswer;
use App\Models\ActivityAttendanceForm;
use App\Models\ActivityAttendanceQuestion;
use App\Models\ActivityAttendanceResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\QrCode\Output\Png;
use Mpdf\QrCode\QrCode;
use ZipArchive;

class ActivityAttendanceController extends Controller
{
    private const TYPES = ['short_text','long_text','dropdown','radio','checkbox','file','photo','signature','info'];

    public function index(Request $request)
    {
        $this->guardRole();
        $forms = $this->scopedForms()->withCount(['questions','responses'])
            ->when($request->filled('q'), fn($q)=>$q->where(fn($x)=>$x->where('title','like','%'.$request->q.'%')->orWhere('subtitle','like','%'.$request->q.'%')))
            ->when($request->filled('status'), fn($q)=>$q->where('status',$request->status))
            ->latest()->paginate(12)->withQueryString();
        return view('activity-attendance.index', compact('forms'));
    }

    public function create()
    {
        $this->guardRole();
        return view('activity-attendance.form', ['form'=>new ActivityAttendanceForm()]);
    }

    public function store(Request $request)
    {
        $this->guardRole();
        $data=$this->validateForm($request);
        $data['public_token']=(string)Str::uuid();
        $data['bidang']=Auth::user()->role==='superadmin' ? ($data['bidang'] ?: Auth::user()->bidang) : Auth::user()->bidang;
        $data['created_by']=Auth::id();
        $form=ActivityAttendanceForm::create($data);
        return redirect()->route('activity-attendance.edit',$form)->with('success','Form berhasil dibuat. Tambahkan pertanyaan sebelum dibagikan.');
    }

    public function edit(ActivityAttendanceForm $activityAttendance)
    {
        $this->authorizeForm($activityAttendance);
        $activityAttendance->load('questions')->loadCount('responses');
        $fields = self::TYPES;
        return view('activity-attendance.edit', ['form'=>$activityAttendance,'fields'=>$fields]);
    }

    public function update(Request $request, ActivityAttendanceForm $activityAttendance)
    {
        $this->authorizeForm($activityAttendance);
        $data=$this->validateForm($request);
        if(Auth::user()->role!=='superadmin')$data['bidang']=Auth::user()->bidang;
        $activityAttendance->update($data);
        return back()->with('success','Pengaturan form berhasil diperbarui.');
    }

    public function destroy(ActivityAttendanceForm $activityAttendance)
    {
        $this->authorizeForm($activityAttendance);
        foreach($activityAttendance->responses()->with('answers')->get() as $response){
            foreach($response->answers as $answer)if($answer->file_path)Storage::disk('local')->delete($answer->file_path);
        }
        $activityAttendance->delete();
        return redirect()->route('activity-attendance.index')->with('success','Form dan seluruh responsnya berhasil dihapus.');
    }

    public function storeQuestion(Request $request, ActivityAttendanceForm $activityAttendance)
    {
        $this->authorizeForm($activityAttendance);
        $data=$this->validateQuestion($request);
        $data['options']=$this->questionOptions($request,$data['type']);
        unset($data['option_source'], $data['options_text']);
        $data['sort_order']=$data['sort_order']??((int)$activityAttendance->questions()->max('sort_order')+1);
        $activityAttendance->questions()->create($data);
        return back()->with('success','Pertanyaan berhasil ditambahkan.');
    }

    public function updateQuestion(Request $request, ActivityAttendanceQuestion $question)
    {
        $this->authorizeForm($question->form);
        $data=$this->validateQuestion($request);
        $data['options']=$this->questionOptions($request,$data['type']);
        unset($data['option_source'], $data['options_text']);
        $question->update($data);
        return back()->with('success','Pertanyaan berhasil diperbarui.');
    }

    public function reorderQuestions(Request $request, ActivityAttendanceForm $activityAttendance)
    {
        $this->authorizeForm($activityAttendance);
        $data = $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'required|integer|distinct',
        ]);

        $submittedIds = collect($data['question_ids'])->map(fn ($id) => (int) $id)->values();
        $existingIds = $activityAttendance->questions()->pluck('id')->map(fn ($id) => (int) $id)->values();
        abort_unless(
            $submittedIds->count() === $existingIds->count()
            && $submittedIds->diff($existingIds)->isEmpty()
            && $existingIds->diff($submittedIds)->isEmpty(),
            422,
            'Daftar pertanyaan tidak sesuai dengan formulir.'
        );

        DB::transaction(function () use ($activityAttendance, $submittedIds) {
            foreach ($submittedIds as $index => $questionId) {
                $activityAttendance->questions()->whereKey($questionId)->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json(['message' => 'Urutan pertanyaan berhasil disimpan.']);
    }
    public function destroyQuestion(ActivityAttendanceQuestion $question)
    {
        $this->authorizeForm($question->form);
        abort_if($question->answers()->exists(),422,'Pertanyaan yang sudah memiliki jawaban tidak dapat dihapus.');
        $question->delete();
        return back()->with('success','Pertanyaan berhasil dihapus.');
    }

    public function responses(Request $request, ActivityAttendanceForm $activityAttendance)
    {
        $this->authorizeForm($activityAttendance);

        $responses = $activityAttendance->responses()->with('answers.question')
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->whereHas('answers', fn ($answer) => $answer->where('value_text', 'like', '%'.$request->q.'%'));
            })
            ->latest('submitted_at')
            ->paginate(20)
            ->withQueryString();

        $questions = $activityAttendance->questions()->with('answers')->get();
        $totalResponses = $activityAttendance->responses()->count();
        $totalAttachments = ActivityAttendanceAnswer::query()
            ->whereHas('response', fn ($query) => $query->where('activity_attendance_form_id', $activityAttendance->id))
            ->whereNotNull('file_path')
            ->count();
        $requiredQuestions = $questions->where('is_required', true)->where('type', '!=', 'info');
        $requiredAnswerCount = ActivityAttendanceAnswer::query()
            ->whereHas('response', fn ($query) => $query->where('activity_attendance_form_id', $activityAttendance->id))
            ->whereIn('activity_attendance_question_id', $requiredQuestions->pluck('id'))
            ->where(function ($query) {
                $query->whereNotNull('value_text')->orWhereNotNull('value_json')->orWhereNotNull('file_path');
            })
            ->count();
        $requiredAnswerTarget = $totalResponses * $requiredQuestions->count();

        $stats = [
            'responses' => $totalResponses,
            'today' => $activityAttendance->responses()->whereDate('submitted_at', today())->count(),
            'attachments' => $totalAttachments,
            'completion' => $requiredAnswerTarget > 0 ? round(($requiredAnswerCount / $requiredAnswerTarget) * 100, 1) : ($totalResponses > 0 ? 100 : 0),
        ];

        $trend = $activityAttendance->responses()
            ->selectRaw('DATE(submitted_at) as response_date, COUNT(*) as total')
            ->groupBy('response_date')
            ->orderBy('response_date')
            ->get()
            ->map(fn ($item) => [
                'label' => \Carbon\Carbon::parse($item->response_date)->translatedFormat('d M'),
                'value' => (int) $item->total,
            ]);

        $charts = $questions->whereIn('type', ['dropdown', 'radio', 'checkbox'])->map(function ($question) {
            $counts = collect($question->options ?: [])->mapWithKeys(fn ($option) => [(string) $option => 0]);
            foreach ($question->answers as $answer) {
                $values = $answer->value_json ?: [$answer->value_text];
                foreach ((array) $values as $value) {
                    if (filled($value)) {
                        $counts[(string) $value] = ($counts[(string) $value] ?? 0) + 1;
                    }
                }
            }

            return [
                'question' => $question,
                'labels' => $counts->keys()->values(),
                'values' => $counts->values(),
                'total' => (int) $counts->sum(),
            ];
        })->values();

        return view('activity-attendance.responses', compact('activityAttendance', 'responses', 'charts', 'stats', 'trend'));
    }
    public function response(ActivityAttendanceResponse $response)
    {
        $response->load(['form','answers.question']);
        $this->authorizeForm($response->form);
        return view('activity-attendance.response-show',compact('response'));
    }

    public function download(ActivityAttendanceAnswer $answer)
    {
        $answer->load('response.form');
        $this->authorizeForm($answer->response->form);
        abort_unless($answer->file_path&&Storage::disk('local')->exists($answer->file_path),404);
        return Storage::disk('local')->download($answer->file_path,$answer->original_name?:'lampiran');
    }

    public function duplicate(ActivityAttendanceForm $activityAttendance)
    {
        $this->authorizeForm($activityAttendance);
        $copy = $activityAttendance->replicate(['public_token','status']);
        $copy->public_token = (string) Str::uuid();
        $copy->title = $activityAttendance->title.' - Salinan';
        $copy->status = 'draft';
        $copy->created_by = Auth::id();
        $copy->save();
        foreach ($activityAttendance->questions()->get() as $question) $copy->questions()->create($question->only([
            'label','help_text','type','options','is_required','sort_order','max_file_size_kb','allowed_extensions'
        ]));
        return redirect()->route('activity-attendance.edit',$copy)->with('success','Form berhasil diduplikasi sebagai draft dengan link publik baru.');
    }

    public function qrCode(ActivityAttendanceForm $activityAttendance)
    {
        $this->authorizeForm($activityAttendance);
        $png = (new Png())->output(new QrCode($activityAttendance->public_url), 700);
        return response($png,200,[
            'Content-Type'=>'image/png',
            'Content-Disposition'=>'attachment; filename="qr-presensi-'.Str::slug($activityAttendance->title).'.png"',
        ]);
    }

    public function exportExcel(ActivityAttendanceForm $activityAttendance)
    {
        $this->authorizeForm($activityAttendance);
        $activityAttendance->load(['questions','responses.answers']);
        $questions = $activityAttendance->questions;
        $rows = [[
            'No.','Waktu Mengisi','Kode Respons',...$questions->pluck('label')->all()
        ]];
        foreach ($activityAttendance->responses->sortBy('submitted_at')->values() as $index => $response) {
            $answers = $response->answers->keyBy('activity_attendance_question_id');
            $row = [$index+1,$response->submitted_at?->format('d-m-Y H:i:s'),$response->response_token];
            foreach ($questions as $question) {
                $row[] = $this->excelAnswerValue($answers->get($question->id), $question);
            }
            $rows[] = $row;
        }
        $export = new class($rows) implements FromArray {
            public function __construct(private array $rows){}
            public function array():array{return $this->rows;}
        };
        return Excel::download($export,'rekap-presensi-'.Str::slug($activityAttendance->title).'.xlsx');
    }

    public function exportPdf(ActivityAttendanceForm $activityAttendance)
    {
        $this->authorizeForm($activityAttendance);
        $activityAttendance->load(['questions','responses.answers']);
        $questions = $activityAttendance->questions;
        $responses = $activityAttendance->responses->sortBy('submitted_at')->values();
        $signatureImages = [];
        foreach ($responses->flatMap->answers as $answer) {
            if ($answer->question?->type !== 'signature' || !$answer->file_path || !Storage::disk('local')->exists($answer->file_path)) {
                continue;
            }

            $mime = $answer->mime_type ?: Storage::disk('local')->mimeType($answer->file_path) ?: 'image/png';
            $signatureImages[$answer->id] = 'data:'.$mime.';base64,'.base64_encode(Storage::disk('local')->get($answer->file_path));
        }

        return Pdf::loadView('activity-attendance.pdf', compact('activityAttendance', 'questions', 'responses', 'signatureImages'))
            ->setPaper('a4','landscape')->download('rekap-presensi-'.Str::slug($activityAttendance->title).'.pdf');
    }

    public function downloadAttachments(ActivityAttendanceForm $activityAttendance)
    {
        $this->authorizeForm($activityAttendance);
        $activityAttendance->load(['responses.answers.question']);
        $answers = $activityAttendance->responses->flatMap->answers->filter(fn ($answer) => $answer->file_path && Storage::disk('local')->exists($answer->file_path));
        abort_if($answers->isEmpty(),422,'Belum ada lampiran atau tanda tangan untuk diunduh.');
        $path = tempnam(sys_get_temp_dir(),'presensi-zip-');
        $zip = new ZipArchive();
        abort_unless($zip->open($path,ZipArchive::CREATE|ZipArchive::OVERWRITE)===true,500,'ZIP tidak dapat dibuat.');
        $used=[];
        foreach ($answers as $answer) {
            $response = $answer->response;
            $base = str_pad((string)$response->id,5,'0',STR_PAD_LEFT).'-'.Str::slug($answer->question?->label ?: 'lampiran');
            $extension = pathinfo($answer->original_name ?: $answer->file_path,PATHINFO_EXTENSION) ?: 'bin';
            $name=$base.'.'.$extension;$counter=2;
            while(isset($used[$name]))$name=$base.'-'.$counter++.'.'.$extension;
            $used[$name]=true;
            $zip->addFile(Storage::disk('local')->path($answer->file_path),$name);
        }
        $zip->close();
        return response()->download($path,'lampiran-presensi-'.Str::slug($activityAttendance->title).'.zip')->deleteFileAfterSend(true);
    }

    private function excelAnswerValue($answer, ActivityAttendanceQuestion $question): string
    {
        if ($answer?->file_path) {
            $url = route('activity-attendance.answers.download', $answer);
            $label = match ($question->type) {
                'signature' => 'Buka tanda tangan',
                'photo' => 'Buka foto',
                default => 'Buka dokumen',
            };

            return '=HYPERLINK("'.str_replace('"', '""', $url).'","'.$label.'")';
        }

        return $this->answerValue($answer);
    }

    private function answerValue($answer): string
    {
        if (!$answer) return '';
        if ($answer->value_json) return collect($answer->value_json)->join(', ');
        if ($answer->file_path) return $answer->original_name ?: 'Lampiran';
        $value = (string) $answer->value_text;
        return preg_match('/^[=+\-@]/',$value) ? "'".$value : $value;
    }
    private function validateForm(Request $request):array
    {
        return $request->validate([
            'title'=>'required|string|max:255','subtitle'=>'nullable|string|max:5000','location'=>'nullable|string|max:255',
            'bidang'=>'nullable|string|max:255','status'=>['required',Rule::in(['draft','open','closed','archived'])],
            'opens_at'=>'nullable|date','closes_at'=>'nullable|date|after_or_equal:opens_at','confirmation_message'=>'nullable|string|max:2000',
        ]);
    }

    private function validateQuestion(Request $request):array
    {
        return $request->validate([
            'label'=>'required|string|max:2000','help_text'=>'nullable|string|max:2000','type'=>['required',Rule::in(self::TYPES)],
            'is_required'=>'nullable|boolean','sort_order'=>'nullable|integer|min:0|max:10000',
            'option_source'=>'nullable|in:manual,users','options_text'=>'nullable|string|max:20000',
            'max_file_size_kb'=>'nullable|integer|min:100|max:10240','allowed_extensions'=>['nullable','string','max:100','regex:/^[a-zA-Z0-9, ]+$/'],
        ]);
    }

    private function questionOptions(Request $request,string $type):?array
    {
        if(!in_array($type,['dropdown','radio','checkbox'],true))return null;
        if($request->option_source==='users'){
            return User::query()->whereNotNull('name')->orderBy('name')->pluck('name')->filter()->unique()->values()->all();
        }
        return collect(preg_split('/\r\n|\r|\n/',(string)$request->options_text))->map(fn($v)=>trim($v))->filter()->unique()->values()->all();
    }

    private function scopedForms()
    {
        $query=ActivityAttendanceForm::query();
        return Auth::user()->role==='admin_bidang'?$query->where('bidang',Auth::user()->bidang):$query;
    }

    private function authorizeForm(ActivityAttendanceForm $form):void
    {
        $this->guardRole();
        abort_if(Auth::user()->role==='admin_bidang'&&$form->bidang!==Auth::user()->bidang,403);
    }

    private function guardRole():void
    {
        abort_unless(in_array(Auth::user()?->role,['superadmin','admin_bidang'],true),403);
    }
}
