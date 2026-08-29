<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'evaluation_questions';

    protected $fillable = [
        'training_type',
        'bidang',
        'training_id',
        'category', 
        'sub_category', 
        'metode', // <--- Tambahkan ini
        'type', 
        'question_text', 
        'options'
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function scopeForTraining($query, Training $training, ?string $category = null)
    {
        $category ??= '';
        $method = strtolower(trim((string) $training->metode));

        return $query
            ->when($category !== '', fn ($q) => $q->where('category', $category))
            ->where(function ($q) use ($training) {
                $q->where('bidang', $training->bidang)
                    ->orWhere('bidang', 'Semua Bidang');
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
            });
    }
}
