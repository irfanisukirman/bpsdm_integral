<?php
namespace Tests\Feature;

use App\Models\{Training,User,Folder,File,TrainingActivityDocumentation};
use App\Services\TrainingDocumentArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Storage,Route};
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TrainingDocumentArchiveTest extends TestCase
{
 use RefreshDatabase;
 private function training(User $user,string $name='Pelatihan Arsip'): Training
 {
  return Training::create(['nama_pelatihan'=>$name,'bidang'=>'Bidang A','program_evaluasi'=>'PKP','model'=>'standar','metode'=>'klasikal','lokasi'=>'BPSDM','angkatan'=>'I','jumlah_peserta'=>10,'jp'=>10,'tgl_mulai'=>'2026-01-01','tgl_selesai'=>'2026-01-02','created_by'=>$user->id]);
 }
 public function test_archive_preserves_each_content_and_training_without_overwriting_or_repeating_identical_files(): void
 {
  Storage::fake('local');$user=User::factory()->create(['role'=>'superadmin']);$this->actingAs($user);
  $one=$this->training($user);$two=$this->training($user,'Pelatihan Kedua');$archive=app(TrainingDocumentArchiveService::class);
  $a=$archive->store($one,'LAPORAN','Laporan.docx','versi pertama');
  $same=$archive->store($one,'HASIL UNDUHAN','Laporan.docx','versi pertama');
  $b=$archive->store($one,'LAPORAN','Laporan.docx','versi kedua');
  $c=$archive->store($two,'LAPORAN','Laporan.docx','pelatihan kedua');
  $this->assertSame($a->id,$same->id);$this->assertNotSame($a->file_path,$b->file_path);$this->assertNotSame($a->file_path,$c->file_path);
  $this->assertSame('versi pertama',Storage::disk('local')->get($a->file_path));
  $this->assertSame('versi kedua',Storage::disk('local')->get($b->file_path));
  $this->assertSame(3,File::count());$this->assertFalse((bool)$archive->root($one)->is_public);
  $this->get(route('documents.file.download',$a))->assertOk()->assertDownload('Laporan.docx');
  $this->actingAs(User::factory()->create(['role'=>'admin_bidang','bidang'=>'Bidang Lain']))->get(route('documents.file.download',$a))->assertForbidden();
 }
 public function test_training_folder_syncs_private_historical_uploads_and_keeps_access_scoped(): void
 {
  Storage::fake('local');$user=User::factory()->create(['role'=>'superadmin']);$training=$this->training($user);
  Storage::disk('local')->put('activity-documentation/photo.jpg','foto-uji');
  TrainingActivityDocumentation::create(['training_id'=>$training->id,'title'=>'Dokumentasi kelas','category'=>'learning','file_path'=>'activity-documentation/photo.jpg','uploaded_by'=>$user->id]);
  $this->actingAs($user)->get(route('documents.training',$training))->assertRedirect();
  $root=Folder::where('training_id',$training->id)->whereNull('parent_id')->firstOrFail();
  $this->get(route('documents.training',$training))->assertRedirect(route('documents.index',['folder'=>$root->id]));
  $this->assertSame(1,File::count());$this->assertSame('local',File::first()->storage_disk);
  $this->get(route('documents.index',['folder'=>$root->id]))->assertOk()->assertSee('DOKUMENTASI KEGIATAN');
  $this->actingAs(User::factory()->create(['role'=>'admin_bidang','bidang'=>'Bidang Lain']))->get(route('documents.training',$training))->assertForbidden();
 }
 public function test_download_stream_and_upload_are_automatically_captured_in_training_folder(): void
 {
  Storage::fake('local');$user=User::factory()->create(['role'=>'superadmin']);$training=$this->training($user);$this->actingAs($user);
  Route::middleware('web')->get('trainings/{id}/archive-test',fn()=>response()->streamDownload(fn()=>print('isi dokumen unduh'),'Hasil Uji.pdf'));
  $response=$this->get('/trainings/'.$training->id.'/archive-test')->assertOk();
  $this->assertSame('isi dokumen unduh',$response->streamedContent());
  $file=File::where('display_name','Hasil Uji.pdf')->firstOrFail();
  $this->assertSame('isi dokumen unduh',Storage::disk('local')->get($file->file_path));
  Route::middleware('web')->post('trainings/{id}/archive-upload-test',fn()=>response('ok'));
  $this->post('/trainings/'.$training->id.'/archive-upload-test',['attachment'=>UploadedFile::fake()->createWithContent('Dokumen Uji.txt','isi unggahan')])->assertOk();
  $this->assertDatabaseHas('files',['display_name'=>'Dokumen Uji.txt','storage_disk'=>'local']);
 }
 public function test_private_archive_versions_can_be_downloaded_and_restored_after_a_new_upload(): void
 {
  Storage::fake('local');Storage::fake('public');$user=User::factory()->create(['role'=>'superadmin']);$this->actingAs($user);
  $training=$this->training($user);$file=app(TrainingDocumentArchiveService::class)->store($training,'LAPORAN','Versi.txt','isi awal');
  $this->post(route('documents.upload'),['folder_id'=>$file->folder_id,'attachments'=>[UploadedFile::fake()->createWithContent('Versi.txt','isi pengganti')]])->assertRedirect();
  $version=$file->versions()->firstOrFail();$this->assertSame('local',$version->storage_disk);$this->assertSame('public',$file->fresh()->storage_disk);
  $this->get(route('documents.file-versions.download',$version))->assertOk();
  $this->post(route('documents.file-versions.restore',$version))->assertRedirect();
  $file=$file->fresh();$this->assertSame('local',$file->storage_disk);$this->assertSame('isi awal',Storage::disk('local')->get($file->file_path));
 }

 public function test_editing_training_renames_only_root_and_preserves_category_and_person_names(): void
 {
  $user=User::factory()->create(['role'=>'superadmin']);$this->actingAs($user);$training=$this->training($user);
  $root=app(TrainingDocumentArchiveService::class)->root($training);
  $category=Folder::create(['training_id'=>$training->id,'parent_id'=>$root->id,'name'=>'KELENGKAPAN PESERTA','bidang'=>$training->bidang,'user_id'=>$user->id]);
  $person=Folder::create(['training_id'=>$training->id,'parent_id'=>$category->id,'name'=>'PESERTA UJI','bidang'=>$training->bidang,'user_id'=>$user->id]);
  $data=$training->only(['bidang','program_evaluasi','lokasi','angkatan','jumlah_peserta','jp','tgl_mulai','tgl_selesai','metode']);$data['nama_pelatihan']='Nama Pelatihan Baru';
  $this->put(route('trainings.update',$training),$data)->assertRedirect();
  $this->assertSame('Nama Pelatihan Baru - Angkatan I',$root->fresh()->name);
  $this->assertSame('KELENGKAPAN PESERTA',$category->fresh()->name);$this->assertSame('PESERTA UJI',$person->fresh()->name);
 }
 public function test_folder_repair_restores_categories_without_deleting_files_or_changing_ownership(): void
 {
  Storage::fake('local');$user=User::factory()->create(['role'=>'superadmin']);$training=$this->training($user);$archive=app(TrainingDocumentArchiveService::class);$root=$archive->root($training);
  $target=Folder::create(['training_id'=>$training->id,'parent_id'=>$root->id,'name'=>'LAPORAN EVALUASI LEVEL 1 DAN 2','bidang'=>$training->bidang,'user_id'=>$user->id]);
  $owner=User::factory()->create();
  $old=Folder::create(['training_id'=>$training->id,'parent_id'=>$root->id,'name'=>$root->name,'bidang'=>$training->bidang,'user_id'=>$owner->id,'is_public'=>false]);
  $file=File::create(['folder_id'=>$old->id,'display_name'=>'LAPORAN_EVALUASI_LV1_LV2_Uji.docx','file_path'=>'documents/old.docx','file_type'=>'docx','file_size'=>3,'user_id'=>$owner->id]);
  $source=$file->fresh()->getAttributes();
  $service=app(\App\Services\TrainingFolderRepairService::class);$preview=$service->repair($training);
  $this->assertSame($root->name,$old->fresh()->name);$this->assertCount(1,$preview['changes']);
  $result=$service->repair($training,true);
  $this->assertTrue(Storage::disk('local')->exists($result['backup']));
  $this->assertSame($target->id,$old->fresh()->parent_id);$this->assertStringStartsWith('Arsip sebelumnya',$old->fresh()->name);
  $this->assertSame($owner->id,$old->fresh()->user_id);$this->assertSame($source,$file->fresh()->getAttributes());$this->assertSame(1,File::count());
  $this->assertSame([],$service->repair($training)['changes']);
 }

}
