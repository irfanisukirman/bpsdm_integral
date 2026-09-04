<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('participant_certificates', function (Blueprint $table) {
            $table->timestamp('downloaded_at')->nullable()->after('uploaded_at');
        });
    }
    public function down(): void
    {
        Schema::table('participant_certificates', fn (Blueprint $table) => $table->dropColumn('downloaded_at'));
    }
};
