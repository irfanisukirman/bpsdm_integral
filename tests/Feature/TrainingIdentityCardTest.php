<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\Training;
use App\Models\TrainingIdentityCardSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrainingIdentityCardTest extends TestCase
{
    use RefreshDatabase;

    private function training(User $admin): Training
    {
        return Training::create([
            'nama_pelatihan' => 'Pelatihan ID Card', 'bidang' => $admin->bidang,
            'model' => 'standar', 'metode' => 'klasikal', 'lokasi' => 'BPSDM',
            'angkatan' => 'I', 'jumlah_peserta' => 30, 'jp' => 10,
            'tgl_mulai' => now()->toDateString(), 'tgl_selesai' => now()->addDay()->toDateString(),
            'created_by' => $admin->id,
        ]);
    }

    public function test_admin_bidang_can_configure_identity_card_for_own_training(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin_bidang']);
        $training = $this->training($admin);

        $this->actingAs($admin)->get(route('training-id-cards.settings', $training))
            ->assertOk()->assertSee('Kelola ID Card Peserta');
        $this->put(route('training-id-cards.settings.update', $training), [
            'enabled' => '1', 'primary_color' => '#123456', 'accent_color' => '#F4B740',
            'text_color' => '#FFFFFF', 'background_opacity' => 70,
            'logo' => UploadedFile::fake()->image('logo.png', 300, 300),
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('training_identity_card_settings', [
            'training_id' => $training->id, 'enabled' => 1,
            'primary_color' => '#123456', 'background_opacity' => 70,
        ]);
    }

    public function test_approved_participant_can_edit_photo_and_download_pdf_when_enabled(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin_bidang']);
        $training = $this->training($admin);
        $user = User::factory()->create(['role' => 'participant', 'nip_nik' => '1987654321']);
        $participant = Participant::create([
            'training_id' => $training->id, 'user_id' => $user->id, 'nip_nik' => $user->nip_nik,
            'name' => 'Peserta Contoh', 'jabatan' => 'Analis', 'instansi' => 'BPSDM',
            'registration_status' => 'approved',
        ]);
        TrainingIdentityCardSetting::create(['training_id' => $training->id, 'enabled' => true]);

        $this->actingAs($user)->get(route('participant.identity-card.edit', $training))
            ->assertOk()->assertSee('ID Card Peserta')->assertSee('Peserta Contoh');
        $this->put(route('participant.identity-card.photo', $training), [
            'photo' => UploadedFile::fake()->image('foto.jpg', 600, 800),
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('participant_identity_cards', ['participant_id' => $participant->id]);
        $this->get(route('participant.identity-card.pdf', $training))->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->get(route('participant.identity-card.image', [$training, 'format' => 'png']))->assertOk()
            ->assertHeader('content-type', 'image/png');
        $this->get(route('participant.identity-card.image', [$training, 'format' => 'jpg']))->assertOk()
            ->assertHeader('content-type', 'image/jpeg');
    }
}