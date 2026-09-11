<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
return new class extends Migration {
 public function up():void{Schema::table('electronic_signature_documents',fn(Blueprint $t)=>$t->uuid('verification_token')->nullable()->unique()->after('external_user_id'));DB::table('electronic_signature_documents')->orderBy('id')->get(['id'])->each(fn($row)=>DB::table('electronic_signature_documents')->where('id',$row->id)->update(['verification_token'=>(string)Str::uuid()]));}
 public function down():void{Schema::table('electronic_signature_documents',fn(Blueprint $t)=>$t->dropColumn('verification_token'));}
};
