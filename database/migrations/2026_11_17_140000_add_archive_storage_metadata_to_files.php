<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('files',function(Blueprint $t){$t->string('storage_disk',20)->default('public');$t->string('content_hash',64)->nullable()->index();});
  Schema::table('file_versions',fn(Blueprint $t)=>$t->string('storage_disk',20)->default('public'));
 }
 public function down(): void {Schema::table('files',fn(Blueprint $t)=>$t->dropColumn(['storage_disk','content_hash']));Schema::table('file_versions',fn(Blueprint $t)=>$t->dropColumn('storage_disk'));}
};
