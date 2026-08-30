<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
 public function up():void{
  Schema::table('certification_events',function(Blueprint $t){$t->string('public_token',64)->nullable()->unique()->after('participant_quota');});
  Schema::table('certification_participants',function(Blueprint $t){$t->string('biodata_token',64)->nullable()->unique()->after('result');$t->string('birth_place_date')->nullable();$t->string('rank_grade')->nullable();$t->string('religion',50)->nullable();$t->string('gender',30)->nullable();$t->string('education')->nullable();$t->text('office_address')->nullable();$t->text('trainings')->nullable();$t->string('signature_path')->nullable();$t->foreignId('biodata_file_id')->nullable()->constrained('files')->nullOnDelete();$t->timestamp('biodata_submitted_at')->nullable();});
  DB::table('certification_events')->orderBy('id')->get()->each(fn($row)=>DB::table('certification_events')->where('id',$row->id)->update(['public_token'=>Str::random(48)]));
  DB::table('certification_participants')->orderBy('id')->get()->each(fn($row)=>DB::table('certification_participants')->where('id',$row->id)->update(['biodata_token'=>Str::random(48)]));
 }
 public function down():void{Schema::table('certification_participants',function(Blueprint $t){$t->dropForeign(['biodata_file_id']);$t->dropColumn(['biodata_token','birth_place_date','rank_grade','religion','gender','education','office_address','trainings','signature_path','biodata_file_id','biodata_submitted_at']);});Schema::table('certification_events',fn(Blueprint $t)=>$t->dropColumn('public_token'));}
};
