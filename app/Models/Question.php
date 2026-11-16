<?php

namespace App\Models;

use App\Support\EvaluationProgram;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Question extends Model
{
    public function getProgramEvaluasiAttribute($value): string
    {
        return EvaluationProgram::normalize($value);
    }

    public function setProgramEvaluasiAttribute($value): void
    {
        $this->attributes['program_evaluasi'] = EvaluationProgram::normalize($value);
    }

    protected $table = 'evaluation_questions';

    protected $fillable = [
        'is_active', 'source_column', 'source_order', 'source_occurrence',
        'training_type',
        'bidang',
        'program_evaluasi',
        'training_id',
        'category',
        'sub_category',
        'metode', // <--- Tambahkan ini
        'type',
        'question_text',
        'options',
    ];

    public const L34_SUB_CATEGORIES = [
        'Data Diri Alumni',
        'Data Diri Atasan',
        'Data Diri Rekan',
        'Penempatan Tugas dan Transfer Learning',
        'Perubahan Perilaku',
        'Perubahan Sikap Perilaku',
        'Dampak Pelatihan',
        'Faktor Pendukung Aktualisasi',
        'Faktor Penghambat Aktualisasi',
        'Faktor Pendukung Aksi Perubahan',
        'Faktor Penghambat Aksi Perubahan',
        'Faktor Pendukung Proyek Perubahan',
        'Faktor Penghambat Proyek Perubahan',
        'Kesesuaian Rekomendasi Kebijakan Dengan Kebutuhan Instansi',
        'Kemanfaatan Rekomendasi',
    ];

    public static function l34SubCategoryOptions(): array
    {
        return self::L34_SUB_CATEGORIES;
    }

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'source_order' => 'integer',
        'source_occurrence' => 'integer',
    ];

    public function scopeForTraining($query, Training $training, ?string $category = null, bool $includeHistorical = false)
    {
        $category ??= '';
        $query->where(function ($active) use ($includeHistorical, $training) {
            $active->where('is_active', true);
            if ($includeHistorical) {
                $active->orWhereIn('id', EvaluationResultL34::where('training_id', $training->id)->whereNotNull('question_id')->select('question_id'));
            }
        });
        $query->orderByRaw('COALESCE(source_order, id)');

        $query->where(function ($q) use ($training) {
            $q->where('category', 'not like', 'l34_%')
                ->orWhereIn('category', array_map(fn ($role) => 'l34_'.$role, $training->evaluationRoles()));
        });
        $method = strtolower(trim((string) $training->metode));

        return $query
            ->when($category !== '', fn ($q) => $q->where('category', $category))
            ->where(function ($q) use ($training, $category) {
                $q->where('bidang', $training->bidang)
                    ->orWhere('bidang', 'Semua Bidang');

                $program = strtoupper(trim((string) ($training->program_evaluasi ?: 'Teknis')));
                if (($category === '' || str_starts_with($category, 'l34_')) && EvaluationProgram::isManagerial($program)) {
                    $q->orWhere('bidang', Bidang::resolve('Bidang Pengembangan Kompetensi Manajerial'));
                }
            })
            ->where(function ($q) use ($category, $method) {
                if (in_array($category, ['l1_penyelenggara', 'l1_narasumber'], true)) {
                    $q->whereRaw('LOWER(metode) = ?', [$method])
                        ->orWhereRaw('LOWER(metode) = ?', ['semua'])
                        ->orWhereNull('metode');

                    return;
                }

                $q->whereRaw('LOWER(metode) = ?', ['semua'])
                    ->orWhereNull('metode');
            })
            ->where(function ($q) use ($training) {
                $program = strtoupper(trim((string) ($training->program_evaluasi ?: 'Teknis')));
                $q->where(function ($programQuery) use ($program) {
                    $programQuery->whereIn(DB::raw('UPPER(program_evaluasi)'), EvaluationProgram::aliases($program))
                        ->orWhereRaw('LOWER(program_evaluasi) = ?', ['semua'])
                        ->orWhereNull('program_evaluasi');
                });
            });
    }
}
