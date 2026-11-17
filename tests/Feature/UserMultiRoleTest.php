<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\EmploymentStatus;
use App\Models\FinanceBudgetLine;
use App\Models\Institution;
use App\Models\Training;
use App\Models\User;
use App\Support\UserRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserMultiRoleTest extends TestCase
{
    use RefreshDatabase;

    private function participant(): User
    {
        return User::factory()->create(['role' => 'participant', 'user_type' => 'peserta', 'user_type_status' => 'approved']);
    }

    public function test_superadmin_assigns_multiple_roles_with_independent_scopes_without_changing_identity(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $user = $this->participant();
        $before = $user->only(['role', 'user_type', 'user_type_status', 'username', 'password', 'nip_nik', 'bidang']);
        $roles = ['participant', 'admin_bidang', 'pengelola_keuangan', 'pengelola_magang', 'resepsionis', 'kasubag_pppk_pw'];
        $fields = Bidang::names();
        $this->actingAs($admin)->put(route('users.roles.update', $user), ['roles' => $roles, 'role_bidang' => ['admin_bidang' => $fields[1], 'pengelola_keuangan' => $fields[2]]])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($before, $user->fresh()->only(array_keys($before)));
        $this->assertEqualsCanonicalizing($roles, $user->fresh()->assignedRoles());
        $this->assertDatabaseHas('user_role_assignments', ['user_id' => $user->id, 'role' => 'admin_bidang', 'bidang' => $fields[1]]);
        $this->assertDatabaseHas('user_role_assignments', ['user_id' => $user->id, 'role' => 'pengelola_keuangan', 'bidang' => $fields[2]]);
        $this->get(route('users.index', ['category' => 'admin']))->assertOk()->assertSee($user->name)->assertSee('Kelola Peran')->assertSee('Pengelola Buku Tamu');
        $this->put(route('users.roles.update', $user), ['roles' => ['participant', 'resepsionis']])->assertSessionHasNoErrors();
        $this->assertEqualsCanonicalizing(['participant', 'resepsionis'], $user->fresh()->assignedRoles());
    }

    public function test_user_can_switch_between_assigned_management_and_participant_menus_without_changing_database_role(): void
    {
        $user = $this->participant();
        $user->roleAssignments()->create(['role' => 'resepsionis']);
        $user->roleAssignments()->create(['role' => 'pengelola_magang']);
        $this->actingAs($user)->get(route('guest-book.index'))->assertForbidden();
        $this->post(route('roles.switch'), ['role' => 'resepsionis'])->assertRedirect(route('guest-book.index'));
        $this->get(route('guest-book.index'))->assertOk()->assertSee('Akses Peran')->assertSee('Buku Tamu')->assertSee('Pengelola Magang/PKL');
        $this->get(route('users.index'))->assertForbidden();
        $this->assertSame('participant', $user->fresh()->primaryRole());
        $this->post(route('roles.switch'), ['role' => 'pengelola_magang'])->assertRedirect(route('internships.index'));
        $this->get(route('internships.index'))->assertOk();
        $this->post(route('roles.switch'), ['role' => 'participant'])->assertRedirect(route('participant.dashboard'));
        $this->get(route('participant.dashboard'))->assertOk()->assertSee('Dashboard Saya');
        $this->get(route('guest-book.index'))->assertForbidden();
    }

    public function test_context_applies_the_assigned_bidang_and_revocation_is_immediate(): void
    {
        $user = $this->participant();
        $fields = Bidang::names();
        DB::connection()->getPdo()->sqliteCreateFunction('YEAR', fn ($date) => (int) substr($date, 0, 4));
        $assignment = $user->roleAssignments()->create(['role' => 'admin_bidang', 'bidang' => $fields[1]]);
        foreach ([$fields[1] => 'Pelatihan Milik Tim', $fields[2] => 'Pelatihan Bidang Lain'] as $field => $name) {
            Training::create(['nama_pelatihan' => $name, 'bidang' => $field, 'model' => 'standar', 'metode' => 'klasikal', 'lokasi' => 'BPSDM', 'angkatan' => 'I', 'jumlah_peserta' => 10, 'jp' => 10, 'tgl_mulai' => '2026-01-01', 'tgl_selesai' => '2026-01-02', 'created_by' => $user->id]);
        }
        $this->actingAs($user)->post(route('roles.switch'), ['role' => 'admin_bidang'])->assertRedirect(route('dashboard'));
        $this->get(route('trainings.index'))->assertOk()->assertSee('Pelatihan Milik Tim')->assertDontSee('Pelatihan Bidang Lain');
        $assignment->delete();
        $this->get(route('trainings.execution-notes.index', Training::where('nama_pelatihan', 'Pelatihan Milik Tim')->firstOrFail()))->assertForbidden();
        $this->assertSame('participant', $user->fresh()->role);
    }

    public function test_roles_cannot_be_forged_or_self_assigned_and_primary_role_is_retained(): void
    {
        $user = $this->participant();
        $admin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($user)->post(route('roles.switch'), ['role' => 'superadmin'])->assertForbidden();
        $this->put(route('users.roles.update', $user), ['roles' => ['participant', 'superadmin']])->assertForbidden();
        $this->withSession(['user_role_context' => ['user_id' => $user->id, 'role' => 'superadmin']])->get(route('users.index'))->assertForbidden();
        $this->actingAs($admin)->put(route('users.roles.update', $user), ['roles' => ['resepsionis']])->assertSessionHasErrors('roles');
        $this->put(route('users.roles.update', $user), ['roles' => ['participant', 'admin_bidang'], 'role_bidang' => ['admin_bidang' => 'Bidang tidak sah']])->assertSessionHasErrors('role_bidang.admin_bidang');
        $this->put(route('users.roles.update', $user), ['roles' => ['participant', 'root']])->assertSessionHasErrors('roles.1');
        $this->assertDatabaseCount('user_role_assignments', 0);
    }

    public function test_superadmin_can_use_participant_context_and_return_without_carrying_privileges(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $admin->roleAssignments()->create(['role' => 'participant']);
        $this->actingAs($admin)->post(route('roles.switch'), ['role' => 'participant'])->assertRedirect(route('participant.dashboard'));
        $this->get(route('users.index'))->assertForbidden();
        $this->post(route('roles.switch'), ['role' => 'superadmin'])->assertRedirect(route('dashboard'));
        $this->get(route('users.index'))->assertOk();
        $this->assertSame('superadmin', $admin->fresh()->role);
    }

    public function test_deleting_a_participant_with_management_roles_is_blocked(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $user = $this->participant();
        $user->roleAssignments()->create(['role' => 'resepsionis']);
        $this->actingAs($admin)->delete(route('users.destroy', $user))->assertSessionHas('error');
        $this->assertModelExists($user);
    }

    public function test_finance_and_asset_contexts_use_independent_scopes(): void
    {
        DB::connection()->getPdo()->sqliteCreateFunction('FIELD', fn ($value, ...$values) => array_search($value, $values, true) + 1);
        $fields = Bidang::names();
        $user = $this->participant();
        $originalBidang = $user->bidang;
        $user->roleAssignments()->create(['role' => 'pengelola_keuangan', 'bidang' => $fields[2]]);
        $user->roleAssignments()->create(['role' => 'admin_aset', 'bidang' => 'Pengelola Aset']);
        foreach ([$fields[1] => 'Anggaran Bidang Lain', $fields[2] => 'Anggaran Bidang Terpilih'] as $field => $label) {
            FinanceBudgetLine::create(['year' => 2026, 'bidang' => $field, 'level' => 'activity', 'description' => $label, 'created_by' => $user->id]);
        }
        $this->actingAs($user)->post(route('roles.switch'), ['role' => 'pengelola_keuangan'])->assertRedirect(route('finance.index'));
        $this->get(route('finance.index', ['year' => 2026, 'bidang' => $fields[1]]))->assertOk()->assertSee('Anggaran Bidang Terpilih')->assertDontSee('Anggaran Bidang Lain');
        $this->post(route('roles.switch'), ['role' => 'admin_aset'])->assertRedirect(route('assets.dashboard'));
        $this->get(route('assets.dashboard'))->assertOk();
        $this->assertSame($originalBidang, $user->fresh()->bidang);
    }

    public function test_added_teacher_can_complete_setup_and_switch_back_to_participant(): void
    {
        $user = $this->participant();
        $user->roleAssignments()->create(['role' => 'pengajar']);
        $this->assertTrue(User::eligibleTeachers()->whereKey($user->id)->exists());
        $this->assertTrue(validator(['teacher' => $user->id], ['teacher' => UserRoles::existsRule('pengajar')])->passes());
        $this->actingAs($user)->post(route('roles.switch'), ['role' => 'pengajar'])->assertRedirect(route('pengajar.index'));
        $this->get(route('pengajar.index'))->assertRedirect(route('pengajar.setup'));
        $this->get(route('pengajar.setup'))->assertOk();
        $this->post(route('roles.switch'), ['role' => 'participant'])->assertRedirect(route('participant.dashboard'));
        $this->get(route('participant.dashboard'))->assertOk();
        $this->assertSame('peserta', $user->fresh()->user_type);
    }

    public function test_added_managers_are_available_in_assignments_and_admin_username_login(): void
    {
        $user = $this->participant();
        $user->update(['username' => 'pengelola@example.test', 'nip_nik' => '1234567890', 'password' => 'secret-password']);
        $user->roleAssignments()->create(['role' => 'pengelola_magang']);
        $user->roleAssignments()->create(['role' => 'penandatangan']);
        $this->assertTrue(validator(['manager' => $user->id], ['manager' => UserRoles::existsRule('pengelola_magang')])->passes());
        $this->assertTrue(validator(['signer' => $user->id], ['signer' => UserRoles::existsRule('penandatangan')])->passes());
        $this->post(route('login'), ['username' => 'pengelola@example.test', 'password' => 'secret-password'])->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
        $admin = User::factory()->create(['role' => 'superadmin']);
        $response = $this->actingAs($admin)->get(route('internships.index'))->assertOk();
        $this->assertTrue($response->viewData('managers')->contains('id', $user->id));
    }

    public function test_imported_participant_cannot_bypass_required_password_change_via_management_context(): void
    {
        $user = $this->participant();
        $user->update(['must_complete_profile' => true, 'must_change_password' => true]);
        $user->roleAssignments()->create(['role' => 'resepsionis']);
        $this->actingAs($user)->withSession(['user_role_context' => ['user_id' => $user->id, 'role' => 'resepsionis']])->get(route('guest-book.index'))->assertRedirect(route('participant.profile.complete'));
        $this->get(route('participant.profile.complete'))->assertOk();
    }

    public function test_admin_can_complete_participant_profile_without_losing_primary_admin_role_or_bidang(): void
    {
        $bidang = Bidang::names()[1];
        $user = User::factory()->create(['role' => 'admin_bidang', 'bidang' => $bidang, 'user_type' => null]);
        $user->roleAssignments()->create(['role' => 'participant']);
        $institution = Institution::firstOrCreate(['name' => 'BPSDM Uji'], ['is_active' => true]);
        $status = EmploymentStatus::where('code', 'PNS')->firstOrFail();
        $this->actingAs($user)->post(route('roles.switch'), ['role' => 'participant'])->assertRedirect(route('participant.dashboard'));
        $this->post(route('participant.profile.store'), [
            'user_type' => 'peserta', 'nip' => '199001012020011001', 'nik' => '3201010101900001', 'whatsapp' => '628123456789', 'gender' => 'Laki-Laki',
            'birth_place' => 'Bandung', 'birth_date' => '1990-01-01', 'jabatan' => 'Analis', 'golongan' => 'III/a',
            'institution_id' => $institution->id, 'employment_status_id' => $status->id, 'provinsi' => 'JAWA BARAT', 'kota' => 'KOTA BANDUNG',
            'kecamatan' => 'COBLONG', 'kelurahan' => 'DAGO', 'address' => 'Jalan Uji', 'latitude' => -6.9, 'longitude' => 107.6,
        ])->assertSessionHasNoErrors()->assertRedirect(route('participant.dashboard'));
        $this->assertSame('admin_bidang', $user->fresh()->primaryRole());
        $this->assertSame($bidang, $user->fresh()->bidang);
        $this->get(route('participant.dashboard'))->assertOk();
    }

    public function test_last_secondary_superadmin_cannot_revoke_its_own_only_administrative_access(): void
    {
        $user = $this->participant();
        $user->roleAssignments()->create(['role' => 'superadmin']);
        $this->actingAs($user)->post(route('roles.switch'), ['role' => 'superadmin'])->assertRedirect(route('dashboard'));
        $this->put(route('users.roles.update', $user), ['roles' => ['participant']])->assertSessionHasErrors('roles');
        $this->assertTrue($user->fresh()->hasAssignedRole('superadmin'));
    }
}
