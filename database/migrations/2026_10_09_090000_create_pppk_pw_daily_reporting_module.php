<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up():void {
  Schema::create('institutions',function(Blueprint $t){$t->id();$t->string('name')->unique();$t->string('code',50)->nullable()->unique();$t->boolean('is_internal_bpsdm')->default(false)->index();$t->boolean('is_active')->default(true)->index();$t->unsignedInteger('sort_order')->default(0);$t->timestamps();});
  Schema::create('employment_statuses',function(Blueprint $t){$t->id();$t->string('name')->unique();$t->string('code',50)->unique();$t->text('description')->nullable();$t->boolean('is_active')->default(true)->index();$t->unsignedInteger('sort_order')->default(0);$t->timestamps();});
  Schema::table('users',function(Blueprint $t){$t->foreignId('institution_id')->nullable()->after('instansi')->constrained('institutions')->nullOnDelete();$t->foreignId('employment_status_id')->nullable()->after('status_kepegawaian')->constrained('employment_statuses')->nullOnDelete();});
  if(DB::getDriverName()==='mysql') DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin','admin_bidang','participant','pengajar','admin_aset','mitra','penandatangan','intern','pengelola_magang','resepsionis','pengelola_keuangan','manajemen_mutu','kasubag_pppk_pw') NOT NULL");
  Schema::create('daily_report_assignments',function(Blueprint $t){$t->id();$t->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();$t->string('employee_email')->unique();$t->foreignId('supervisor_user_id')->constrained('users')->restrictOnDelete();$t->foreignId('institution_id')->constrained()->restrictOnDelete();$t->date('start_date');$t->date('end_date')->nullable();$t->boolean('is_active')->default(true)->index();$t->foreignId('created_by')->constrained('users')->restrictOnDelete();$t->timestamps();});
  Schema::create('daily_reports',function(Blueprint $t){$t->id();$t->foreignId('assignment_id')->constrained('daily_report_assignments')->cascadeOnDelete();$t->date('report_date')->index();$t->string('status',30)->default('draft')->index();$t->text('employee_note')->nullable();$t->text('supervisor_note')->nullable();$t->timestamp('submitted_at')->nullable();$t->timestamp('reviewed_at')->nullable();$t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();$t->unique(['assignment_id','report_date']);});
  Schema::create('daily_report_items',function(Blueprint $t){$t->id();$t->foreignId('daily_report_id')->constrained()->cascadeOnDelete();$t->time('start_time');$t->time('end_time');$t->text('activity');$t->text('output');$t->text('obstacle')->nullable();$t->text('follow_up')->nullable();$t->unsignedInteger('sort_order')->default(0);$t->timestamps();});
  $now=now();
  $statuses=[['PNS','PNS',10],['CPNS','CPNS',20],['PPPK','PPPK',30],['PPPK-PW','PPPK-PW',40],['TNI','TNI',50],['POLRI','POLRI',60],['Pegawai BUMN/BUMD','BUMN-BUMD',70],['Swasta','SWASTA',80],['Lainnya','LAINNYA',90]];
  foreach($statuses as [$name,$code,$sort]) DB::table('employment_statuses')->insertOrIgnore(['name'=>$name,'code'=>$code,'sort_order'=>$sort,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now]);
  $institutionId=DB::table('institutions')->insertGetId(['name'=>'BPSDM Provinsi Jawa Barat','code'=>'BPSDM-JABAR','is_internal_bpsdm'=>1,'is_active'=>1,'sort_order'=>1,'created_at'=>$now,'updated_at'=>$now]);
  foreach(DB::table('employment_statuses')->get() as $status) DB::table('users')->where('status_kepegawaian',$status->name)->update(['employment_status_id'=>$status->id]);
  DB::table('users')->whereIn('instansi',['BPSDM Provinsi Jawa Barat','BPSDM Jabar','BPSDM Prov. Jabar'])->update(['institution_id'=>$institutionId,'instansi'=>'BPSDM Provinsi Jawa Barat']);
 }
 public function down():void {
  Schema::dropIfExists('daily_report_items');Schema::dropIfExists('daily_reports');Schema::dropIfExists('daily_report_assignments');
  Schema::table('users',function(Blueprint $t){$t->dropForeign(['institution_id']);$t->dropForeign(['employment_status_id']);$t->dropColumn(['institution_id','employment_status_id']);});
  Schema::dropIfExists('employment_statuses');Schema::dropIfExists('institutions');
 }
};
