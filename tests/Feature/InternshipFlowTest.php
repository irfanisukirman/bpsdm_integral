<?php
namespace Tests\Feature;
use App\Models\InternshipParticipant;
use App\Models\InternshipProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
class InternshipFlowTest extends TestCase {
 use RefreshDatabase;
 public function test_registration_approval_and_login_flow():void {
  $admin=User::factory()->create(['role'=>'superadmin','bidang'=>null]);
  $program=InternshipProgram::create(['public_token'=>(string)Str::uuid(),'title'=>'PKL 2026','bidang'=>'Bidang A','status'=>'open','check_in_opens_at'=>'06:00','late_after'=>'07:30','check_out_opens_at'=>'16:00','created_by'=>$admin->id]);
  $this->get(route('internships.public.register',$program->public_token))->assertOk()->assertSee('Buat Akun INTEGRAL');
  $data=['name'=>'Budi PKL','student_number'=>'NIM001','major'=>'Informatika','institution'=>'Universitas Test','placement_unit'=>'Bidang A','start_date'=>today()->format('Y-m-d'),'end_date'=>today()->addMonth()->format('Y-m-d'),'email'=>'budi@example.test','password'=>'password123','password_confirmation'=>'password123'];
  $this->post(route('internships.public.store',$program->public_token),$data)->assertOk()->assertSee('Pendaftaran Terkirim');
  $participant=InternshipParticipant::firstOrFail();$this->assertSame('pending',$participant->status);
  $this->post(route('login'),['username'=>'NIM001','password'=>'password123'])->assertRedirect(route('login'));$this->assertGuest();
  $this->actingAs($admin)->put(route('internships.participants.approve',$participant),[])->assertRedirect();
  $this->post(route('logout'));$this->post(route('login'),['username'=>'NIM001','password'=>'password123'])->assertRedirect(route('internships.dashboard'));
  $this->get(route('internships.dashboard'))->assertOk()->assertSee('Selamat datang, Budi PKL');
 }
 public function test_admin_bidang_cannot_access_internship_management():void {
  $admin=User::factory()->create(['role'=>'admin_bidang','bidang'=>'Bidang A']);
  $other=InternshipProgram::create(['public_token'=>(string)Str::uuid(),'title'=>'Program B','bidang'=>'Bidang B','status'=>'draft','check_in_opens_at'=>'06:00','late_after'=>'07:30','check_out_opens_at'=>'16:00']);
  $this->actingAs($admin)->get(route('internships.index'))->assertForbidden();
  $this->actingAs($admin)->get(route('internships.show',$other))->assertForbidden();
 }

 public function test_intern_attendance_lateness_checkout_and_photo_cleanup():void {
  \Illuminate\Support\Facades\Storage::fake('local');
  \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-09-14 08:00:00','Asia/Jakarta'));
  $user=User::factory()->create(['role'=>'intern','user_type_status'=>'approved']);
  $program=InternshipProgram::create(['public_token'=>(string)Str::uuid(),'title'=>'PKL','status'=>'closed','check_in_opens_at'=>'06:00','late_after'=>'07:30','check_out_opens_at'=>'16:00']);
  $participant=$program->participants()->create(['user_id'=>$user->id,'name'=>'Intern','student_number'=>'X1','major'=>'TI','institution'=>'Kampus','placement_unit'=>'Unit','start_date'=>'2026-09-01','end_date'=>'2026-10-01','email'=>'i@test.local','status'=>'approved']);
  $this->actingAs($user)->post(route('internships.attendance.check-in'),['selfie'=>\Illuminate\Http\UploadedFile::fake()->image('masuk.jpg')])->assertRedirect();
  $attendance=$participant->attendances()->firstOrFail();$this->assertSame(30,$attendance->late_minutes);$inPath=$attendance->check_in_photo_path;\Illuminate\Support\Facades\Storage::disk('local')->assertExists($inPath);
  $this->post(route('internships.attendance.check-in'),['selfie'=>\Illuminate\Http\UploadedFile::fake()->image('ulang.jpg')])->assertSessionHasErrors('attendance');
  \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-09-14 16:01:00','Asia/Jakarta'));
  $this->post(route('internships.attendance.check-out'),['selfie'=>\Illuminate\Http\UploadedFile::fake()->image('pulang.jpg')])->assertRedirect();
  $attendance->refresh();$this->assertNotNull($attendance->check_out_at);$outPath=$attendance->check_out_photo_path;
  \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-09-22 02:00:00','Asia/Jakarta'));
  $this->artisan('internships:purge-photos')->assertSuccessful();
  $attendance->refresh();$this->assertNull($attendance->check_in_photo_path);$this->assertNull($attendance->check_out_photo_path);\Illuminate\Support\Facades\Storage::disk('local')->assertMissing($inPath);\Illuminate\Support\Facades\Storage::disk('local')->assertMissing($outPath);
  \Carbon\Carbon::setTestNow();
 }

