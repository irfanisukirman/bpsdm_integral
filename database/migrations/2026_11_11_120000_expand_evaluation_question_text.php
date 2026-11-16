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
            $table->longText('question_text')->change();
        });
    }

    public function down(): void
    {
        foreach (DB::table('evaluation_questions')->select('question_text')->cursor() as $question) {
            if (mb_strlen($question->question_text) > 255) {
                throw new RuntimeException('Kolom question_text tidak dapat diperkecil karena masih berisi pertanyaan lebih dari 255 karakter.');
            }
        }

        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->string('question_text')->change();
        });
    }
};
