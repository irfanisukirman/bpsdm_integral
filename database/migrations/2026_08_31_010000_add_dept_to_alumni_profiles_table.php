<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('alumni_profiles', 'dept_during_training')) {
                $table->string('dept_during_training')->nullable()->after('unit_current');
            }
            if (!Schema::hasColumn('alumni_profiles', 'dept_current')) {
                $table->string('dept_current')->nullable()->after('dept_during_training');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alumni_profiles', function (Blueprint $table) {
            $table->dropColumn(['dept_during_training', 'dept_current']);
        });
    }
};