 public function test_sick_note_requires_evidence_and_closes_attendance_for_the_day():void {
  \Illuminate\Support\Facades\Storage::fake('local');\Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-09-15 07:00','Asia/Jakarta'));
  $intern=User::factory()->create(['role'=>'intern','user_type_status'=>'approved']);$super=User::factory()->create(['role'=>'superadmin']);
  $program=InternshipProgram::create(['public_token'=>(string)Str::uuid(),'title'=>'PKL','status'=>'closed','check_in_opens_at'=>'06:00','late_after'=>'07:30','check_out_opens_at'=>'16:00']);
  $participant=$program->participants()->create(['user_id'=>$intern->id,'name'=>'Intern','student_number'=>'X2','major'=>'TI','institution'=>'Kampus','placement_unit'=>'Unit','start_date'=>'2026-09-01','end_date'=>'2026-10-01','email'=>'s@test.local','status'=>'approved']);
  $this->actingAs($intern)->post(route('internships.attendance.absence'),['type'=>'sick','note'=>'Sedang sakit dan perlu beristirahat'])->assertSessionHasErrors('evidence');
  $this->post(route('internships.attendance.absence'),['type'=>'sick','note'=>'Sedang sakit dan perlu beristirahat','evidence'=>\Illuminate\Http\UploadedFile::fake()->image('bukti.jpg')])->assertRedirect();
  $attendance=$participant->attendances()->firstOrFail();$this->assertSame('not_required',$attendance->review_status);$this->assertSame('sick',$attendance->status);
  $this->post(route('internships.attendance.check-in'),['selfie'=>\Illuminate\Http\UploadedFile::fake()->image('tidak-boleh.jpg')])->assertSessionHasErrors('attendance');
  $this->actingAs($super)->get(route('internships.show',$program))->assertOk()->assertSee('Catatan Izin & Sakit',false)->assertSee('Hadir administratif')->assertDontSee('Verifikasi keterangan dan bukti peserta.');
  \Carbon\Carbon::setTestNow();
 }

 public function test_superadmin_can_mark_participant_present_without_lateness():void {
  \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-09-15 14:45','Asia/Jakarta'));
  $admin=User::factory()->create(['role'=>'superadmin']);
  $program=InternshipProgram::create(['public_token'=>(string)Str::uuid(),'title'=>'PKL Admin','status'=>'closed','check_in_opens_at'=>'06:00','late_after'=>'07:30','check_out_opens_at'=>'16:00','created_by'=>$admin->id]);
  $participant=$program->participants()->create(['name'=>'Peserta Admin','student_number'=>'ADM-1','major'=>'TI','institution'=>'Kampus','placement_unit'=>'Unit','start_date'=>'2026-09-01','end_date'=>'2026-09-30','email'=>'admin-presensi@example.test','status'=>'approved']);
  $this->actingAs($admin)->get(route('internships.participants.attendance',$participant))->assertOk()->assertSee('Absenkan Peserta');
  $this->post(route('internships.participants.mark-present',$participant),['attendance_date'=>'2026-09-15','check_in_time'=>'14:45','note'=>'Kendala perangkat'])->assertRedirect()->assertSessionHas('success');
  $attendance=$participant->attendances()->firstOrFail();
  $this->assertSame('present',$attendance->status);$this->assertSame(0,$attendance->late_minutes);$this->assertSame('14:45',$attendance->check_in_at->format('H:i'));$this->assertSame($admin->id,$attendance->reviewed_by);
  $this->post(route('internships.participants.mark-present',$participant),['attendance_date'=>'2026-09-15','check_in_time'=>'15:30'])->assertSessionHasErrors('attendance_date');
  \Carbon\Carbon::setTestNow();
 }

