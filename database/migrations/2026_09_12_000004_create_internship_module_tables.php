<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up():void {
  if (DB::getDriverName() === 'mysql') {
   DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin','admin_bidang','participant','pengajar','admin_aset','mitra','penandatangan','intern') NOT NULL");
  }
  Schema::create('internship_programs',function(Blueprint $t){$t->id();$t->uuid('public_token')->unique();$t->string('title');$t->string('bidang')->nullable();$t->text('description')->nullable();$t->date('registration_opens_at')->nullable();$t->date('registration_closes_at')->nullable();$t->time('check_in_opens_at')->default('06:00');$t->time('late_after')->default('07:30');$t->time('check_out_opens_at')->default('16:00');$t->enum('status',['draft','open','closed','archived'])->default('draft');$t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();});
  Schema::create('internship_participants',function(Blueprint $t){$t->id();$t->foreignId('internship_program_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('name');$t->string('student_number',100);$t->string('major');$t->string('institution');$t->string('placement_unit');$t->date('start_date');$t->date('end_date');$t->string('email');$t->enum('status',['pending','approved','rejected','completed'])->default('pending');$t->enum('recommended_grade',['Sangat Baik','Baik','Cukup','Kurang'])->nullable();$t->enum('final_grade',['Sangat Baik','Baik','Cukup','Kurang'])->nullable();$t->text('review_note')->nullable();$t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamp('reviewed_at')->nullable();$t->timestamps();$t->unique(['internship_program_id','student_number'],'intern_program_student_unique');});
 }
 public function down():void {Schema::dropIfExists('internship_participants');Schema::dropIfExists('internship_programs');}
};