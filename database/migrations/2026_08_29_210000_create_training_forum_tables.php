<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        DB::table('folders')
            ->join('users', 'users.id', '=', 'folders.user_id')
            ->whereNull('folders.parent_id')
            ->whereNotNull('folders.training_id')
            ->whereIn('users.role', ['admin_bidang', 'superadmin'])
            ->select('folders.training_id', 'folders.user_id')
            ->orderBy('folders.id')
            ->get()
            ->unique('training_id')
            ->each(function ($folder) {
                DB::table('trainings')
                    ->where('id', $folder->training_id)
                    ->whereNull('created_by')
                    ->update(['created_by' => $folder->user_id]);
            });
        Schema::create('training_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->timestamps();
            $table->index(['training_id', 'id']);
        });
        Schema::create('training_forum_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('last_read_message_id')->nullable();
            $table->timestamps();
            $table->unique(['training_id', 'user_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('training_forum_reads');
        Schema::dropIfExists('training_messages');
        Schema::table('trainings', fn (Blueprint $table) => $table->dropConstrainedForeignId('created_by'));
    }
};
