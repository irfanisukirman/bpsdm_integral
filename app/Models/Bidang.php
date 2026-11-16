<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Bidang extends Model
{
    protected $table = 'bidang_units';

    protected $fillable = ['name', 'original_name'];

    public const DEFAULTS = [
        'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan',
        'Bidang Pengembangan Kompetensi Teknis Inti',
        'Bidang Pengembangan Kompetensi Teknis Umum',
        'Bidang Pengembangan Kompetensi Manajerial',
        'Sekretariat',
    ];

    public static function names(): array
    {
        return Schema::hasTable('bidang_units') ? static::orderBy('id')->pluck('name')->all() : self::DEFAULTS;
    }

    public static function resolve(string $original): string
    {
        return Schema::hasTable('bidang_units') ? (static::where('original_name', $original)->value('name') ?? $original) : $original;
    }

    public static function trainingNames(): array
    {
        return Schema::hasTable('bidang_units') ? static::whereIn('original_name', array_slice(self::DEFAULTS, 1, 3))->orderBy('id')->pluck('name')->all() : array_slice(self::DEFAULTS, 1, 3);
    }

    /** Columns storing a unit name rather than a foreign key. */
    public static function referenceColumns(): array
    {
        $references = [];
        foreach (Schema::getTableListing(DB::getDriverName() === 'sqlite' ? 'main' : DB::getDatabaseName(), false) as $table) {
            foreach (array_intersect(Schema::getColumnListing($table), ['bidang', 'target_bidang']) as $column) {
                $references[] = [$table, $column];
            }
            if ($table === 'evaluation_questions' && Schema::hasColumn($table, 'training_type')) {
                $references[] = [$table, 'training_type'];
            }
        }

        return $references;
    }

    public function usage(array $references): int
    {
        $count = array_sum(array_map(fn ($reference) => DB::table($reference[0])->where($reference[1], $this->name)->count(), $references));
        // These modules belong to the certification unit without storing its name on each row.
        if ($this->original_name === self::DEFAULTS[0]) {
            foreach (['cooperation_records', 'certification_events'] as $table) {
                if (Schema::hasTable($table)) {
                    $count += DB::table($table)->count();
                }
            }
        }
        return $count;
    }
}
