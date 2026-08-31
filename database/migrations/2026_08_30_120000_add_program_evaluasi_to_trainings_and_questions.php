<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->string('program_evaluasi', 30)->default('PKTI/PKTU')->after('bidang')->index();
        });

        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->string('program_evaluasi', 30)->default('semua')->after('bidang')->index();
        });

        DB::table('trainings')
            ->where('bidang', 'Bidang Pengembangan Kompetensi Manajerial')
            ->orderBy('id')
            ->chunkById(100, function ($trainings) {
                foreach ($trainings as $training) {
                    $name = strtoupper((string) $training->nama_pelatihan);
                    $program = match (true) {
                        str_contains($name, 'PKN'), str_contains($name, 'KEPEMIMPINAN NASIONAL') => 'PKN',
                        str_contains($name, 'PKA'), str_contains($name, 'ADMINISTRATOR') => 'PKA',
                        str_contains($name, 'PKP'), str_contains($name, 'PENGAWAS') => 'PKP',
                        str_contains($name, 'CPNS'), str_contains($name, 'LATSAR') => 'CPNS',
                        default => 'PKTI/PKTU',
                    };
                    DB::table('trainings')->where('id', $training->id)->update(['program_evaluasi' => $program]);
                }
            });

        DB::table('evaluation_questions')
            ->whereIn('training_type', ['CPNS', 'PKP', 'PKA', 'PKN', 'PKTI/PKTU'])
            ->update(['program_evaluasi' => DB::raw('training_type')]);
    }

    public function down(): void
    {
        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->dropIndex(['program_evaluasi']);
            $table->dropColumn('program_evaluasi');
        });
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropIndex(['program_evaluasi']);
            $table->dropColumn('program_evaluasi');
        });
    }
};
