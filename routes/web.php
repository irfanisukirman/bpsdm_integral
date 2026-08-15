<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EvaluationLevel1Controller;
use App\Http\Controllers\EvaluationLevel2Controller;
use App\Http\Controllers\EvaluationLevel34Controller; // Pastikan ini ada
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\MonitoringIndicatorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\FollowUpController; 
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostEvalControlController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Akses Tanpa Login - Untuk Peserta, Narasumber, Atasan)
|--------------------------------------------------------------------------
*/

// Absensi Publik per Sesi
Route::get('absen/{schedule_id}', [AttendanceController::class, 'publicShow'])->name('public.attendance.show');
Route::get('absen/harian/{training_id}/{date}', [AttendanceController::class, 'publicShowDaily'])->name('public.attendance.daily');
Route::post('absen/{schedule_id}', [AttendanceController::class, 'publicStore'])->name('public.attendance.store');

// Evaluasi Level 1 (Kepuasan Peserta)
Route::get('evaluasi-l1/form/{training_id}', [EvaluationLevel1Controller::class, 'publicForm'])->name('public.evall1.form');
Route::post('evaluasi-l1/store/{training_id}', [EvaluationLevel1Controller::class, 'publicStore'])->name('public.evall1.store');

// Evaluasi Level 3 & 4 (Penilaian 360 Derajat)
Route::get('evaluasi-dampak/gateway/{training_id}', [EvaluationLevel34Controller::class, 'publicGateway'])->name('public.l34.gateway');
Route::get('evaluasi-dampak/form/{training_id}/{role}', [EvaluationLevel34Controller::class, 'publicForm'])->name('public.l34.form');
Route::post('evaluasi-dampak/store/{training_id}/{role}', [EvaluationLevel34Controller::class, 'publicStore'])->name('public.l34.store');

