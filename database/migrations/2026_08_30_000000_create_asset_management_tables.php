<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('assets',function(Blueprint $t){$t->id();$t->string('name');$t->string('type')->default('ruangan');$t->text('facilities')->nullable();$t->string('location');$t->unsignedInteger('capacity')->nullable();$t->text('description')->nullable();$t->boolean('is_active')->default(true);$t->boolean('is_public')->default(true);$t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();});
  Schema::create('asset_images',function(Blueprint $t){$t->id();$t->foreignId('asset_id')->constrained()->cascadeOnDelete();$t->string('path');$t->unsignedInteger('sort_order')->default(0);$t->timestamps();});
  Schema::create('agendas',function(Blueprint $t){$t->id();$t->string('scope');$t->string('agenda_type');$t->string('name');$t->text('description')->nullable();$t->string('bidang')->nullable();$t->boolean('is_public')->default(false);$t->foreignId('created_by')->constrained('users')->cascadeOnDelete();$t->timestamps();});
  Schema::create('agenda_schedules',function(Blueprint $t){$t->id();$t->foreignId('agenda_id')->constrained()->cascadeOnDelete();$t->string('title')->nullable();$t->dateTime('starts_at');$t->dateTime('ends_at');$t->string('external_place')->nullable();$t->string('zoom_link')->nullable();$t->text('participants_info')->nullable();$t->text('notes')->nullable();$t->timestamps();});
  Schema::create('asset_bookings',function(Blueprint $t){$t->id();$t->foreignId('asset_id')->constrained()->cascadeOnDelete();$t->string('bookable_type');$t->unsignedBigInteger('bookable_id');$t->dateTime('starts_at');$t->dateTime('ends_at');$t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamps();$t->index(['asset_id','starts_at','ends_at']);$t->unique(['asset_id','bookable_type','bookable_id']);});
  Schema::table('schedules',function(Blueprint $t){$t->string('venue_type')->default('external');$t->string('external_place')->nullable();});
 }
 public function down():void {Schema::table('schedules',fn(Blueprint $t)=>$t->dropColumn(['venue_type','external_place']));Schema::dropIfExists('asset_bookings');Schema::dropIfExists('agenda_schedules');Schema::dropIfExists('agendas');Schema::dropIfExists('asset_images');Schema::dropIfExists('assets');}
};
