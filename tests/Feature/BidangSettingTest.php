<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\FinanceBudgetLine;
use App\Models\Question;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BidangSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_rename_and_all_unit_references_follow_without_changing_access(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $unit = Bidang::where('original_name', Bidang::DEFAULTS[3])->firstOrFail();
        $old = $unit->name;
        $manager = User::factory()->create(['role' => 'admin_bidang', 'bidang' => $old]);
        $training = Training::create(['nama_pelatihan' => 'Uji Bidang', 'bidang' => $old, 'model' => 'standar', 'metode' => 'klasikal', 'lokasi' => 'BPSDM', 'angkatan' => 'I', 'jumlah_peserta' => 10, 'jp' => 10, 'tgl_mulai' => '2026-01-01', 'tgl_selesai' => '2026-01-02', 'created_by' => $admin->id]);
        $question = Question::create(['question_text' => 'Pertanyaan', 'category' => 'l34_mandiri', 'sub_category' => 'Tujuan', 'type' => 'dropdown', 'options' => ['Tidak Sesuai', 'Kurang Sesuai', 'Sesuai', 'Sangat Sesuai'], 'bidang' => $old, 'training_type' => $old, 'evaluation_program' => 'PKP', 'training_method' => 'all']);
        $line = FinanceBudgetLine::create(['year' => 2026, 'bidang' => $old, 'level' => 'activity', 'description' => 'Anggaran', 'created_by' => $admin->id]);
        $this->actingAs($admin)->get(route('settings.bidang.index'))->assertOk()->assertSee('Kelola Bidang')->assertSee('Ganti Nama');
        $this->put(route('settings.bidang.update', $unit), ['name' => 'Bidang Manajerial Baru'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Bidang Manajerial Baru', $manager->fresh()->bidang);
        $this->assertSame('Bidang Manajerial Baru', $training->fresh()->bidang);
        $this->assertSame('Bidang Manajerial Baru', $question->fresh()->bidang);
        $this->assertSame('Bidang Manajerial Baru', $question->fresh()->training_type);
        $this->assertSame('Bidang Manajerial Baru', $line->fresh()->bidang);
        $this->assertSame('Bidang Manajerial Baru', Bidang::resolve($old));
        $this->assertNotContains($old, Bidang::names());
        $this->actingAs($manager->fresh())->get(route('trainings.edit', $training))->assertOk()->assertSee('Bidang Manajerial Baru');
        $this->actingAs($admin)->get(route('trainings.create'))->assertOk()->assertSee('Bidang Manajerial Baru')->assertDontSee('value="'.$old.'"', false);
    }

    public function test_used_units_cannot_be_deleted_unused_units_disappear_and_names_are_validated(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $unit = Bidang::firstOrFail();
        User::factory()->create(['role' => 'admin_bidang', 'bidang' => $unit->name]);
        $this->actingAs($admin)->delete(route('settings.bidang.destroy', $unit))->assertSessionHasErrors('bidang');
        $this->assertModelExists($unit);
        $unused = Bidang::where('original_name', 'Sekretariat')->firstOrFail();
        $this->delete(route('settings.bidang.destroy', $unused))->assertSessionHasNoErrors();
        $this->assertModelMissing($unused);
        $this->assertNotContains('Sekretariat', Bidang::names());
        foreach (['', str_repeat('a', 256), 'Semua Bidang', 'Pengelola Aset', Bidang::DEFAULTS[1]] as $name) {
            $this->put(route('settings.bidang.update', $unit), ['name' => $name])->assertSessionHasErrors('name');
        }
        $this->assertSame(Bidang::DEFAULTS[0], $unit->fresh()->name);
    }

    public function test_other_roles_cannot_manage_unit_names(): void
    {
        $unit = Bidang::firstOrFail();
        foreach (['admin_bidang', 'pengelola_keuangan', 'participant'] as $role) {
            $user = User::factory()->create(['role' => $role, 'bidang' => $unit->name]);
            $this->actingAs($user)->get(route('settings.bidang.index'))->assertForbidden();
            $this->put(route('settings.bidang.update', $unit), ['name' => 'Nama lain'])->assertForbidden();
            $this->delete(route('settings.bidang.destroy',$unit))->assertForbidden();
        }
    }
}
