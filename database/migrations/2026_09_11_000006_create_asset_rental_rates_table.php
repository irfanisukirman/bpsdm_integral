<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('asset_rental_rates',function(Blueprint $t){$t->id();$t->foreignId('asset_id')->constrained()->cascadeOnDelete();$t->time('start_time');$t->time('end_time');$t->decimal('hourly_rate',12,2);$t->unsignedSmallInteger('sort_order')->default(0);$t->timestamps();$t->index(['asset_id','start_time','end_time'],'asset_rates_time_idx');});
  Schema::table('asset_public_reservations',fn(Blueprint $t)=>$t->json('rate_breakdown')->nullable()->after('total_amount'));
  DB::table('assets')->where('is_rentable',true)->whereNotNull('hourly_rate')->orderBy('id')->get()->each(function($asset){DB::table('asset_rental_rates')->insert(['asset_id'=>$asset->id,'start_time'=>$asset->rental_open_time?:'07:00:00','end_time'=>$asset->rental_close_time?:'21:00:00','hourly_rate'=>$asset->hourly_rate,'sort_order'=>0,'created_at'=>now(),'updated_at'=>now()]);});
 }
 public function down():void {Schema::table('asset_public_reservations',fn(Blueprint $t)=>$t->dropColumn('rate_breakdown'));Schema::dropIfExists('asset_rental_rates');}
};