 public function test_superadmin_can_edit_participant_and_reset_login_password():void {
  $admin=User::factory()->create(['role'=>'superadmin']);
  $user=User::factory()->create(['role'=>'intern','username'=>'NIM-LAMA','password'=>bcrypt('password-lama'),'user_type_status'=>'approved']);
  $program=InternshipProgram::create(['public_token'=>(string)Str::uuid(),'title'=>'PKL Edit','status'=>'closed','check_in_opens_at'=>'06:00','late_after'=>'07:30','check_out_opens_at'=>'16:00','created_by'=>$admin->id]);
  $participant=$program->participants()->create(['user_id'=>$user->id,'name'=>'Nama Lama','student_number'=>'NIM-LAMA','major'=>'TI','institution'=>'Kampus Lama','placement_unit'=>'Unit Lama','start_date'=>'2026-09-01','end_date'=>'2026-10-01','email'=>'lama@example.test','status'=>'approved']);

  $this->actingAs($admin)->put(route('internships.participants.update',$participant),[
   'name'=>'Nama Baru','student_number'=>'NIM-BARU','major'=>'Sistem Informasi','institution'=>'Kampus Baru','placement_unit'=>'Unit Baru','start_date'=>'2026-09-02','end_date'=>'2026-10-02','email'=>'baru@example.test',
  ])->assertRedirect()->assertSessionHas('success');

  $this->assertDatabaseHas('internship_participants',['id'=>$participant->id,'name'=>'Nama Baru','student_number'=>'NIM-BARU','placement_unit'=>'Unit Baru']);
  $this->assertDatabaseHas('users',['id'=>$user->id,'name'=>'Nama Baru','username'=>'NIM-BARU','bidang'=>'Unit Baru']);

  $this->put(route('internships.participants.password',$participant),[
   'password'=>'password-baru','password_confirmation'=>'password-baru',
  ])->assertRedirect()->assertSessionHas('success');

  $this->post(route('logout'));
  $this->post(route('login'),['username'=>'NIM-BARU','password'=>'password-baru'])->assertRedirect(route('internships.dashboard'));
 }

 public function test_superadmin_can_view_filter_and_export_participant_attendance():void {
  $admin=User::factory()->create(['role'=>'superadmin']);
  $program=InternshipProgram::create(['public_token'=>(string)Str::uuid(),'title'=>'PKL Rekap','status'=>'closed','check_in_opens_at'=>'06:00','late_after'=>'07:30','check_out_opens_at'=>'16:00','created_by'=>$admin->id]);
  $participant=$program->participants()->create(['name'=>'Peserta Rekap','student_number'=>'REKAP-01','major'=>'TI','institution'=>'Kampus','placement_unit'=>'Unit','start_date'=>'2026-09-01','end_date'=>'2026-10-01','email'=>'rekap@example.test','status'=>'approved']);
  $participant->attendances()->create(['attendance_date'=>'2026-09-10','status'=>'present','check_in_at'=>'2026-09-10 08:00:00','check_out_at'=>'2026-09-10 16:00:00','late_minutes'=>30]);
  $participant->attendances()->create(['attendance_date'=>'2026-09-11','status'=>'permission','note'=>'Keperluan keluarga','review_status'=>'approved']);

  $this->actingAs($admin)->get(route('internships.show',['program'=>$program,'from'=>'2026-09-01','to'=>'2026-09-30']))
   ->assertOk()->assertSee('Rekap Keseluruhan Presensi')->assertSee('Peserta Rekap')->assertSee('30 menit');
  $this->get(route('internships.recap.export',['program'=>$program,'from'=>'2026-09-01','to'=>'2026-09-30']))
   ->assertOk()->assertHeader('content-disposition');

  $this->get(route('internships.participants.attendance',['participant'=>$participant,'month'=>'2026-09']))
   ->assertOk()->assertSee('Riwayat Presensi')->assertSee('30 menit')->assertSee('Keperluan keluarga');

  $this->get(route('internships.participants.attendance',['participant'=>$participant,'month'=>'2026-09','status'=>'present']))
   ->assertOk()->assertSee('30 menit')->assertDontSee('Keperluan keluarga');

  $this->get(route('internships.participants.attendance.export',['participant'=>$participant,'month'=>'2026-09']))
   ->assertOk()->assertHeader('content-disposition');
 }

