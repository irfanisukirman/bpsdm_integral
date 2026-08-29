<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('evaluation_questions')
            ->where('category', 'like', 'l34_%')
            ->where(function ($query) {
                $query->whereNull('sub_category')->orWhere('sub_category', '');
            })
            ->update(['sub_category' => 'Data Diri Alumni']);
    }

    public function down(): void
    {
        // Klasifikasi tidak dikosongkan kembali agar perubahan manual setelah migrasi tidak hilang.
    }
};
