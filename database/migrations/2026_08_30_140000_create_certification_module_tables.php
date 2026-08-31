<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
 public function up():void{
  Schema::create('certification_types',function(Blueprint $t){$t->id();$t->string('name')->unique();$t->timestamps();});
  Schema::create('certification_events',function(Blueprint $t){$t->id();$t->foreignId('certification_type_id')->constrained()->restrictOnDelete();$t->string('title');$t->date('start_date');$t->date('end_date');$t->string('location');$t->string('supervisor_name');$t->string('supervisor_phone',30)->nullable();$t->string('supervisor_institution')->nullable();$t->unsignedInteger('participant_quota')->default(0);$t->foreignId('folder_id')->nullable()->constrained('folders')->nullOnDelete();$t->foreignId('minutes_file_id')->nullable()->constrained('files')->nullOnDelete();$t->foreignId('created_by')->constrained('users')->restrictOnDelete();$t->timestamps();});
  Schema::create('certification_participants',function(Blueprint $t){$t->id();$t->foreignId('certification_event_id')->constrained()->cascadeOnDelete();$t->string('nip_nik',80);$t->string('name');$t->string('position')->nullable();$t->string('institution')->nullable();$t->string('province')->nullable();$t->string('city')->nullable();$t->string('phone',30)->nullable();$t->string('email')->nullable();$t->string('result',30)->default('belum_ditentukan');$t->text('notes')->nullable();$t->timestamps();$t->unique(['certification_event_id','nip_nik'],'cert_event_nip_unique');$t->index(['nip_nik','result']);});
  $now=now();DB::table('certification_types')->insert(collect(['PBJP Level 1','PPK Tipe-B','PPK Tipe-C','Pol PP','P2UPD','Keuangan Daerah'])->map(fn($name)=>['name'=>$name,'created_at'=>$now,'updated_at'=>$now])->all());
 }
 public function down():void{Schema::dropIfExists('certification_participants');Schema::dropIfExists('certification_events');Schema::dropIfExists('certification_types');}
};
