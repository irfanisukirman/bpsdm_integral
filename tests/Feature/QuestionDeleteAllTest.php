<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionDeleteAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_all_respects_admin_scope_and_keeps_monitoring_questions(): void
    {
        foreach (['admin_bidang', 'superadmin'] as $role) {
            $own = Question::create(['bidang' => 'Semua Bidang', 'category' => 'l34_mandiri', 'type' => 'slider', 'question_text' => 'Sendiri']);
            $other = Question::create(['bidang' => 'Bidang Lain', 'category' => 'l34_mandiri', 'type' => 'slider', 'question_text' => 'Lain']);
            $monitoring = Question::create(['bidang' => 'Semua Bidang', 'category' => 'monitoring', 'type' => 'ya_tidak', 'question_text' => 'Monitoring']);
            $user = User::factory()->create(['role' => $role, 'bidang' => 'Semua Bidang']);
            $response = $this->actingAs($user)->get(route('questions.index'));
            if ($role === 'superadmin') {
                $response->assertOk()->assertSee('Hapus Semua Pertanyaan');
                $this->delete(route('questions.destroy-bundle'))->assertRedirect(route('questions.index'));
                $this->assertModelMissing($own);
                $this->assertModelMissing($other);
            } else {
                $response->assertForbidden();
                $this->delete(route('questions.destroy-bundle'))->assertForbidden();
                $this->assertModelExists($own);
                $this->assertModelExists($other);
            }
            $this->assertModelExists($monitoring);
        }
    }
}
