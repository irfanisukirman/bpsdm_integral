<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {public function up():void{Schema::table('guest_visits',function(Blueprint $table){$table->string('target_bidang')->nullable()->after('purpose')->index();});}public function down():void{Schema::table('guest_visits',function(Blueprint $table){$table->dropIndex(['target_bidang']);$table->dropColumn('target_bidang');});}};