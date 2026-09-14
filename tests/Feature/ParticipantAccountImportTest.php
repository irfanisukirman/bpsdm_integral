<?php

namespace Tests\Feature;

use App\Exports\ParticipantTemplateExport;
use App\Imports\ParticipantImport;
use App\Models\Participant;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ParticipantAccountImportTest extends TestCase
{
    use RefreshDatabase;

    private function training(User $admin): Training
    {
        return Training::create([
            'nama_pelatihan' => 'Pelatihan Import', 'bidang' => $admin->bidang,
            'model' => 'standar', 'metode' => 'klasikal', 'lokasi' => 'BPSDM',
            'angkatan' => 'I', 'jumlah_peserta' => 30, 'jp' => 10,
            'tgl_mulai' => now()->toDateString(), 'tgl_selesai' => now()->addDay()->toDateString(),
            'created_by' => $admin->id,
        ]);
    }

    public function test_import_links_existing_account_and_creates_new_account_with_onboarding(): void
    {
        $admin = User::factory()->create(['role' => 'admin_bidang']);
        $training = $this->training($admin);
        $existing = User::factory()->create([
            'role' => 'participant', 'username' => 'user-lama', 'nip_nik' => '1111',
            'instansi' => 'Instansi Lama', 'password' => Hash::make('password123'),
        ]);

        $import = new ParticipantImport($training->id);
        $import->collection(collect([
            ['nip_nik' => '1111', 'nama_lengkap' => 'Nama dari Excel', 'instansi' => 'Instansi Excel'],
            ['nip_nik' => "'2222", 'nama_lengkap' => 'Peserta Baru', 'instansi' => 'Instansi Baru'],
        ]));

        $this->assertSame(2, $import->participantsAdded);
        $this->assertSame(1, $import->accountsCreated);
        $this->assertDatabaseHas('participants', ['training_id' => $training->id, 'user_id' => $existing->id, 'nip_nik' => '1111', 'registration_status' => 'approved']);
        $newUser = User::where('nip_nik', '2222')->firstOrFail();
        $this->assertSame('2222', $newUser->username);
        $this->assertTrue($newUser->must_complete_profile);
        $this->assertTrue($newUser->must_change_password);
        $this->assertSame("'2222", $import->results[1][1]);
        $this->assertSame("'2222", $import->results[1][5]);
        $this->assertNotEmpty($import->results[1][6]);
        $this->assertTrue(Hash::check($import->results[1][6], $newUser->password));
    }

    public function test_imported_participant_logs_in_with_nip_and_is_forced_to_complete_profile(): void
    {
        $user = User::factory()->create([
            'role' => 'participant', 'username' => 'legacy-email', 'nip_nik' => '19880001',
            'password' => Hash::make('password123'), 'must_complete_profile' => true, 'must_change_password' => true,
        ]);

        $this->post(route('login'), ['username' => '19880001', 'password' => 'password123'])
            ->assertRedirect(route('participant.profile.complete'));
        $this->assertAuthenticatedAs($user);
        $this->get(route('dashboard'))->assertRedirect(route('participant.profile.complete'));
        $this->post(route('participant.profile.store'), [
            'user_type' => 'peserta', 'nip_nik' => '19880001', 'whatsapp' => '08123456789',
            'gender' => 'Laki-Laki', 'birth_place' => 'Bandung', 'birth_date' => '1990-01-01',
            'jabatan' => 'Analis', 'instansi' => 'BPSDM', 'status_kepegawaian' => 'PNS',
            'provinsi' => 'JAWA BARAT', 'kota' => 'KOTA BANDUNG', 'kecamatan' => 'COBLONG',
            'kelurahan' => 'DAGO', 'address' => 'Jalan Contoh', 'latitude' => -6.9, 'longitude' => 107.6,
            'password' => 'PasswordBaru123!', 'password_confirmation' => 'PasswordBaru123!',
        ])->assertRedirect(route('participant.dashboard'));
        $user->refresh();
        $this->assertFalse($user->must_complete_profile);
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('PasswordBaru123!', $user->password));
    }

    public function test_admin_can_download_template_and_import_file(): void
    {
        $admin = User::factory()->create(['role' => 'admin_bidang']);
        $training = $this->training($admin);
        $this->actingAs($admin)->get(route('participants.template'))->assertOk()->assertDownload();

        $source = new class implements FromArray {
            public function array(): array { return [['nip_nik','nama_lengkap','instansi'], ["'3333",'Peserta File','Instansi File']]; }
        };
        $binary = Excel::raw($source, \Maatwebsite\Excel\Excel::XLSX);
        $file = UploadedFile::fake()->createWithContent('peserta.xlsx', $binary);

        $this->post(route('participants.import', $training), ['file' => $file])
            ->assertRedirect(route('trainings.participants', $training))
            ->assertSessionHas('success')
            ->assertSessionHas('participant_import_results.'.$training->id);
        $this->get(route('trainings.participants', $training))->assertOk()->assertSee('Download Hasil Import');
        $this->get(route('participants.import-result', $training))->assertOk()->assertDownload();
        $this->assertDatabaseHas('participants', ['training_id' => $training->id, 'nip_nik' => '3333', 'registration_status' => 'approved']);
    }
}