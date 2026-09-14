<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'kabupaten_kota') && ! Schema::hasColumn('users', 'kota')) {
            Schema::table('users', fn ($table) => $table->renameColumn('kabupaten_kota', 'kota'));
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'kota') && ! Schema::hasColumn('users', 'kabupaten_kota')) {
            Schema::table('users', fn ($table) => $table->renameColumn('kota', 'kabupaten_kota'));
        }
    }
};
