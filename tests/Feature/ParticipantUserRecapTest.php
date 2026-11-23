<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\EvaluationResultL2;
use App\Models\Institution;
use App\Models\InstitutionMunicipality;
use App\Models\Participant;
use App\Models\Schedule;
use App\Models\Training;
use App\Models\TrainingMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ParticipantUserRecapTest extends TestCase
{
    use RefreshDatabase;

    public function test_recap_groups_participants_by_workplace_and_shows_only_training_with_activity(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $institution = Institution::firstOrCreate(['name' => 'Dinas Provinsi Uji']);
        $municipality = InstitutionMunicipality::where('name', 'Kabupaten Bandung')->firstOrFail();
        $provinceUser = User::factory()->create([
            'name' => 'Peserta Provinsi Uji', 'role' => 'participant', 'institution_id' => $institution->id,
            'nip' => '199001012020011001', 'nik' => '3201010101900001',
        ]);
        $localUser = User::factory()->create([
            'name' => 'Peserta Kabupaten Uji', 'role' => 'participant', 'institution_scope' => 'municipality',
            'institution_municipality_id' => $municipality->id, 'instansi' => 'Dinas Daerah Uji',
            'kota' => 'KOTA DEPOK',
        ]);
        $emptyUser = User::factory()->create([
            'name' => 'Peserta Tanpa Aktivitas', 'role' => 'participant', 'institution_id' => $institution->id,
        ]);

        foreach (['Presensi Uji', 'Evaluasi Uji', 'Forum Uji', 'Registrasi Saja Uji', 'Izin Saja Uji'] as $name) {
            $training = Training::create([
                'nama_pelatihan' => $name, 'bidang' => 'Bidang A', 'model' => 'standar', 'metode' => 'klasikal',
                'lokasi' => 'BPSDM', 'angkatan' => 'I', 'jumlah_peserta' => 10, 'jp' => 10,
                'tgl_mulai' => '2026-01-01', 'tgl_selesai' => '2026-01-02', 'created_by' => $admin->id,
            ]);
            $participant = Participant::create([
                'training_id' => $training->id,
                'user_id' => $name === 'Evaluasi Uji' ? null : $provinceUser->id,
                'name' => $provinceUser->name,
                'nip_nik' => $provinceUser->nip,
            ]);

            if (in_array($name, ['Presensi Uji', 'Izin Saja Uji'], true)) {
                $schedule = Schedule::create([
                    'training_id' => $training->id, 'date' => '2026-01-01', 'start_time' => '08:00',
                    'end_time' => '10:00', 'activity' => 'Materi', 'pic' => 'Panitia',
                    'schedule_type' => 'learning', 'jp' => 2,
                ]);
                Attendance::create([
                    'schedule_id' => $schedule->id, 'participant_id' => $participant->id,
                    'status' => $name === 'Presensi Uji' ? 'hadir' : 'izin',
                ]);
            }
            if ($name === 'Evaluasi Uji') {
                EvaluationResultL2::create(['participant_id' => $participant->id, 'pretest' => 60, 'postest' => 80]);
            }
            if ($name === 'Forum Uji') {
                TrainingMessage::create(['training_id' => $training->id, 'user_id' => $provinceUser->id, 'message' => 'Diskusi']);
            }
        }

        $recap = ['tab' => 'rekap-peserta'];
        $this->actingAs($localUser)->get(route('users.index', $recap))->assertForbidden();
        $this->post(route('users.recap.export'))->assertForbidden();
        $this->actingAs($admin)->get(route('users.index', $recap + ['group_id' => $institution->id]))->assertOk()
            ->assertSee('Unduh Batch Excel')
            ->assertSee($provinceUser->name)->assertSee($provinceUser->nip)->assertSee($provinceUser->nik)
            ->assertSee($institution->name)->assertSee($emptyUser->name)->assertSee('Belum ada aktivitas tercatat')
            ->assertSee('<li>Presensi Uji</li>', false)->assertSee('<li>Evaluasi Uji</li>', false)->assertSee('<li>Forum Uji</li>', false)
            ->assertDontSee('<li>Registrasi Saja Uji</li>', false)->assertDontSee('<li>Izin Saja Uji</li>', false)
            ->assertDontSee($localUser->name);

        $this->get(route('users.index', $recap + ['group' => 'kabupaten-kota', 'group_id' => $municipality->id]))->assertOk()
            ->assertSee($localUser->name)->assertSee('Dinas Daerah Uji')->assertSee($municipality->name)
            ->assertDontSee('KOTA DEPOK', false)->assertDontSee($provinceUser->name);

        $response = $this->post(route('users.recap.export', [
            'group_id' => $institution->id, 'search' => $provinceUser->name,
        ]))->assertDownload('rekap-peserta-instansi-batch-1.xlsx');
        $sheet = IOFactory::load($response->baseResponse->getFile()->getPathname())->getActiveSheet();
        $this->assertSame(['Nama', 'NIP', 'NIK', 'Nama Instansi', 'Pelatihan yang Diikuti'], $sheet->toArray()[0]);
        $this->assertSame([$provinceUser->name, $provinceUser->nip, $provinceUser->nik, $institution->name],
            array_slice($sheet->toArray()[1], 0, 4));
        $this->assertEqualsCanonicalizing(['Presensi Uji', 'Evaluasi Uji', 'Forum Uji'],
            explode("\n", $sheet->getCell('E2')->getValue()));
        $this->assertSame(2, $sheet->getHighestDataRow());

        $response = $this->post(route('users.recap.export', [
            'group' => 'kabupaten-kota', 'group_id' => $municipality->id,
        ]))->assertDownload('rekap-peserta-kabupaten-kota-batch-1.xlsx');
        $sheet = IOFactory::load($response->baseResponse->getFile()->getPathname())->getActiveSheet();
        $this->assertSame('Kabupaten/Kota', $sheet->getCell('D1')->getValue());
        $this->assertSame($municipality->name, $sheet->getCell('D2')->getValue());
        $this->assertSame('Dinas Daerah Uji', $sheet->getCell('E2')->getValue());
        $this->assertSame('Belum ada aktivitas tercatat', $sheet->getCell('F2')->getValue());
    }

    public function test_excel_download_advances_without_repeating_participants(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $institution = Institution::firstOrCreate(['name' => 'Instansi Paginasi']);
        for ($number = 1; $number <= 1001; $number++) {
            User::factory()->create([
                'name' => sprintf('Peserta Paginasi %04d', $number),
                'role' => 'participant',
                'institution_id' => $institution->id,
            ]);
        }
        $filters = ['group_id' => $institution->id, 'search' => 'Peserta Paginasi'];
        $download = route('users.recap.export', $filters);
        $recap = route('users.index', ['tab' => 'rekap-peserta', 'page' => 2] + $filters);
        $this->actingAs($admin)->get($recap)->assertOk()->assertSee($download);

        $response = $this->post($download)->assertDownload('rekap-peserta-instansi-batch-1.xlsx');
        $sheet = IOFactory::load($response->baseResponse->getFile()->getPathname())->getActiveSheet();
        $this->assertSame(1001, $sheet->getHighestDataRow());
        $this->assertSame('Peserta Paginasi 0001', $sheet->getCell('A2')->getValue());
        $this->assertSame('Peserta Paginasi 1000', $sheet->getCell('A1001')->getValue());

        $response = $this->post($download)->assertDownload('rekap-peserta-instansi-batch-2.xlsx');
        $sheet = IOFactory::load($response->baseResponse->getFile()->getPathname())->getActiveSheet();
        $this->assertSame(2, $sheet->getHighestDataRow());
        $this->assertSame('Peserta Paginasi 1001', $sheet->getCell('A2')->getValue());

        $this->from($recap)->post($download)->assertRedirect($recap)->assertSessionHas('recap_export_status');
    }
}
