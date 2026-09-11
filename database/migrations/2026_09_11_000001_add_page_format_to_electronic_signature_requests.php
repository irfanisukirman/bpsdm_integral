<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{Schema::table('electronic_signature_requests',function(Blueprint $table){$table->string('page_format',30)->default('f4_portrait')->after('source_type');});}
 public function down():void{Schema::table('electronic_signature_requests',fn(Blueprint $table)=>$table->dropColumn('page_format'));}
};
