<?php

use App\Models\Bidang;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bidang_units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('original_name')->unique();
            $table->timestamps();
        });
        $names = collect(Bidang::DEFAULTS);
        foreach (Bidang::referenceColumns() as [$table, $column]) {
            if ($column !== 'training_type') {
                $names = $names->merge(DB::table($table)->whereNotNull($column)->distinct()->pluck($column));
            }
        }
        foreach ($names->filter()->unique() as $name) {
            if (! in_array($name, ['Semua Bidang', 'Pengelola Aset'], true)) {
                DB::table('bidang_units')->insert(['name' => $name, 'original_name' => $name, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bidang_units');
    }
};
