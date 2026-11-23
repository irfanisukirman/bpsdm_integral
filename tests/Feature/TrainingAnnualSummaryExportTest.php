<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class TrainingAnnualSummaryExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_annual_export_contains_requested_columns_statuses_and_approved_participant_counts(): void
    {
        $this->travelTo(Carbon::parse('2026-06-15 12:00:00', 'Asia/Jakarta'));
        $admin = User::factory()->create(['role' => 'admin_bidang', 'bidang' => 'Bidang A']);
        $base = [
            'bidang' => 'Bidang A', 'model' => 'standar', 'metode' => 'klasikal', 'lokasi' => 'BPSDM',
            'angkatan' => 'I', 'jumlah_peserta' => 20, 'jp' => 16, 'created_by' => $admin->id,
        ];
        $training = Training::create($base + [
            'nama_pelatihan' => 'Pelatihan selesai', 'tgl_mulai' => '2026-01-01', 'tgl_selesai' => '2026-01-02',
        ]);
        foreach (['approved', 'pending'] as $status) {
            $training->participants()->create(['name' => $status, 'nip_nik' => $status, 'registration_status' => $status]);
        }
        Training::create($base + ['nama_pelatihan' => 'Pelatihan aktif', 'tgl_mulai' => '2026-06-14', 'tgl_selesai' => '2026-06-15']);
        Training::create($base + ['nama_pelatihan' => 'Pelatihan mendatang', 'tgl_mulai' => '2026-08-01', 'tgl_selesai' => '2026-08-02']);
        Training::create($base + ['nama_pelatihan' => 'Pelatihan tahun lalu', 'tgl_mulai' => '2025-12-31', 'tgl_selesai' => '2026-01-02']);
        Training::create(array_replace($base, ['bidang' => 'Bidang B', 'nama_pelatihan' => 'Pelatihan bidang lain', 'tgl_mulai' => '2026-01-01', 'tgl_selesai' => '2026-01-02']));

        $export = route('trainings.export-summary', ['year' => 2026]);
        $this->actingAs(User::factory()->create(['role' => 'participant']))->get($export)->assertForbidden();
        $this->actingAs($admin)->get(route('trainings.index'))->assertOk()->assertSee('Ekspor Ringkasan Tahunan');
        $response = $this->get($export)->assertDownload('ringkasan-pelatihan-2026.xlsx');
        Cell::setValueBinder(new DefaultValueBinder());
        $sheet = IOFactory::load($response->baseResponse->getFile()->getPathname())->getActiveSheet();

        $this->assertSame([
            'Nama Pelatihan', 'Tanggal Mulai', 'Tanggal Selesai', 'Metode Pelatihan',
            'Angkatan', 'Total JP', 'Jumlah Peserta', 'Status',
        ], $sheet->toArray()[0]);
        $this->assertSame(4, $sheet->getHighestDataRow());
        $this->assertSame('Pelatihan selesai', $sheet->getCell('A2')->getValue());
        $this->assertSame('01/01/2026', $sheet->getCell('B2')->getFormattedValue());
        $this->assertSame('02/01/2026', $sheet->getCell('C2')->getFormattedValue());
        $this->assertSame('Klasikal', $sheet->getCell('D2')->getValue());
        $this->assertSame('I', $sheet->getCell('E2')->getValue());
        $this->assertSame('n', $sheet->getCell('F2')->getDataType());
        $this->assertSame('n', $sheet->getCell('G3')->getDataType());
        $this->assertEquals(16, $sheet->getCell('F2')->getValue());
        $this->assertEquals(1, $sheet->getCell('G2')->getValue());
        $this->assertEquals(0, $sheet->getCell('G3')->getValue());
        $this->assertSame(['Selesai', 'Berlangsung', 'Belum selesai'], array_column(array_slice($sheet->toArray(), 1), 7));
    }
}
