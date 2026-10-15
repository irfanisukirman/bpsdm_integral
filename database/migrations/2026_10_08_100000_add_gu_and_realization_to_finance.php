<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::table('finance_transactions',function(Blueprint $t){$t->decimal('realized_amount',18,2)->nullable()->after('net_amount');$t->date('realized_at')->nullable()->after('realized_amount');});
  Schema::create('finance_gu_batches',function(Blueprint $t){$t->id();$t->unsignedSmallInteger('year')->index();$t->string('bidang')->index();$t->date('gu_date');$t->string('description');$t->decimal('amount',18,2);$t->text('note')->nullable();$t->foreignId('created_by')->constrained('users')->restrictOnDelete();$t->timestamps();});
  Schema::create('finance_gu_allocations',function(Blueprint $t){$t->id();$t->foreignId('finance_gu_batch_id')->constrained()->cascadeOnDelete();$t->foreignId('finance_budget_line_id')->constrained()->restrictOnDelete();$t->decimal('amount',18,2);$t->timestamps();$t->unique(['finance_gu_batch_id','finance_budget_line_id'],'finance_gu_line_unique');});
 }
 public function down():void {Schema::dropIfExists('finance_gu_allocations');Schema::dropIfExists('finance_gu_batches');Schema::table('finance_transactions',function(Blueprint $t){$t->dropColumn(['realized_amount','realized_at']);});}
};