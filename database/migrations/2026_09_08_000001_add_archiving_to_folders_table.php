<?php

use App\Models\Training;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->unsignedSmallInteger('document_year')->nullable()->after('training_id');
            $table->boolean('is_archived')->default(false)->after('document_year');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            $table->foreignId('archived_by')->nullable()->after('archived_at')->constrained('users')->nullOnDelete();
            $table->index(['parent_id', 'document_year', 'is_archived'], 'folders_archive_index');
        });

        DB::table('folders')->whereNull('parent_id')->orderBy('id')->chunkById(200, function ($folders) {
            foreach ($folders as $folder) {
                $year = null;
                if ($folder->training_id) {
                    $date = Training::whereKey($folder->training_id)->value('tgl_mulai');
                    $year = $date ? (int) substr((string) $date, 0, 4) : null;
                }
                $year ??= (int) substr((string) $folder->created_at, 0, 4);
                $year = $year ?: (int) now()->format('Y');
                DB::table('folders')->where('id', $folder->id)->update(['document_year' => $year]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropIndex('folders_archive_index');
            $table->dropForeign(['archived_by']);
            $table->dropColumn(['document_year', 'is_archived', 'archived_at', 'archived_by']);
        });
    }
};