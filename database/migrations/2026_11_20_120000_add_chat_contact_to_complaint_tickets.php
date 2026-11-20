<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaint_tickets', function (Blueprint $table) {
            $table->string('chat_name', 100)->nullable();
            $table->string('chat_institution', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('complaint_tickets', fn (Blueprint $table) => $table->dropColumn(['chat_name', 'chat_institution']));
    }
};
