<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_public_reservations', function (Blueprint $table) {
            $table->json('tracking_events')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('asset_public_reservations', fn (Blueprint $table) => $table->dropColumn('tracking_events'));
    }
};
