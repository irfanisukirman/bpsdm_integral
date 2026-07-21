<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoringSummary extends Model
{
    // Nama tabel di database
    protected $table = 'monitoring_summaries';

    // Daftarkan kolom agar bisa disimpan oleh Controller
    protected $fillable = ['training_id', 'training_stage_id', 'category', 'conclusion'];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }
}
