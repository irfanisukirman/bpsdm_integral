<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        $column = DB::selectOne("SHOW COLUMNS FROM users WHERE Field = 'role'");
        $type = $column->Type;
        if (str_starts_with(strtolower($type), 'enum(') && ! str_contains($type, "'evaluator'")) {
            DB::statement('ALTER TABLE users MODIFY role '.substr($type, 0, -1).",'evaluator') NOT NULL");
        }
    }

    public function down(): void
    {
        // Preserve evaluator accounts and existing enum values on rollback.
    }
};
