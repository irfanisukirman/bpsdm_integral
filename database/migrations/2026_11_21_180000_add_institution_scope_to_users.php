<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_municipalities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        foreach ([
            'Kabupaten Bandung', 'Kabupaten Bandung Barat', 'Kabupaten Bekasi',
            'Kabupaten Bogor', 'Kabupaten Ciamis', 'Kabupaten Cianjur',
            'Kabupaten Cirebon', 'Kabupaten Garut', 'Kabupaten Indramayu',
            'Kabupaten Karawang', 'Kabupaten Kuningan', 'Kabupaten Majalengka',
            'Kabupaten Pangandaran', 'Kabupaten Purwakarta', 'Kabupaten Subang',
            'Kabupaten Sukabumi', 'Kabupaten Sumedang', 'Kabupaten Tasikmalaya',
            'Kota Bandung', 'Kota Banjar', 'Kota Bekasi', 'Kota Bogor',
            'Kota Cimahi', 'Kota Cirebon', 'Kota Depok', 'Kota Sukabumi', 'Kota Tasikmalaya',
        ] as $name) {
            DB::table('institution_municipalities')->insert(['name' => $name]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('institution_scope', 20)->nullable();
            $table->foreignId('institution_municipality_id')->nullable()->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('institution_municipality_id');
            $table->dropColumn('institution_scope');
        });

        Schema::dropIfExists('institution_municipalities');
    }
};
