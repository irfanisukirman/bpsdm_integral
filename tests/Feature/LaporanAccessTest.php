<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_pppk_pw_user_can_open_laporan_and_sees_menu(): void
    {
        $user = User::factory()->create(['role' => 'participant', 'status_kepegawaian' => 'PPPK-PW']);

        $this->assertTrue($user->isPppkPw());
        $this->assertTrue($user->canAccessLaporan());

        $this->actingAs($user)->get(route('laporan.index'))
            ->assertOk()
            ->assertSee('Pelaporan')
            ->assertSee('Halaman Laporan');
    }

    public function test_non_pppk_pw_user_is_forbidden_on_laporan(): void
    {
        $user = User::factory()->create(['role' => 'participant', 'status_kepegawaian' => 'PNS']);

        $this->assertFalse($user->isPppkPw());
        $this->assertFalse($user->canAccessLaporan());

        $this->actingAs($user)->get(route('laporan.index'))->assertForbidden();
    }

    public function test_superadmin_can_open_laporan(): void
    {
        $user = User::factory()->create(['role' => 'superadmin', 'status_kepegawaian' => 'PNS']);

        $this->assertTrue($user->canAccessLaporan());

        $this->actingAs($user)->get(route('laporan.index'))->assertOk();
    }

    public function test_registering_with_pppk_pw_status_shows_laporan_menu(): void
    {
        $this->post(route('register'), [
            'name' => 'Peserta P3K PW',
            'email' => 'p3kpw@example.test',
            'email_confirmation' => 'p3kpw@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'peserta',
            'nip_nik' => '3273010101900001',
            'whatsapp' => '628123456789',
            'gender' => 'Laki-Laki',
            'status_kepegawaian' => 'PPPK-PW',
            'birth_place' => 'Bandung',
            'birth_date' => '1990-01-01',
            'jabatan' => 'Analis',
            'instansi' => 'BPSDM Provinsi Jawa Barat',
            'provinsi' => 'JAWA BARAT',
            'kota' => 'KOTA BANDUNG',
            'kecamatan' => 'COBLONG',
            'kelurahan' => 'DAGO',
            'address' => 'Jl. Contoh No. 1, Bandung',
            'latitude' => '-6.8914800',
            'longitude' => '107.6106600',
        ])->assertRedirect(route('participant.dashboard'));

        $user = User::where('email', 'p3kpw@example.test')->firstOrFail();

        $this->assertSame('PPPK-PW', $user->status_kepegawaian);
        $this->assertTrue($user->canAccessLaporan());

        $this->actingAs($user)->get(route('laporan.index'))
            ->assertOk()
            ->assertSee('Pelaporan');
    }

    public function test_scope_filters_users_and_participants_by_pppk_pw(): void
    {
        User::factory()->create(['status_kepegawaian' => 'PPPK-PW']);
        User::factory()->create(['status_kepegawaian' => 'PNS']);

        $this->assertSame(1, User::pppkPw()->count());

        $training = \App\Models\Training::create([
            'bidang' => 'Bidang A', 'nama_pelatihan' => 'Pelatihan Uji Coba',
            'model' => 'standar', 'metode' => 'klasikal', 'lokasi' => 'Bandung',
            'angkatan' => '1', 'jumlah_peserta' => 10, 'jp' => 8,
            'tgl_mulai' => '2026-01-01', 'tgl_selesai' => '2026-01-02',
        ]);
        $training->participants()->create(['nip_nik' => '1001', 'name' => 'Satu', 'status_kepegawaian' => 'PPPK-PW']);
        $training->participants()->create(['nip_nik' => '1002', 'name' => 'Dua', 'status_kepegawaian' => 'PPPK']);

        $this->assertSame(1, \App\Models\Participant::pppkPw()->count());
    }
}
