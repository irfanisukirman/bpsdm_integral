<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up():void {
  Schema::table('internship_programs',function(Blueprint $table){
   $table->foreignId('certificate_signer_id')->nullable()->after('certificate_issued_at')->constrained('users')->nullOnDelete();
   $table->json('certificate_reviewer_ids')->nullable()->after('certificate_signer_id');
  });
  Schema::table('internship_participants',function(Blueprint $table){
   $table->string('certificate_generated_file_path')->nullable()->after('certificate_number');
   $table->timestamp('certificate_sent_at')->nullable()->after('certificate_generated_at');
   $table->foreignId('certificate_sent_by')->nullable()->after('certificate_sent_at')->constrained('users')->nullOnDelete();
  });
  Schema::table('electronic_signature_documents',function(Blueprint $table){
   $table->foreignId('internship_participant_id')->nullable()->after('participant_certificate_id')->constrained('internship_participants')->nullOnDelete();
  });
  DB::table('internship_participants')->whereNotNull('certificate_file_path')->update([
   'certificate_generated_file_path'=>DB::raw('certificate_file_path'),
   'certificate_file_path'=>null,
  ]);
 }
 public function down():void {
  Schema::table('electronic_signature_documents',function(Blueprint $table){$table->dropForeign(['internship_participant_id']);$table->dropColumn('internship_participant_id');});
  Schema::table('internship_participants',function(Blueprint $table){$table->dropForeign(['certificate_sent_by']);$table->dropColumn(['certificate_generated_file_path','certificate_sent_at','certificate_sent_by']);});
  Schema::table('internship_programs',function(Blueprint $table){$table->dropForeign(['certificate_signer_id']);$table->dropColumn(['certificate_signer_id','certificate_reviewer_ids']);});
 }
};