 public function test_admin_sets_final_grade_and_finished_intern_downloads_certificate():void {
  \Illuminate\Support\Facades\Storage::fake('local');
  \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-09-12 10:00','Asia/Jakarta'));
  $admin=User::factory()->create(['role'=>'superadmin']);
  $intern=User::factory()->create(['role'=>'intern','username'=>'CERT-01','user_type_status'=>'approved']);
  $program=InternshipProgram::create(['public_token'=>(string)Str::uuid(),'title'=>'PKL Sertifikat','status'=>'closed','check_in_opens_at'=>'06:00','late_after'=>'07:30','check_out_opens_at'=>'16:00','created_by'=>$admin->id]);
  $participant=$program->participants()->create(['user_id'=>$intern->id,'name'=>'Peserta Sertifikat','student_number'=>'CERT-01','major'=>'TI','institution'=>'Kampus Sertifikat','placement_unit'=>'Unit','start_date'=>'2026-09-01','end_date'=>'2026-09-10','email'=>'cert@example.test','status'=>'approved']);
  $this->actingAs($admin)->put(route('internships.participants.grade',$participant),['final_grade'=>'Sangat Baik'])->assertRedirect()->assertSessionHas('success');
  $this->assertSame('Sangat Baik',$participant->fresh()->final_grade);
  \Illuminate\Support\Facades\Storage::disk('local')->put('internships/certificates/'.$program->id.'/'.$participant->id.'.pdf','PDF dummy');
  $participant->update(['certificate_number'=>'001/MAGANG/2026','certificate_file_path'=>'internships/certificates/'.$program->id.'/'.$participant->id.'.pdf','certificate_generated_at'=>now()]);
  $this->actingAs($intern)->get(route('internships.dashboard'))->assertOk()->assertSee('Masa Magang Selesai')->assertSee('sedang disiapkan')->assertDontSee('Download Sertifikat PDF')->assertDontSee('Presensi Hari Ini');
  $this->get(route('internships.certificate.download'))->assertForbidden();
  $this->actingAs($admin)->post(route('internships.certificates.send-ready',$program))->assertRedirect()->assertSessionHas('success');
  $this->actingAs($intern)->get(route('internships.dashboard'))->assertOk()->assertSee('Download Sertifikat PDF');
  $this->get(route('internships.certificate.download'))->assertOk()->assertDownload();
  $this->assertNotNull($participant->fresh()->certificate_sent_at);
  $this->assertNotNull($participant->fresh()->certificate_downloaded_at);
  \Carbon\Carbon::setTestNow();
 }

 public function test_internship_certificate_template_contains_period_codes():void {
  $service=app(\App\Services\InternshipCertificateService::class);
  $path=tempnam(sys_get_temp_dir(),'intern-cert-test-').'.docx';
  try {
   $service->createSampleTemplate($path);
   $variables=$service->validateTemplate($path);
   $this->assertContains('tanggal_mulai',$variables);
   $this->assertContains('tanggal_selesai',$variables);
   $this->assertEqualsCanonicalizing(\App\Services\InternshipCertificateService::CODES,$variables);
  } finally {
   @unlink($path);
  }
 }
}