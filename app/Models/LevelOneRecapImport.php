<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LevelOneRecapImport extends Model
{
    public const LABELS = [
        'narasumber' => 'Narasumber',
        'penyelenggara' => 'Penyelenggara',
    ];

    protected $fillable = ['training_id', 'imported_by', 'kind', 'fingerprint', 'filename', 'source_training', 'indicators', 'entries'];

    protected $casts = ['indicators' => 'array', 'entries' => 'array'];
}
