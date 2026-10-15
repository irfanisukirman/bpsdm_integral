<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();
        $names = DB::table('users')->whereNotNull('instansi')->where('instansi', '<>', '')
            ->distinct()->orderBy('instansi')->pluck('instansi');

        foreach ($names as $name) {
            $id = DB::table('institutions')->where('name', $name)->value('id');
            if (! $id) {
                $id = DB::table('institutions')->insertGetId([
                    'name' => $name,
                    'is_internal_bpsdm' => false,
                    'is_active' => true,
                    'sort_order' => 100,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            DB::table('users')->where('instansi', $name)->whereNull('institution_id')->update(['institution_id' => $id]);
        }
    }

    public function down(): void {}
};
