<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->date('evaluation_l1_opens_on')->nullable();
            $table->date('evaluation_l34_opens_on')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn(['evaluation_l1_opens_on', 'evaluation_l34_opens_on']);
        });
    }
};
