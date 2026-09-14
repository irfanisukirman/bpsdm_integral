<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('internships:purge-photos', function () {
    $cutoff = now('Asia/Jakarta')->subDays(7);
    $deleted = 0;
    \App\Models\InternshipAttendance::query()
        ->where(function ($query) use ($cutoff) {
            $query->where(fn ($q) => $q->whereNotNull('check_in_photo_path')->where('check_in_at', '<=', $cutoff))
                ->orWhere(fn ($q) => $q->whereNotNull('check_out_photo_path')->where('check_out_at', '<=', $cutoff));
        })
        ->chunkById(100, function ($attendances) use ($cutoff, &$deleted) {
            foreach ($attendances as $attendance) {
                $updates = [];
                if ($attendance->check_in_photo_path && $attendance->check_in_at?->lte($cutoff)) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($attendance->check_in_photo_path);
                    $updates['check_in_photo_path'] = null;
                    $deleted++;
                }
                if ($attendance->check_out_photo_path && $attendance->check_out_at?->lte($cutoff)) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($attendance->check_out_photo_path);
                    $updates['check_out_photo_path'] = null;
                    $deleted++;
                }
                if ($updates) $attendance->update($updates);
            }
        });
    $this->info($deleted.' foto presensi lama berhasil dihapus.');
})->purpose('Menghapus foto selfie presensi magang yang berusia lebih dari tujuh hari');

\Illuminate\Support\Facades\Schedule::command('internships:purge-photos')
    ->dailyAt('01:15')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();
