<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QuestionLongTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_long_question_and_options_can_be_created_and_updated_without_truncation(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $text = "Metode Pelaksanaan\n\n".rtrim(str_repeat('Penjelasan pembelajaran klasikal dan blended learning. ', 30));
        $data = [
            'bidang' => 'Semua Bidang', 'category' => 'l34_mandiri',
            'program_evaluasi' => 'PKP', 'sub_category' => 'Data Diri Alumni',
            'metode' => 'semua', 'type' => 'dropdown', 'question_text' => $text,
            'options' => ['Klasikal', 'Blended Learning'],
        ];

        // SQLite also permits long VARCHAR values, so verify the migrated type explicitly.
        $this->assertContains(Schema::getColumnType('evaluation_questions', 'question_text'), ['text', 'longtext']);
        $this->actingAs($user)->post(route('questions.store'), $data)
            ->assertRedirect()->assertSessionHasNoErrors();
        $question = Question::latest('id')->firstOrFail();
        $this->assertSame($text, $question->question_text);
        $this->assertSame($data['options'], $question->options);

        $data['question_text'] .= "\nKeterangan tambahan untuk responden.";
        $this->put(route('questions.update', $question), $data)
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($data['question_text'], $question->fresh()->question_text);
        $this->assertSame($data['options'], $question->fresh()->options);
    }
}
