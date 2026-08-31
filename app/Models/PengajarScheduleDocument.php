<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PengajarScheduleDocument extends Model
{
    protected $fillable = ['schedule_id', 'user_id', 'bahan_ajar_path', 'rbpmp_rp_path', 'bukti_mengajar_path'];
    public function schedule() { return $this->belongsTo(Schedule::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function isComplete(): bool
    {
        return collect([$this->bahan_ajar_path, $this->rbpmp_rp_path, $this->bukti_mengajar_path])
            ->every(fn ($path) => filled($path) && Storage::disk('public')->exists($path));
    }
}
