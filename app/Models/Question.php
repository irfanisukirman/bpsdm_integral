<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'evaluation_questions';

    protected $fillable = [
        'training_type', 
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
}