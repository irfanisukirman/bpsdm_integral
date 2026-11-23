<?php

namespace Tests\Feature;

use App\Models\LevelOneRecapImport;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class LevelOneRecapImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_previews_and_saves_both_recap_formats_without_creating_schedules(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $training = Training::create([
            'nama_pelatihan' => 'Pelatihan Uji Impor', 'bidang' => 'Bidang A', 'model' => 'standar',
            'metode' => 'klasikal', 'lokasi' => 'BPSDM', 'angkatan' => 'I', 'jumlah_peserta' => 10,
            'jp' => 10, 'tgl_mulai' => '2025-01-01', 'tgl_selesai' => '2025-01-02', 'created_by' => $admin->id,
        ]);
        $index = route('evall1.recap-import.index', $training);
        $preview = route('evall1.recap-import.preview', $training);
        $organizationRows = [
            ['Rekap Penyelenggara'], ['Pelatihan Uji Impor'],
            ['No', 'Nama Lengkap', 'Penyelenggaraan', 'Mohon Saudara berikan komentar'],
            [1, '-', 93, 'Perlu tambahan contoh'], [2, '-', 85, 'Baik'],
        ];
        $this->actingAs($admin)->from($index)->post($preview, ['file' => $this->workbook($organizationRows)])
            ->assertSessionHasErrors('file');
        $participant = $training->participants()->create(['name' => 'Peserta Sudah Terdaftar', 'nip_nik' => '12345']);
        $response = $this->post($preview, ['file' => $this->workbook($organizationRows)])->assertSessionHasNoErrors();
        $previewUrl = $response->headers->get('Location');
        $this->assertDatabaseCount('level_one_recap_imports', 0);
        $this->get($previewUrl)->assertOk()->assertSee('Belum disimpan')->assertSee($participant->name)
            ->assertSee('Mohon Saudara berikan komentar')->assertSee('Perlu tambahan contoh')
            ->assertSee('name="mapping[4]"', false)->assertSee('Tetap anonim');
        parse_str(parse_url($previewUrl, PHP_URL_QUERY), $query);
        $confirm = route('evall1.recap-import.store', [$training, $query['preview']]);
        $this->from($previewUrl)->post($confirm, ['mapping' => [4 => '999999']])->assertSessionHasErrors('mapping.4');
        $this->post($confirm, ['mapping' => [4 => (string) $participant->id]])->assertRedirect($index);
        $organization = LevelOneRecapImport::where('kind', 'penyelenggara')->firstOrFail();
        $this->assertSame($participant->id, $organization->entries[0]['linked_id']);
        $this->assertArrayNotHasKey('linked_id', $organization->entries[1]);
        $this->post($preview, ['file' => $this->workbook($organizationRows)])->assertRedirect($index);
        $this->assertDatabaseCount('level_one_recap_imports', 1);

        $response = $this->post($preview, ['file' => $this->workbook([
            ['Rekap Pengajar'], ['Pelatihan Uji Impor'], ['No', 'Pengajar / Narasumber', 'Unsur'],
            [null, null, 'Kehadiran', 'Penyajian', 'Jumlah'], [null, null, 1, 2],
            [1, 'Pengajar Belum Terdaftar / Materi Uji', 90, 98, 188],
        ])])->assertSessionHasNoErrors();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->post(route('evall1.recap-import.store', [$training, $query['preview']]))->assertRedirect($index);
        $speaker = LevelOneRecapImport::where('kind', 'narasumber')->firstOrFail();
        $this->assertEquals(94, $speaker->entries[0]['average']);
        $this->assertSame('Materi Uji', $speaker->entries[0]['material']);
        $this->assertDatabaseCount('schedules', 0);
        $this->assertDatabaseCount('evaluation_results_l1', 0);
        $this->get($index)->assertOk()->assertSee('Rekap yang Tersimpan')->assertSee('Materi Uji');
        $this->get(route('evall1.index', $training))->assertOk()
            ->assertSeeInOrder(['Status Pelatihan', 'Hasil Impor Evaluasi Level 1', 'Materi Uji', 'Daftar Formulir Evaluasi'])
            ->assertSee('Perlu tambahan contoh')->assertSee('Unduh Rekap Excel');
        $this->get(route('evall12.dashboard', $training))->assertOk()->assertSee('Materi Uji')
            ->assertViewHas('speakerAverage', 94.0)->assertViewHas('organizerAverage', 89.0)
            ->assertViewHas('l1Average', 91.5)->assertViewHas('l1Respondents', 1)->assertSee('91,5');
        $this->get(route('trainings.manage', $training))->assertOk()->assertSee('Skor Level 1 91,5/100');
        $participant->update(['registration_status' => 'approved']);
        foreach (['Perubahan Perilaku' => 'Baik', 'Dampak Pelatihan' => 'Sangat Baik'] as $section => $answer) {
            $question = \App\Models\Question::create([
                'bidang' => $training->bidang, 'program_evaluasi' => 'Teknis', 'category' => 'l34_atasan',
                'sub_category' => $section, 'type' => 'dropdown', 'question_text' => $section,
                'options' => ['Sangat Kurang', 'Kurang', 'Cukup', 'Baik', 'Sangat Baik'],
            ]);
            \App\Models\EvaluationResultL34::create([
                'training_id' => $training->id, 'participant_id' => $participant->id, 'question_id' => $question->id,
                'evaluator_role' => 'atasan', 'evaluator_name' => 'Penilai Uji', 'note' => $answer,
            ]);
        }
        $this->get(route('dashboard'))->assertOk()->assertViewHas('avgL1', 91.5)
            ->assertViewHas('avgL3', 80.0)->assertViewHas('avgL4', 100.0);
        $this->get(route('evall1.organizer-summary', $training))->assertOk()
            ->assertSee('Perlu tambahan contoh')->assertSee($participant->name);
        $this->get(route('evall1.recap-import.download', [$training, $speaker]))
            ->assertDownload("rekap-lama-narasumber-{$speaker->id}.xlsx");

        $other = User::factory()->create(['role' => 'admin_bidang', 'bidang' => 'Bidang B']);
        $this->actingAs($other)->get($index)->assertForbidden();
        $this->get(route('evall1.index', $training))->assertOk()->assertDontSee('Perlu tambahan contoh');
        $this->post($preview, ['file' => $this->workbook($organizationRows)])->assertForbidden();
    }

    public function test_organizer_file_without_a_name_column_is_read_as_anonymous(): void
    {
        $file = $this->workbook([
            ['Rekap Penyelenggara'], ['Pelatihan Uji Impor'],
            ['No', 'Penyelenggaraan', 'Sebutkan materi pelatihan yang sangat mendukung tugas Anda',
                '(Materi yang dibahas) Menurut pendapat Anda materi apa saja yang perlu dihapuskan?'],
            [1, 93, 'Simulasi lapangan', 'Tambahkan contoh'],
        ]);
        $data = app(\App\Services\LevelOneRecapReader::class)->read($file->getRealPath());

        $this->assertSame('penyelenggara', $data['kind']);
        $this->assertNull($data['entries'][0]['name']);
        $this->assertSame(['score', 'text', 'text'], array_column($data['indicators'], 'kind'));
        $this->assertSame([93.0, 'Simulasi lapangan', 'Tambahkan contoh'], $data['entries'][0]['values']);
    }

    private function workbook(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1', true);
        $path = tempnam(sys_get_temp_dir(), 'l1-recap-test-');
        try {
            (new Xlsx($spreadsheet))->save($path);

            return UploadedFile::fake()->createWithContent('rekap.xlsx', file_get_contents($path));
        } finally {
            unlink($path);
        }
    }
}
