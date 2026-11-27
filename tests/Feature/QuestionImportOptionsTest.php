<?php

namespace Tests\Feature;

use App\Imports\QuestionImport;
use App\Models\Bidang;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Tests\TestCase;

class QuestionImportOptionsTest extends TestCase
{
    use RefreshDatabase;

    private function row(): array
    {
        return ['pertanyaan' => 'Penilaian perilaku', 'level_peran' => 'Atasan',
            'program_evaluasi' => 'PKP', 'sub_kategori' => 'Perubahan Sikap Perilaku',
            'tipe_jawaban' => 'dropdown'];
    }

    public function test_answer_alias_and_pipe_options_are_saved_as_separate_choices(): void
    {
        $question = (new QuestionImport('Semua Bidang'))->model($this->row() + [
            'jawaban' => 'Tidak Sesuai | Kurang Sesuai | Sesuai | Sangat Sesuai',
        ]);
        $question->save();
        $this->assertSame(['Tidak Sesuai', 'Kurang Sesuai', 'Sesuai', 'Sangat Sesuai'], $question->fresh()->options);
    }

    public function test_official_header_supports_separators_and_preserves_zero(): void
    {
        $question = (new QuestionImport('Semua Bidang'))->model($this->row() + [
            'pilihan_jawaban' => "0, 1; 2\n3 | 4", 'jawaban' => 'Tidak dipakai',
        ]);
        $this->assertSame(['0', '1', '2', '3', '4'], $question->options);
    }

    public function test_empty_choices_are_rejected_instead_of_silently_saved(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new QuestionImport('Semua Bidang'))->model($this->row());
    }

    public function test_export_can_be_uploaded_without_editing_and_preserves_fields_and_choices(): void
    {
        $bidang = Bidang::create(['name' => 'Bidang Uji Export', 'original_name' => 'Bidang Uji Export'])->name;
        $options = ['0', 'Baik, sekali', 'Tepat | sesuai', "Catatan\nlanjutan"];
        $question = (new QuestionImport($bidang))->model(array_replace($this->row(), [
            'pertanyaan' => '=Pertanyaan disimpan sebagai teks',
            'pilihan_jawaban' => json_encode($options, JSON_THROW_ON_ERROR),
        ]));
        $question->save();
        $global = (new QuestionImport('Semua Bidang'))->model([
            'pertanyaan' => 'Evaluasi penyelenggara', 'level_peran' => 'Penyelenggara', 'metode' => 'blended',
        ]);
        $global->save();

        $admin = User::factory()->create(['role' => 'superadmin']);
        $response = $this->actingAs($admin)->get(route('questions.export'))->assertOk();
        $path = $response->baseResponse->getFile()->getPathname();
        $book = (new Xlsx())->load($path);
        $sheet = $book->getActiveSheet();
        $this->assertSame(QuestionImport::HEADINGS, $sheet->rangeToArray('A1:H1')[0]);
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('G2')->getDataType());
        $book->disconnectWorksheets();

        $this->post(route('questions.import'), [
            'bidang' => '__excel__',
            'file' => new UploadedFile($path, 'export.xlsx', null, null, true),
        ])->assertRedirect(route('questions.index'))->assertSessionHasNoErrors();

        $copy = Question::where('question_text', $question->question_text)->where('id', '!=', $question->id)->sole();
        $this->assertSame($bidang, $copy->bidang);
        $this->assertSame('l34_atasan', $copy->category);
        $this->assertSame('PKP', $copy->program_evaluasi);
        $this->assertSame($options, $copy->options);
        $this->assertDatabaseHas('evaluation_questions', [
            'question_text' => $global->question_text, 'bidang' => 'Semua Bidang', 'metode' => 'blended',
        ]);
        $this->assertSame(2, Question::where('question_text', $global->question_text)->count());
    }
}
