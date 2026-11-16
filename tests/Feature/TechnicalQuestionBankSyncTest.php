<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Services\TechnicalQuestionBankSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TechnicalQuestionBankSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_keeps_three_distinct_perspectives_and_unique_questions(): void
    {
        $bundle = app(TechnicalQuestionBankSyncService::class)->catalog()['programs']['Teknis'];
        $questions = collect($bundle['questions']);
        $this->assertCount(74, $questions);
        foreach (['mandiri' => 28, 'atasan' => 23, 'rekan' => 23] as $role => $count) {
            $group = $questions->where('category', 'l34_'.$role);
            $this->assertCount($count, $group);
            $this->assertCount($count, $group->pluck('question_text')->unique());
        }
        $this->assertCount(8, $bundle['merged_columns']);
        foreach (['AB', 'AZ', 'BX'] as $column) {
            $this->assertSame(['Sudah', 'Belum'], $questions->firstWhere('source_column', $column)['options']);
        }
        foreach (['AC', 'BA', 'BY'] as $column) {
            $this->assertSame(['Diskusi informal', 'Diskusi formal (und rapat, daftar hadir, dll)'], $questions->firstWhere('source_column', $column)['options']);
        }
        $this->assertStringStartsWith('Ybs', $questions->firstWhere('source_column', 'BG')['options'][0]);
        $this->assertSame(['Sangat Kurang', 'Kurang', 'Cukup', 'Baik', 'Sangat Baik'], $questions->firstWhere('source_column', 'AD')['options']);
        $this->assertFalse($questions->contains(fn ($q) => in_array($q['source_column'], ['O', 'AU', 'BS'])));
        foreach ($questions as $q) {
            $this->assertSame('Semua Bidang', $q['bidang']);
            if ($q['options']) $this->assertCount(count(array_unique($q['options'])), $q['options']);
        }
    }

    public function test_sync_preserves_existing_id_archives_duplicates_and_leaves_other_banks_untouched(): void
    {
        Storage::fake('local');
        $base = ['bidang' => 'Bidang A', 'metode' => 'semua', 'program_evaluasi' => 'Teknis', 'category' => 'l34_mandiri', 'sub_category' => 'Data Diri Alumni', 'type' => 'text', 'question_text' => 'Jabatan (saat ini)'];
        $original = Question::create($base);
        $duplicate = Question::create($base);
        $managerial = Question::create(array_replace($base, ['program_evaluasi' => 'PKP']));
        $levelOne = Question::create(array_replace($base, ['category' => 'penyelenggara']));
        $before = [$managerial->fresh()->getAttributes(), $levelOne->fresh()->getAttributes()];
        $sync = app(TechnicalQuestionBankSyncService::class);
        $sync->sync();
        $this->assertSame(4, Question::count());
        $result = $sync->sync(true);
        $this->assertTrue(Storage::disk('local')->exists($result['backup']));
        $this->assertTrue($original->fresh()->is_active);
        $this->assertFalse($duplicate->fresh()->is_active);
        $this->assertSame('Semua Bidang', $original->fresh()->bidang);
        $this->assertSame($before, [$managerial->fresh()->getAttributes(), $levelOne->fresh()->getAttributes()]);
        $this->assertSame(74, Question::where('program_evaluasi', 'Teknis')->whereIn('category', ['l34_mandiri', 'l34_atasan', 'l34_rekan'])->where('is_active', true)->count());
        $repeat = $sync->sync()['stats']['Teknis'];
        $this->assertSame(0, $repeat['created']);
        $this->assertSame(0, $repeat['updated']);
        $this->assertSame(0, $repeat['archived']);
    }
}
