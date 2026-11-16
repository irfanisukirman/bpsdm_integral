<?php
namespace Tests\Feature;

use App\Http\Controllers\UserController;
use App\Models\{FinanceBudgetLine, FinanceTransaction, PengajarScheduleDocument, Schedule, Training, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Storage};
use Tests\TestCase;

class FinanceTeacherOverviewTest extends TestCase
{
    use RefreshDatabase;

    private function training(User $user, string $name, string $bidang): Training
    {
        return Training::create(['nama_pelatihan'=>$name,'bidang'=>$bidang,'model'=>'standar','metode'=>'klasikal','lokasi'=>'BPSDM','angkatan'=>'I','jumlah_peserta'=>10,'jp'=>10,'tgl_mulai'=>'2026-01-01','tgl_selesai'=>'2026-12-31','created_by'=>$user->id]);
    }

    public function test_all_schedules_are_grouped_by_training_including_future_and_processed_honor(): void
    {
        Storage::fake('public');
        DB::connection()->getPdo()->sqliteCreateFunction('FIELD',fn($value,...$values)=>array_search($value,$values,true)+1);
        $bidang=UserController::$listBidang[0];
        $admin=User::factory()->create(['role'=>'pengelola_keuangan','bidang'=>$bidang]);
        $training=$this->training($admin,'Pelatihan Alpha',$bidang);
        $teacher=User::factory()->create(['role'=>'pengajar','name'=>'Pengajar Semua Jadwal']);
        $make=fn($t,$date,$activity)=>Schedule::create(['training_id'=>$t->id,'pengajar_id'=>$teacher->id,'date'=>$date,'start_time'=>'08:00','end_time'=>'10:00','activity'=>$activity,'schedule_type'=>'learning','pic'=>'Panitia','jp'=>2]);
        $old=$make($training,'2026-01-02','Materi sudah dibayar');
        for($i=0;$i<101;$i++)$make($training,'2026-02-02','Materi '.$i);
        $make($training,'2026-12-31','Materi mendatang');
        $make($training,'2025-01-01','Tahun lain tersembunyi');
        $other=$this->training($admin,'Pelatihan Bidang Lain',UserController::$listBidang[1]);
        $foreign=$make($other,'2026-02-02','Materi bidang lain');
        $line=FinanceBudgetLine::create(['year'=>2026,'bidang'=>$bidang,'level'=>'activity','description'=>'Anggaran','created_by'=>$admin->id]);
        FinanceTransaction::create(['finance_budget_line_id'=>$line->id,'schedule_id'=>$old->id,'year'=>2026,'bidang'=>$bidang,'transaction_date'=>'2026-01-02','type'=>'teacher_payment','status'=>'paid','payee'=>$teacher->name,'description'=>'Honor','gross_amount'=>100,'net_amount'=>100,'created_by'=>$admin->id]);
        $response=$this->actingAs($admin)->get(route('finance.index',['year'=>2026,'bidang'=>UserController::$listBidang[1]]));
        $response->assertOk()->assertSeeInOrder(['Pelatihan Alpha','Pengajar Semua Jadwal','Materi sudah dibayar'])->assertSee('Materi 100')->assertSee('Materi mendatang')->assertSee('Sudah Dibayar')->assertSee('Belum lengkap')->assertDontSee('Tahun lain tersembunyi')->assertSee('Materi bidang lain')->assertSee('Pelatihan Bidang Lain')->assertSee('Honor diproses pengelola keuangan bidang terkait.');
        $this->assertCount(104,$response->viewData('teacherSchedules'));
        $this->assertCount(2,$response->viewData('teacherTrainings'));
        $this->post(route('finance.teacher.store',$foreign))->assertForbidden();
        $superadmin=User::factory()->create(['role'=>'superadmin']);
        $this->actingAs($superadmin)->get(route('finance.index',['year'=>2026,'bidang'=>$bidang]))->assertOk()->assertSee('Pelatihan Alpha')->assertSee('Pelatihan Bidang Lain')->assertSee('Pilih bidang pelatihan pada filter anggaran');
    }

    public function test_empty_document_record_does_not_count_as_complete_or_allow_payment(): void
    {
        Storage::fake('public');
        $bidang=UserController::$listBidang[0];
        $admin=User::factory()->create(['role'=>'pengelola_keuangan','bidang'=>$bidang]);
        $training=$this->training($admin,'Pelatihan',$bidang);
        $teacher=User::factory()->create(['role'=>'pengajar']);
        $schedule=Schedule::create(['training_id'=>$training->id,'pengajar_id'=>$teacher->id,'date'=>'2026-01-02','start_time'=>'08:00','end_time'=>'10:00','activity'=>'Materi','schedule_type'=>'learning','pic'=>'Panitia','jp'=>2]);
        PengajarScheduleDocument::create(['schedule_id'=>$schedule->id,'user_id'=>$teacher->id,'bahan_ajar_path'=>'missing.pdf']);
        $this->actingAs($admin)->post(route('finance.teacher.store',$schedule))->assertStatus(422)->assertSee('Bahan ajar')->assertSee('RBPMP/RP')->assertSee('Bukti mengajar');
        $this->assertDatabaseCount('finance_transactions',0);
    }
}
