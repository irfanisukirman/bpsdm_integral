<?php
namespace Tests\Feature;

use App\Models\{Training, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingExecutionNoteTest extends TestCase
{
    use RefreshDatabase;

    private function training(User $user): Training
    {
        return Training::create(['nama_pelatihan'=>'Uji Catatan','bidang'=>$user->bidang,'model'=>'standar','metode'=>'klasikal','lokasi'=>'BPSDM','angkatan'=>'I','jumlah_peserta'=>10,'jp'=>10,'tgl_mulai'=>'2026-11-01','tgl_selesai'=>'2026-11-02','created_by'=>$user->id]);
    }

    public function test_manager_can_edit_and_delete_existing_note_without_changing_author(): void
    {
        $admin=User::factory()->create(['role'=>'admin_bidang','bidang'=>'Bidang A']);
        $training=$this->training($admin);
        $note=$training->executionNotes()->create(['created_by'=>$admin->id,'title'=>'Catatan lama','note'=>'Isi lama']);
        $this->actingAs($admin)->get(route('trainings.execution-notes.index',$training))->assertOk()->assertSee('Edit')->assertSee('Hapus');
        $this->get(route('trainings.execution-notes.index',[$training,'edit'=>$note->id]))->assertOk()->assertSee('Catatan lama')->assertSee('Isi lama')->assertSee('Simpan Perubahan');
        $this->put(route('trainings.execution-notes.update',[$training,$note]),['note_title'=>'Catatan baru','note_content'=>'Isi baru'])->assertRedirect(route('trainings.execution-notes.index',$training));
        $this->assertSame('Isi baru',$note->fresh()->note);$this->assertSame($admin->id,$note->fresh()->created_by);
        $this->put(route('trainings.execution-notes.update',[$training,$note]),['note_title'=>'','note_content'=>''])->assertSessionHasErrors(['note_title','note_content']);
        $this->delete(route('trainings.execution-notes.destroy',[$training,$note]))->assertRedirect(route('trainings.execution-notes.index',$training));
        $this->assertModelMissing($note);
    }

    public function test_foreign_field_and_mismatched_training_cannot_modify_notes(): void
    {
        $admin=User::factory()->create(['role'=>'admin_bidang','bidang'=>'Bidang A']);$training=$this->training($admin);$other=$this->training($admin);
        $note=$training->executionNotes()->create(['created_by'=>$admin->id,'title'=>'Rahasia','note'=>'Isi']);
        $outsider=User::factory()->create(['role'=>'admin_bidang','bidang'=>'Bidang B']);
        $this->actingAs($outsider)->put(route('trainings.execution-notes.update',[$training,$note]),['note_title'=>'Ubah','note_content'=>'Ubah'])->assertForbidden();
        $this->delete(route('trainings.execution-notes.destroy',[$training,$note]))->assertForbidden();
        $this->actingAs($admin)->put(route('trainings.execution-notes.update',[$other,$note]),['note_title'=>'Ubah','note_content'=>'Ubah'])->assertNotFound();
        $this->delete(route('trainings.execution-notes.destroy',[$other,$note]))->assertNotFound();
        $this->get(route('trainings.execution-notes.index',[$other,'edit'=>$note->id]))->assertNotFound();
        $this->assertModelExists($note);
    }
}
