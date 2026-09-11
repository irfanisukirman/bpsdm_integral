<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {Schema::create('asset_loan_requests',function(Blueprint $t){
  $t->id();$t->morphs('requestable');$t->json('asset_ids');$t->string('letter_path');
  $t->text('purpose')->nullable();$t->string('contact_person')->nullable();$t->unsignedInteger('attendee_count')->nullable();
  $t->string('status')->default('pending');$t->text('review_note')->nullable();
  $t->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
  $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamp('reviewed_at')->nullable();
  $t->timestamps();$t->unique(['requestable_type','requestable_id']);
 });}
 public function down():void {Schema::dropIfExists('asset_loan_requests');}
};
