<?php

namespace Tests\Feature;

use App\Models\ActivityAttendanceForm;
use App\Models\ActivityAttendanceQuestion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActivityAttendanceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_bidang_can_create_and_only_access_own_forms(): void
    {
        $admin=User::factory()->create(['role'=>'admin_bidang','bidang'=>'Bidang A']);
        $other=ActivityAttendanceForm::create(['public_token'=>(string)Str::uuid(),'title'=>'Form Bidang B','bidang'=>'Bidang B','status'=>'draft','created_by'=>$admin->id]);

        $this->actingAs($admin)->post(route('activity-attendance.store'),[
            'title'=>'Rapat Bidang A','bidang'=>'Bidang B','status'=>'draft',
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_attendance_forms',['title'=>'Rapat Bidang A','bidang'=>'Bidang A']);
        $own = ActivityAttendanceForm::where('title', 'Rapat Bidang A')->firstOrFail();
        $this->actingAs($admin)->get(route('activity-attendance.edit', $own))->assertOk()->assertSee('Pengaturan Form');
        $this->actingAs($admin)->get(route('activity-attendance.edit',$other))->assertForbidden();
    }

    public function test_question_options_are_saved_without_helper_fields(): void
    {
        $admin=User::factory()->create(['role'=>'superadmin']);
        $form=ActivityAttendanceForm::create(['public_token'=>(string)Str::uuid(),'title'=>'Form','status'=>'draft','created_by'=>$admin->id]);

        $this->actingAs($admin)->post(route('activity-attendance.questions.store',$form),[
            'label'=>'Nama Unit','type'=>'dropdown','is_required'=>1,'option_source'=>'manual','options_text'=>"Unit A\nUnit B",'sort_order'=>1,
        ])->assertRedirect();

        $question=$form->questions()->first();
        $this->assertSame(['Unit A','Unit B'],$question->options);
    }

    public function test_admin_can_reorder_questions_with_drag_and_drop_endpoint(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $form = ActivityAttendanceForm::create([
            'public_token' => (string) Str::uuid(),
            'title' => 'Form Urutan',
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);
        $first = $form->questions()->create(['label' => 'Pertama', 'type' => 'short_text', 'sort_order' => 1]);
        $second = $form->questions()->create(['label' => 'Kedua', 'type' => 'short_text', 'sort_order' => 2]);
        $third = $form->questions()->create(['label' => 'Ketiga', 'type' => 'short_text', 'sort_order' => 3]);

        $this->actingAs($admin)->postJson(route('activity-attendance.questions.reorder', $form), [
            'question_ids' => [$third->id, $first->id, $second->id],
        ])->assertOk()->assertJsonPath('message', 'Urutan pertanyaan berhasil disimpan.');

        $this->assertSame(
            [$third->id, $first->id, $second->id],
            $form->questions()->pluck('id')->all()
        );
    }
    public function test_public_can_submit_text_checkbox_and_signature(): void
    {
        Storage::fake('local');
        $form=ActivityAttendanceForm::create(['public_token'=>(string)Str::uuid(),'title'=>'Rapat','status'=>'open']);
        $name=$form->questions()->create(['label'=>'Nama','type'=>'short_text','is_required'=>true,'sort_order'=>1]);
        $check=$form->questions()->create(['label'=>'Kehadiran','type'=>'checkbox','options'=>['Hadir','Setuju'],'is_required'=>true,'sort_order'=>2]);
        $signature=$form->questions()->create(['label'=>'Tanda Tangan','type'=>'signature','is_required'=>true,'sort_order'=>3]);

        $this->get(route('activity-attendance.public.show', $form->public_token))
            ->assertOk()
            ->assertSee('Progres pengisian')
            ->assertSee('Tanda Tangan');

        $png='data:image/png;base64,'.base64_encode("\x89PNG\r\n\x1a\nTEST");
        $this->post(route('activity-attendance.public.store',$form->public_token),[
            'answers'=>[$name->id=>'Budi',$check->id=>['Hadir'],$signature->id=>$png],
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_attendance_responses',['activity_attendance_form_id'=>$form->id]);
        $this->assertDatabaseHas('activity_attendance_answers',['activity_attendance_question_id'=>$name->id,'value_text'=>'Budi']);
        $answer=$signature->answers()->first();
        Storage::disk('local')->assertExists($answer->file_path);
    }
    public function test_admin_can_duplicate_and_download_second_phase_outputs(): void
    {
        Storage::fake('local');
        $admin=User::factory()->create(['role'=>'superadmin']);
        $form=ActivityAttendanceForm::create(['public_token'=>(string)Str::uuid(),'title'=>'Rapat Export','status'=>'open','created_by'=>$admin->id]);
        $question=$form->questions()->create(['label'=>'Nama','type'=>'short_text','is_required'=>true,'sort_order'=>1]);
        $fileQuestion=$form->questions()->create(['label'=>'Lampiran','type'=>'file','sort_order'=>2]);
        $response=$form->responses()->create(['response_token'=>(string)Str::uuid(),'submitted_at'=>now()]);
        $response->answers()->create(['activity_attendance_question_id'=>$question->id,'value_text'=>'Budi']);
        Storage::disk('local')->put('activity-attendance/test/file.pdf','pdf-test');
        $response->answers()->create(['activity_attendance_question_id'=>$fileQuestion->id,'file_path'=>'activity-attendance/test/file.pdf','original_name'=>'file.pdf','mime_type'=>'application/pdf','file_size'=>8]);

        $this->actingAs($admin)->post(route('activity-attendance.duplicate',$form))->assertRedirect();
        $copy=ActivityAttendanceForm::where('title','Rapat Export - Salinan')->firstOrFail();
        $this->assertSame(2,$copy->questions()->count());
        $this->assertSame(0,$copy->responses()->count());

        $this->actingAs($admin)->get(route('activity-attendance.responses',$form))
            ->assertOk()
            ->assertSee('Statistik & Respons Presensi', false)
            ->assertSee('responseTrendChart', false);
        $this->actingAs($admin)->get(route('activity-attendance.qr',$form))->assertOk()->assertHeader('content-type','image/png');
        $this->actingAs($admin)->get(route('activity-attendance.export.excel',$form))->assertOk();
        $this->actingAs($admin)->get(route('activity-attendance.export.pdf',$form))->assertOk()->assertHeader('content-type','application/pdf');
        $this->actingAs($admin)->get(route('activity-attendance.attachments',$form))->assertOk();
    }

    public function test_public_receives_clear_validation_message_when_file_is_too_large(): void
    {
        Storage::fake('local');
        $form = ActivityAttendanceForm::create([
            'public_token' => (string) Str::uuid(),
            'title' => 'Presensi Upload',
            'status' => 'open',
        ]);
        $question = $form->questions()->create([
            'label' => 'Dokumen Pendukung',
            'type' => 'file',
            'is_required' => true,
            'max_file_size_kb' => 1024,
            'allowed_extensions' => 'pdf,jpg',
            'sort_order' => 1,
        ]);

        $response = $this->from(route('activity-attendance.public.show', $form->public_token))
            ->post(route('activity-attendance.public.store', $form->public_token), [
                'answers' => [
                    $question->id => UploadedFile::fake()->create('dokumen.pdf', 2048, 'application/pdf'),
                ],
            ]);

        $response->assertRedirect(route('activity-attendance.public.show', $form->public_token));
        $response->assertSessionHasErrors('answers.'.$question->id);
        $this->assertStringContainsString(
            'Maksimal 1 MB',
            session('errors')->first('answers.'.$question->id)
        );
        $this->assertDatabaseCount('activity_attendance_responses', 0);
    }
}
