<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  if(DB::getDriverName()==='mysql') DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin','admin_bidang','participant','pengajar','admin_aset','mitra','penandatangan','intern','pengelola_magang','resepsionis','pengelola_keuangan') NOT NULL");
  Schema::create('finance_budget_lines',function(Blueprint $t){$t->id();$t->unsignedSmallInteger('year')->index();$t->string('bidang')->index();$t->foreignId('parent_id')->nullable()->constrained('finance_budget_lines')->cascadeOnDelete();$t->string('level',30);$t->string('account_code',100)->nullable();$t->string('description');$t->decimal('allocated_amount',18,2)->default(0);$t->foreignId('created_by')->constrained('users')->restrictOnDelete();$t->timestamps();$t->index(['year','bidang','level']);});
  Schema::create('finance_transactions',function(Blueprint $t){$t->id();$t->foreignId('finance_budget_line_id')->constrained()->restrictOnDelete();$t->foreignId('schedule_id')->nullable()->unique()->constrained()->nullOnDelete();$t->string('bidang')->index();$t->unsignedSmallInteger('year')->index();$t->string('type',30)->default('other');$t->string('status',30)->default('draft')->index();$t->date('transaction_date');$t->string('payee');$t->text('description');$t->decimal('gross_amount',18,2);$t->decimal('tax_amount',18,2)->default(0);$t->decimal('net_amount',18,2);$t->string('reference_number')->nullable();$t->string('evidence_path')->nullable();$t->string('evidence_name')->nullable();$t->text('note')->nullable();$t->foreignId('created_by')->constrained('users')->restrictOnDelete();$t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamp('reviewed_at')->nullable();$t->timestamp('paid_at')->nullable();$t->timestamps();$t->index(['bidang','year','status']);});
 }
 public function down():void {
  Schema::dropIfExists('finance_transactions');Schema::dropIfExists('finance_budget_lines');
  DB::table('users')->where('role','pengelola_keuangan')->update(['role'=>'admin_bidang']);
  if(DB::getDriverName()==='mysql') DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin','admin_bidang','participant','pengajar','admin_aset','mitra','penandatangan','intern','pengelola_magang','resepsionis') NOT NULL");
 }
};
