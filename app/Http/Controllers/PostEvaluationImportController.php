<?php

namespace App\Http\Controllers;

use App\Models\EvaluationResultL34;
use App\Models\Training;
use App\Services\PostEvaluationImportService;
use App\Services\PostEvaluationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PostEvaluationImportController extends Controller
{
    public function preview(Request $request, Training $training, PostEvaluationService $access, PostEvaluationImportService $importer)
    {
        $access->authorize($request->user(), $training, true);
        $request->validate(['workbook' => 'required|file|mimes:xlsx|max:20480']);
        try {
            $plan = $importer->preview($training, $request->file('workbook')->getRealPath());
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw ValidationException::withMessages(['workbook' => 'Workbook tidak dapat dibaca. Gunakan file XLSX dengan format Pasca yang sesuai.']);
        }
        $id = (string) Str::uuid();
        $path = $request->file('workbook')->store('post-evaluation-imports/'.$training->id, 'local');
        abort_unless($path, 500, 'Gagal menyimpan file import.');
        try {
            DB::table('post_evaluation_imports')->insert(['id' => $id, 'training_id' => $training->id, 'created_by' => $request->user()->id, 'filename' => mb_substr(basename($request->file('workbook')->getClientOriginalName()), 0, 255), 'path' => $path, 'fingerprint' => $importer->fingerprint($plan), 'status' => 'preview', 'report' => json_encode($plan, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), 'created_at' => now(), 'updated_at' => now()]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return redirect()->route('post-evaluation.show', [$training, 'tab' => 'import', 'preview' => $id] + $request->only('from'));
    }

    public function store(Request $request, Training $training, string $import, PostEvaluationService $access, PostEvaluationImportService $importer)
    {
        $access->authorize($request->user(), $training, true);
        $request->validate(['confirm_training' => 'accepted']);
        $count = DB::transaction(function () use ($request, $training, $import, $importer) {
            $batch = DB::table('post_evaluation_imports')->where('id', $import)->where('training_id', $training->id)->where('created_by', $request->user()->id)->lockForUpdate()->first();
            abort_unless($batch, 404);
            if ($batch->status === 'imported') {
                return 0;
            }
            if (now()->diffInHours(Carbon::parse($batch->created_at), true) > 24) {
                throw ValidationException::withMessages(['workbook' => 'Pratinjau sudah kedaluwarsa. Periksa file kembali.']);
            }
            $locked = Training::whereKey($training->id)->lockForUpdate()->firstOrFail();
            if (! Storage::disk('local')->exists($batch->path)) {
                throw ValidationException::withMessages(['workbook' => 'File pratinjau tidak tersedia. Unggah kembali workbook.']);
            }
            $plan = $importer->preview($locked, Storage::disk('local')->path($batch->path));
            if ($plan['errors']) {
                throw ValidationException::withMessages(['workbook' => 'Import belum dapat disimpan. Perbaiki semua masalah pada pratinjau.']);
            }
            if (! hash_equals($batch->fingerprint, $importer->fingerprint($plan))) {
                throw ValidationException::withMessages(['workbook' => 'Data peserta, soal atau jawaban telah berubah. Periksa file kembali agar hasil pencocokan mutakhir.']);
            }
            if ($plan['counts']['new'] === 0) {
                throw ValidationException::withMessages(['workbook' => 'Tidak ada jawaban baru untuk diimpor. Jawaban yang sudah ada tetap dipertahankan.']);
            }
            $count = 0;
            foreach ($plan['rows'] as $row) {
                if ($row['action'] !== 'insert') {
                    continue;
                }
                $data = ['training_id' => $training->id, 'participant_id' => $row['participant_id'], 'question_id' => $row['question_id'], 'evaluator_role' => $row['role'], 'evaluator_name' => $row['evaluator_name'] ?: 'Penilai (import historis)', 'score' => $row['score'], 'note' => $row['note']];
                if ($row['existing_id']) {
                    EvaluationResultL34::whereKey($row['existing_id'])->update($data);
                } else {
                    EvaluationResultL34::create($data);
                }
                $count++;
            }
            DB::table('post_evaluation_imports')->where('id', $batch->id)->update(['status' => 'imported', 'imported_at' => now(), 'updated_at' => now()]);

            return $count;
        });

        return redirect()->route('post-evaluation.show',[$training, 'tab' => 'import', 'preview' => $import] + $request->only('from'))->with('success',$count.' jawaban evaluasi berhasil disimpan. Data peserta, akun, Proper, tim dan nilai wawancara tetap dipertahankan.');
    }
}
