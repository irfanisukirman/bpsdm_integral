<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'participants'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'status_kepegawaian')) {
                continue;
            }

            DB::table($table)
                ->whereIn('status_kepegawaian', ['Non-ASN', 'NON-ASN', 'Non ASN', 'NON ASN', 'NonASN', 'NONASN'])
                ->update(['status_kepegawaian' => 'PPPK-PW']);
        }
    }

    public function down(): void
    {
        foreach (['users', 'participants'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'status_kepegawaian')) {
                continue;
            }

            DB::table($table)
                ->where('status_kepegawaian', 'PPPK-PW')
                ->update(['status_kepegawaian' => 'Non-ASN']);
        }
    }
};