Route::get('absen/harian/{training_id}/{date}', [AttendanceController::class, 'publicShowDaily'])->name('public.attendance.daily');
Route::post('absen/harian/{training_id}/{date}', [AttendanceController::class, 'publicStoreDaily'])->name('public.attendance.store_daily');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES (Hanya untuk Admin & Superadmin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    // --- DASHBOARD ---
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    

    // --- 02. KELOLA USER (Khusus Superadmin) ---
    Route::middleware(['can:superadmin-only'])->group(function () {
        Route::resource('users', UserController::class);
        Route::put('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // --- 03 & 04. KELOLA PELATIHAN ---
    Route::resource('trainings', TrainingController::class);

    // Kelola Pertanyaan
    Route::get('questions/download-template', [QuestionController::class, 'downloadTemplate'])->name('questions.template');
    Route::post('questions/import', [QuestionController::class, 'import'])->name('questions.import');
    Route::resource('questions', QuestionController::class);
    Route::get('questions', [QuestionController::class, 'index'])->name('questions.index');
    Route::post('questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::put('questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    
    // Sub-Modul: Import Peserta & Kelola Jadwal
    Route::get('trainings/{id}/export-l34', [EvaluationLevel34Controller::class, 'exportExcel'])->name('evall34.export');
    Route::get('trainings/{id}/export-evaluation', [TrainingController::class, 'exportEvaluation'])->name('trainings.export_evaluation');
    Route::get('trainings/{id}/participants', [TrainingController::class, 'showParticipants'])->name('trainings.participants');
    Route::post('trainings/{id}/participants/import', [TrainingController::class, 'importParticipants'])->name('participants.import');
    Route::put('participants/{id}', [TrainingController::class, 'updateParticipant'])->name('participants.update');
    Route::delete('participants/{id}', [TrainingController::class, 'destroyParticipant'])->name('participants.destroy');
    Route::post('trainings/{id}/participants/manual', [TrainingController::class, 'storeParticipant'])->name('participants.store');
    
    Route::get('trainings/{id}/schedules', [TrainingController::class, 'showSchedules'])->name('trainings.schedules');
    Route::post('trainings/{id}/schedules', [TrainingController::class, 'storeSchedule'])->name('schedules.store');
    
    Route::put('schedules/{id}', [TrainingController::class, 'updateSchedule'])->name('schedules.update');
    Route::delete('schedules/{id}', [TrainingController::class, 'destroySchedule'])->name('schedules.destroy'); // Route Hapus
    
    Route::get('trainings/{id}/export-word-l34', [EvaluationLevel34Controller::class, 'exportWord'])->name('evall34.export_word');
    Route::get('trainings/{id}/schedules/pdf', [TrainingController::class, 'downloadSchedulePdf'])->name('schedules.pdf');
    Route::get('trainings/{id}/evaluasi-l1/progres', [EvaluationLevel1Controller::class, 'showProgres'])->name('evall1.progres');
    Route::delete('trainings/{id}/evaluasi-l1/destroy', [EvaluationLevel1Controller::class, 'destroyForm'])->name('evall1.destroy');
    Route::post('trainings/{id}/evaluasi-l1/create-form', [EvaluationLevel1Controller::class, 'storeForm'])->name('evall1.storeForm');
    Route::delete('evaluasi-l1/form/{id}', [EvaluationLevel1Controller::class, 'destroyForm'])->name('evall1.destroyForm');
    Route::get('trainings/{id}/evaluasi-l2/download-template', [EvaluationLevel2Controller::class, 'downloadTemplate'])->name('evall2.template');
    Route::post('evaluasi-dampak/store/{training_id}/{role}', [EvaluationLevel34Controller::class, 'publicStore'])
    ->name('public.l34.store');
    Route::get('evaluasi-l1/export/{form_id}', [EvaluationLevel1Controller::class, 'exportExcel'])->name('evall1.export');
    
    // Rute Admin Kehadiran Harian
    Route::get('attendance/detail/{id}/{date}', [AttendanceController::class, 'showDetailDaily'])->name('attendance.detail.daily');
    Route::get('attendance/pdf-harian/{id}/{date}', [AttendanceController::class, 'downloadPdfDaily'])->name('attendance.pdf.daily');
    Route::get('attendance/pdf-harian/{id}/{date}', [AttendanceController::class, 'downloadPdfDaily'])->name('attendance.pdf.daily');
    
    // --- 07. KEHADIRAN (Monitoring Admin) ---
    Route::get('attendance/excel-all/{id}', [AttendanceController::class, 'downloadExcelAll'])->name('attendance.excel.all');
    Route::get('attendance/pdf-all/{id}', [AttendanceController::class, 'downloadPdfAll'])->name('attendance.pdf.all');
    Route::get('attendance', [AttendanceController::class, 'indexAll'])->name('attendance.all');
    Route::get('trainings/{id}/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/detail/{schedule_id}', [AttendanceController::class, 'showDetail'])->name('attendance.detail');
    Route::get('attendance/pdf/{schedule_id}', [AttendanceController::class, 'downloadPdf'])->name('attendance.pdf');
    Route::put('attendance/set-time/{schedule_id}', [AttendanceController::class, 'setTime'])->name('attendance.set-time');
    Route::put('attendance/set-time-date/{training_id}', [AttendanceController::class, 'setTimeByDate'])->name('attendance.set-time-date');
    Route::get('monitoring-indicators', [MonitoringIndicatorController::class, 'index'])->name('indicators.index');
    Route::post('monitoring-indicators', [MonitoringIndicatorController::class, 'store'])->name('indicators.store');
    Route::put('monitoring-indicators/{id}', [MonitoringIndicatorController::class, 'update'])->name('indicators.update');
    Route::delete('monitoring-indicators/{id}', [MonitoringIndicatorController::class, 'destroy'])->name('indicators.destroy');
    Route::post('monitoring-indicators/import', [MonitoringIndicatorController::class, 'import'])->name('indicators.import');
    Route::get('monitoring-indicators/template', [MonitoringIndicatorController::class, 'downloadTemplate'])->name('indicators.template');
    Route::post('monitoring/{id}/store-final', [MonitoringController::class, 'storeFinalSummary'])->name('monitoring.store_final');
    
    // --- 10. MONITORING PENYELENGGARAAN ---
    Route::get('monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('monitoring/{id}/fill', [MonitoringController::class, 'create'])->name('monitoring.fill');
    Route::post('monitoring/{id}/store', [MonitoringController::class, 'store'])->name('monitoring.store');
    
    // Export Monitoring
    Route::get('monitoring/{id}/export-laporan', [MonitoringController::class, 'exportLaporan'])->name('monitoring.export.laporan');
    Route::get('monitoring/{id}/export-tindak-lanjut', [MonitoringController::class, 'exportTindakLanjut'])->name('monitoring.export.tindaklanjut');
    Route::get('monitoring/{id}/export-rekap', [MonitoringController::class, 'exportCeklis'])->name('monitoring.export.rekap');
    Route::get('participants/download-template', [TrainingController::class, 'downloadTemplate'])->name('participants.template');
    Route::get('follow-up', [FollowUpController::class, 'index'])->name('followup.index');
    Route::put('follow-up/{id}/resolve', [FollowUpController::class, 'resolve'])->name('followup.resolve');
    
    // --- EVALUASI KIRKPATRICK (Admin View) ---

    // Level 1: Reaction
    Route::get('control-l34', [PostEvalControlController::class, 'index'])->name('control_l34.index');
    Route::get('evaluasi/l1', [EvaluationLevel1Controller::class, 'indexAll'])->name('evaluasi.l1'); // List Pelatihan L1
    Route::get('trainings/{id}/evaluasi-l1', [EvaluationLevel1Controller::class, 'index'])->name('evall1.index'); // Detail L1
    Route::get('evaluasi-l2/download-template', [EvaluationLevel2Controller::class, 'downloadTemplate'])->name('evall2.template');
    Route::get('evaluasi-l34/download-template', [EvaluationLevel34Controller::class, 'downloadTemplate'])->name('evall34.template');
    // Level 2: Learning
    Route::get('evaluasi/l2', [EvaluationLevel2Controller::class, 'indexAll'])->name('evaluasi.l2'); // List Pelatihan L2
    Route::get('trainings/{id}/evaluasi-l2', [EvaluationLevel2Controller::class, 'index'])->name('evall2.index'); // Detail L2
    Route::post('evaluasi-l2/update-single', [EvaluationLevel2Controller::class, 'updateSingle'])->name('evall2.update-single');
    Route::post('trainings/{id}/evaluasi-l2/import', [EvaluationLevel2Controller::class, 'importExcel'])->name('evall2.import');

    // Level 3 & 4: Impact (360)
    Route::get('evaluasi/l34', [EvaluationLevel34Controller::class, 'indexAll'])->name('evaluasi.l34'); // List Pelatihan L34
    Route::get('trainings/{id}/evaluasi-l34', [EvaluationLevel34Controller::class, 'index'])->name('evall34.index'); // Detail L34
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATION SYSTEM
|--------------------------------------------------------------------------
*/

// Menonaktifkan Registrasi Publik agar hanya Superadmin yang bisa buat akun
Auth::routes(['register' => false]);

// Redirect default Laravel
Route::get('/home', function() {
    return redirect()->route('dashboard');
});
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/login');
});