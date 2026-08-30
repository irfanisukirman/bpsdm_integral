<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
 public function up():void{
  $bidang='Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan';
  $roots=DB::table('folders')->where('name','SKPK')->whereNull('parent_id')->where('bidang',$bidang)->get();
  foreach($roots as $root){
   DB::table('folders')->where('name','Sertifikasi')->where('parent_id',$root->id)->where('bidang',$bidang)->update(['parent_id'=>null,'updated_at'=>now()]);
   $hasChildren=DB::table('folders')->where('parent_id',$root->id)->exists();
   $hasFiles=DB::table('files')->where('folder_id',$root->id)->exists();
   if(!$hasChildren&&!$hasFiles)DB::table('folders')->where('id',$root->id)->delete();
  }
 }
 public function down():void{}
};
