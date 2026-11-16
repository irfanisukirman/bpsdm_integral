<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_questions', function (Blueprint $t) {
            $t->boolean('is_active')->default(true)->index();
            $t->string('source_column', 8)->nullable();
            $t->unsignedSmallInteger('source_order')->nullable();
            $t->unsignedSmallInteger('source_occurrence')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_questions', function (Blueprint $t) {
            $t->dropIndex(['is_active']);
            $t->dropColumn(['is_active', 'source_column', 'source_order', 'source_occurrence']);
        });
    }
};
