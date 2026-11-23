<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingFundingFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cooperation_and_funding_are_validated_and_saved_on_create_and_edit(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($admin)->get(route('trainings.create'))->assertOk()
            ->assertSee('Kerjasama/Pengiriman')->assertSee('APBD Kab/Kota');

        $data = [
            'nama_pelatihan' => 'Uji Anggaran', 'bidang' => 'Bidang A', 'program_evaluasi' => 'Teknis',
            'model' => 'standar', 'metode' => 'klasikal', 'lokasi' => 'BPSDM', 'angkatan' => 'I',
            'jumlah_peserta' => 10, 'jp' => 10, 'tgl_mulai' => '2026-11-20', 'tgl_selesai' => '2026-11-22',
            'kerjasama' => 'Kerjasama/Fasilitasi', 'sumber_anggaran' => 'APBN',
        ];
        $this->post(route('trainings.store'), array_replace($data, ['kerjasama' => 'Opsional lain']))
            ->assertSessionHasErrors('kerjasama');
        $this->post(route('trainings.store'), $data)->assertSessionHasNoErrors()->assertRedirect(route('trainings.index'));

        $training = Training::where('nama_pelatihan', $data['nama_pelatihan'])->firstOrFail();
        $this->assertSame('Kerjasama/Fasilitasi', $training->kerjasama);
        $this->get(route('trainings.edit', $training))->assertOk()->assertSee('value="APBN" selected', false);
        $this->put(route('trainings.update', $training), array_replace($data, ['kerjasama' => '', 'sumber_anggaran' => 'APBD']))
            ->assertSessionHasNoErrors();
        $this->assertNull($training->fresh()->kerjasama);
        $this->assertSame('APBD', $training->fresh()->sumber_anggaran);
    }
}
