<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('birth_place')->nullable()->after('gender');
            $table->date('birth_date')->nullable()->after('birth_place');
            $table->string('golongan', 100)->nullable()->after('jabatan');
            $table->text('address')->nullable()->after('kelurahan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['birth_place', 'birth_date', 'golongan', 'address']);
        });
    }
};
