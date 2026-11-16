<?php

namespace Tests\Feature;

use App\Imports\QuestionImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
