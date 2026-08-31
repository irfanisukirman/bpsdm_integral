<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->string('bidang')->nullable()->after('training_type')->index();
        });

        DB::table('evaluation_questions')->where('training_type', 'PKTI/PKTU')
            ->update(['bidang' => 'Bidang Pengembangan Kompetensi Teknis Umum']);
        DB::table('evaluation_questions')->whereIn('training_type', ['CPNS', 'PKP', 'PKA', 'PKN'])
            ->update(['bidang' => 'Bidang Pengembangan Kompetensi Manajerial']);
        DB::table('evaluation_questions')->where(function ($query) {
            $query->whereNull('bidang')->orWhere('bidang', '');
        })->update(['bidang' => 'Semua Bidang']);
    }

    public function down(): void
    {
        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->dropIndex(['bidang']);
            $table->dropColumn('bidang');
        });
    }
};
