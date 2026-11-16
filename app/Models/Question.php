<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    public function getProgramEvaluasiAttribute($value): string
    {
        return \App\Support\EvaluationProgram::normalize($value);
    }

    public function setProgramEvaluasiAttribute($value): void
    {
        $this->attributes['program_evaluasi'] = \App\Support\EvaluationProgram::normalize($value);
    }

    protected $table = 'evaluation_questions';

    protected $fillable = [
        'training_type',
        'bidang',
        'program_evaluasi',
        'training_id',
        'category', 
        'sub_category', 
        'metode', // <--- Tambahkan ini
        'type', 
        'question_text', 
        'options'
    ];

    public const L34_SUB_CATEGORIES = [
        'Data Diri Alumni',
        'Data Diri Atasan',
        'Penempatan Tugas dan Transfer Learning',
        'Perubahan Perilaku',
        'Perubahan Sikap Perilaku',
        'Dampak Pelatihan',
        'Faktor Pendukung Aktualisasi',
        'Faktor Penghambat Aktualisasi',
        'Faktor Pendukung Aksi Perubahan',
        'Faktor Penghambat Aksi Perubahan',
        'Faktor Pendukung Proyek Perubahan',
        'Kesesuaian Rekomendasi Kebijakan Dengan Kebutuhan Instansi',
        'Kemanfaatan Rekomendasi',
    ];

    public static function l34SubCategoryOptions(): array
    {
        return self::L34_SUB_CATEGORIES;
    }
    protected $casts = [
        'options' => 'array',
    ];

    public function scopeForTraining($query, Training $training, ?string $category = null)
    {
        $category ??= '';
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
                if (($category === '' || str_starts_with($category, 'l34_')) && \App\Support\EvaluationProgram::isManagerial($program)) {
                    $q->orWhere('bidang', 'Bidang Pengembangan Kompetensi Manajerial');
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
                    $programQuery->whereIn(\Illuminate\Support\Facades\DB::raw('UPPER(program_evaluasi)'), \App\Support\EvaluationProgram::aliases($program))
                        ->orWhereRaw('LOWER(program_evaluasi) = ?', ['semua'])
                        ->orWhereNull('program_evaluasi');
                });
            });
    }
}
