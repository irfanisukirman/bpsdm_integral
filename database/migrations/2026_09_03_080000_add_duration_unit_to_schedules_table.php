<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {Schema::table('schedules',fn(Blueprint $table)=>$table->string('duration_unit',2)->default('JP')->after('jp'));}
 public function down():void {Schema::table('schedules',fn(Blueprint $table)=>$table->dropColumn('duration_unit'));}
};
