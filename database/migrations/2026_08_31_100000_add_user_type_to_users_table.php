<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_type', 30)->nullable()->after('role');
            $table->string('user_type_status', 20)->default('approved')->after('user_type');
        });

        DB::table('users')->where('role', 'pengajar')->update(['user_type' => 'narasumber']);
        DB::table('users')->where('role', 'participant')->update(['user_type' => 'peserta']);
        DB::table('users')->where('role', 'mitra')->update(['user_type' => 'mitra']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_type', 'user_type_status']);
        });
    }
};