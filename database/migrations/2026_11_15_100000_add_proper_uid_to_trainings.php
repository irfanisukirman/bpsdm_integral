<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainings', fn (Blueprint $table) => $table->uuid('proper_uid')->nullable()->unique());
        DB::table('trainings')->select('id')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('trainings')->where('id', $row->id)->update(['proper_uid' => (string) Str::uuid()]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropUnique(['proper_uid']);
            $table->dropColumn('proper_uid');
        });
    }
};
