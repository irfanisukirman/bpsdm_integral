<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfigurationExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_export_all_monitoring_indicators(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        Question::create([
            'category' => 'Monitoring Peserta', 'metode' => 'klasikal', 'type' => 'ya_tidak',
            'question_text' => 'Apakah peserta hadir tepat waktu?',
        ]);

        $this->actingAs($user)->get(route('indicators.export'))
            ->assertOk()
            ->assertDownload();
    }

    public function test_question_export_is_available_for_superadmin_and_field_admin(): void
    {
        $bidang = 'Bidang Pengembangan Kompetensi Manajerial';
        Question::create([
            'bidang' => $bidang, 'training_type' => $bidang, 'program_evaluasi' => 'CPNS',
            'category' => 'l34_mandiri', 'sub_category' => 'Dampak Pelatihan',
            'metode' => 'semua', 'type' => 'slider', 'question_text' => 'Apakah pelatihan berdampak?',
        ]);

        foreach ([
            User::factory()->create(['role' => 'superadmin']),
            User::factory()->create(['role' => 'admin_bidang', 'bidang' => $bidang]),
        ] as $user) {
            $this->actingAs($user)->get(route('questions.export'))
                ->assertOk()
                ->assertDownload();
        }
    }
}