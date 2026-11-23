<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingLmsLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_unconfigured_lms_card_opens_the_form_and_link_can_be_saved(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $training = Training::create([
            'nama_pelatihan' => 'Pelatihan Uji LMS', 'bidang' => 'Bidang A',
            'model' => 'standar', 'metode' => 'klasikal', 'lokasi' => 'BPSDM',
            'angkatan' => 'I', 'jumlah_peserta' => 10, 'jp' => 10,
            'tgl_mulai' => '2026-11-01', 'tgl_selesai' => '2026-11-02', 'created_by' => $admin->id,
        ]);
        $manage = route('trainings.manage', $training);
        $update = route('trainings.set_lms', $training);
        $target = 'modalLms'.$training->id;

        $this->actingAs($admin)->get($manage)->assertOk()
            ->assertSee('Akses LMS / Zoom')->assertSee('Belum diatur')
            ->assertSee('data-bs-toggle="modal" data-bs-target="#'.$target.'"', false)
            ->assertSee('id="'.$target.'"', false);

        $this->from($manage)->put($update, ['link_lms' => 'tautan salah'])
            ->assertRedirect($manage)->assertSessionHasErrors('link_lms');
        $this->get($manage)->assertOk()->assertSee("document.getElementById('".$target."')).show()", false);

        $link = 'https://example.com/pelatihan';
        $this->from($manage)->put($update, ['link_lms' => $link])
            ->assertRedirect($manage)->assertSessionHasNoErrors();
        $this->assertSame($link, $training->fresh()->link_lms);
    }
}
