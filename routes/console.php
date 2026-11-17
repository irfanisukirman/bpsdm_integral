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


Artisan::command('questions:sync-managerial-excel {--apply : Terapkan perubahan setelah memeriksa pratinjau}', function () {
    $result=app(\App\Services\ManagerialQuestionBankSyncService::class)->sync((bool)$this->option('apply'));
    $this->table(['Program','Soal aktif','Baru','Diperbarui','Tetap','Diarsipkan','Arsip berjawaban'],collect($result['stats'])->map(fn($row,$program)=>array_merge([$program],array_values($row)))->values()->all());
    if($result['backup'])$this->info('Cadangan privat: '.$result['backup']);
    $this->info($result['applied']?'Bank soal aktif diperbarui. Seluruh jawaban lama tetap tersimpan.':'Pratinjau saja. Gunakan --apply untuk menerapkan katalog soal Excel.');
});

Artisan::command('questions:sync-technical-excel {--apply : Terapkan katalog Teknis}', function () {
    $result = app(\App\Services\TechnicalQuestionBankSyncService::class)->sync((bool) $this->option('apply'));
    $this->table(['Program', 'Aktif', 'Baru', 'Diperbarui', 'Tetap', 'Diarsipkan', 'Arsip berjawaban'], collect($result['stats'])->map(fn ($row, $program) => array_merge([$program], array_values($row)))->values()->all());
    if ($result['backup']) $this->info('Cadangan privat: '.$result['backup']);
    $this->info($result['applied'] ? 'Bank soal Teknis diperbarui; jawaban lama tetap tersimpan.' : 'Pratinjau saja; gunakan --apply untuk menerapkan.');
});

Artisan::command('documents:repair-training-folders {training} {--apply}', function () {
    $training=\App\Models\Training::findOrFail($this->argument('training'));
    $result=app(\App\Services\TrainingFolderRepairService::class)->repair($training,(bool)$this->option('apply'));
    $this->table(['ID','Nama lama','Nama baru','Folder induk'],array_map(fn($row)=>[$row['id'],$row['old_name'],$row['name'],$row['parent_id']],$result['changes']));
    if($result['backup'])$this->info('Cadangan privat: '.$result['backup']);
    $this->info($result['apply']?'Struktur folder diperbaiki. Tidak ada berkas dihapus.':'Pratinjau; tambahkan --apply untuk menerapkan.');
});
