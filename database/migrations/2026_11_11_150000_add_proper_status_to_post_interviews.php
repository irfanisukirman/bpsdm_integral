<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_evaluation_interviews', function (Blueprint $t) {
            $t->string('medium_evidence_status')->nullable();
            $t->string('long_evidence_status')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('post_evaluation_interviews', fn (Blueprint $t) => $t->dropColumn(['medium_evidence_status', 'long_evidence_status']));
    }
};
