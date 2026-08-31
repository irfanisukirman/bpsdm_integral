<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('evaluation_questions')->where(function ($query) {
            $query->whereNull('program_evaluasi')->orWhere('program_evaluasi', '')
                ->orWhereRaw('LOWER(program_evaluasi) = ?', ['semua']);
        })->update(['program_evaluasi' => 'PKTI/PKTU']);
    }
    public function down(): void {}
};
