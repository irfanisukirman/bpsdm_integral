<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {Schema::create('internship_attendances',function(Blueprint $t){$t->id();$t->foreignId('internship_participant_id')->constrained()->cascadeOnDelete();$t->date('attendance_date');$t->enum('status',['present','permission','sick','absent']);$t->timestamp('check_in_at')->nullable();$t->timestamp('check_out_at')->nullable();$t->unsignedInteger('late_minutes')->default(0);$t->string('check_in_photo_path')->nullable();$t->string('check_out_photo_path')->nullable();$t->text('note')->nullable();$t->string('evidence_path')->nullable();$t->enum('review_status',['not_required','pending','approved','rejected'])->default('not_required');$t->text('review_note')->nullable();$t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamp('reviewed_at')->nullable();$t->timestamps();$t->unique(['internship_participant_id','attendance_date'],'intern_attendance_day_unique');});}
 public function down():void {Schema::dropIfExists('internship_attendances');}
};