<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EvaluationLevel1Controller;
use App\Http\Controllers\EvaluationLevel2Controller;
use App\Http\Controllers\EvaluationLevel12ReportController;
use App\Http\Controllers\EvaluationLevel34Controller;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\MonitoringIndicatorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\FollowUpController; 
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostEvalControlController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\PengajarSetupController; // <-- TAMBAHAN CONTROLLER PENGAJAR
use App\Http\Controllers\PengajarController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TrainingForumController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\PublicCertificationBiodataController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\PartnerSubmissionController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Akses Tanpa Login - Untuk Peserta, Narasumber, Atasan)
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Tanpa Login)
|--------------------------------------------------------------------------
*/

// LANDING PAGE (Satu-satunya rute untuk '/')
Route::get('/', [App\Http\Controllers\LandingController::class, 'index'])->name('landing');

// Search Global (Hanya hasil, aksi di dalam auth)
Route::get('/search', [SearchController::class, 'index'])->name('global.search');

// Google Auth
Route::get('auth/google', [App\Http\Controllers\Auth\LoginController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [App\Http\Controllers\Auth\LoginController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Document Share
Route::get('share/folder/{token}/file/{file}/excel', [DocumentController::class, 'previewSharedExcel'])->name('documents.public.excel');
Route::get('share/folder/{token}/file/{file}/preview', [DocumentController::class, 'previewSharedFile'])->name('documents.public.file');
Route::get('share/folder/{token}', [DocumentController::class, 'share'])->name('documents.public');
// Tambahan Document Action (Download/View File)
Route::get('/documents/file/{id}/view', [DocumentController::class, 'viewFile'])->name('documents.file.view');
Route::get('/documents/file/{id}/download', [DocumentController::class, 'downloadFile'])->name('documents.file.download');

// Absensi Publik
Route::get('absen/{schedule_id}', [AttendanceController::class, 'publicShow'])->name('public.attendance.show');
Route::get('absen/harian/{training_id}/{date}', [AttendanceController::class, 'publicShowDaily'])->name('public.attendance.daily');
Route::post('absen/harian/{training_id}/{date}', [AttendanceController::class, 'publicStoreDaily'])->name('public.attendance.store_daily');

// Evaluasi Level 1 Publik
Route::get('evaluasi-l1/form/{training_id}', [EvaluationLevel1Controller::class, 'publicForm'])->name('public.evall1.form');
Route::post('evaluasi-l1/store/{training_id}', [EvaluationLevel1Controller::class, 'publicStore'])->name('public.evall1.store');

// Evaluasi Level 3 & 4 Publik
Route::get('evaluasi-dampak/gateway/{training_id}', [EvaluationLevel34Controller::class, 'publicGateway'])->name('public.l34.gateway');
Route::get('evaluasi-dampak/form/{training_id}/{role}', [EvaluationLevel34Controller::class, 'publicForm'])->name('public.l34.form');
Route::post('evaluasi-dampak/store/{training_id}/{role}', [EvaluationLevel34Controller::class, 'publicStore'])->name('public.l34.store');

/*
|--------------------------------------------------------------------------
| 2. AUTHENTICATION SYSTEM
|--------------------------------------------------------------------------
*/
Auth::routes(['register' => false]);
Route::get('sertifikasi/biodata/{token}', [PublicCertificationBiodataController::class, 'index'])->name('certifications.public');
Route::post('sertifikasi/biodata/{token}', [PublicCertificationBiodataController::class, 'verify'])->name('certifications.public.verify');
Route::get('sertifikasi/biodata/{token}/{participantToken}', [PublicCertificationBiodataController::class, 'form'])->name('certifications.public.form');
Route::post('sertifikasi/biodata/{token}/{participantToken}', [PublicCertificationBiodataController::class, 'submit'])->name('certifications.public.submit');

Route::get('/logout', function() {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/')->with('success', 'Anda telah berhasil keluar.');
});

Route::middleware(['auth'])->group(function () {
    Route::get('certifications/template', [CertificationController::class, 'template'])->name('certifications.template');
    Route::get('certifications/export', [CertificationController::class, 'export'])->name('certifications.export');
    Route::post('certifications/types', [CertificationController::class, 'storeType'])->name('certifications.types.store');
    Route::delete('certifications/types/{type}', [CertificationController::class, 'destroyType'])->name('certifications.types.destroy');
    Route::get('certifications', [CertificationController::class, 'index'])->name('certifications.index');
    Route::post('certifications', [CertificationController::class, 'storeEvent'])->name('certifications.store');
    Route::put('certifications/{event}', [CertificationController::class, 'updateEvent'])->name('certifications.update');
    Route::delete('certifications/{event}', [CertificationController::class, 'destroyEvent'])->name('certifications.destroy');
    Route::get('certifications/{event}', [CertificationController::class, 'show'])->name('certifications.show');
    Route::post('certifications/{event}/participants/import', [CertificationController::class, 'import'])->name('certifications.import');
    Route::delete('certifications/{event}/participants', [CertificationController::class, 'destroyAllParticipants'])->name('certifications.participants.destroy-all');
    Route::delete('certification-participants/{participant}', [CertificationController::class, 'destroyParticipant'])->name('certifications.participants.destroy');
    Route::post('certifications/{event}/minutes', [CertificationController::class, 'uploadMinutes'])->name('certifications.minutes');
    Route::put('certification-participants/{participant}/result', [CertificationController::class, 'updateResult'])->name('certifications.participants.result');
    Route::get('assets/dashboard', [AssetController::class, 'dashboard'])->name('assets.dashboard');
    Route::get('assets/monitoring', [AssetController::class, 'monitoring'])->name('assets.monitoring');
    // Jangan gunakan URI tepat /assets karena bertabrakan dengan folder public/assets.
    Route::get('assets/kelola', [AssetController::class, 'index'])->name('assets.index');
    Route::post('assets/kelola', [AssetController::class, 'store'])->name('assets.store');
    Route::put('assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
    Route::delete('assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');
    Route::get('agendas', [AgendaController::class, 'index'])->name('agendas.index');
    Route::get('agendas/create', [AgendaController::class, 'create'])->name('agendas.create');
    Route::get('agendas-availability', [AgendaController::class, 'availability'])->name('agendas.availability');
    Route::post('agendas', [AgendaController::class, 'store'])->name('agendas.store');
    Route::get('agendas/{agenda}/edit', [AgendaController::class, 'edit'])->name('agendas.edit');
    Route::put('agendas/{agenda}', [AgendaController::class, 'update'])->name('agendas.update');
    Route::delete('agendas/{agenda}', [AgendaController::class, 'destroy'])->name('agendas.destroy');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/trainings/{training}/forum', [TrainingForumController::class, 'index'])->name('training.forum.index');
    Route::get('/trainings/{training}/forum/messages', [TrainingForumController::class, 'messages'])->name('training.forum.messages');
    Route::post('/trainings/{training}/forum/messages', [TrainingForumController::class, 'store'])->name('training.forum.store');
    Route::delete('/trainings/{training}/forum/messages/{message}', [TrainingForumController::class, 'destroy'])->name('training.forum.destroy');
    
    // --- DASHBOARD ---
    // Portal Mitra dan pengelolaan pengajuan
    Route::get('mitra', [PartnerSubmissionController::class, 'index'])->name('mitra.dashboard');
    Route::get('mitra/pengajuan/create/{type}', [PartnerSubmissionController::class, 'create'])->name('mitra.submissions.create');
    Route::post('mitra/pengajuan', [PartnerSubmissionController::class, 'store'])->name('mitra.submissions.store');
    Route::get('mitra/pengajuan/{submission}', [PartnerSubmissionController::class, 'show'])->name('mitra.submissions.show');
    Route::put('mitra/pengajuan/{submission}', [PartnerSubmissionController::class, 'update'])->name('mitra.submissions.update');
    Route::put('mitra/pengajuan/{submission}/submit', [PartnerSubmissionController::class, 'submit'])->name('mitra.submissions.submit');
    Route::get('mitra/pengajuan/{submission}/comments', [PartnerSubmissionController::class, 'comments'])->name('mitra.submissions.comments');
    Route::post('mitra/pengajuan/{submission}/comments', [PartnerSubmissionController::class, 'comment'])->name('mitra.submissions.comment');
    Route::post('mitra/pengajuan/{submission}/documents', [PartnerSubmissionController::class, 'upload'])->name('mitra.submissions.upload');
    Route::get('mitra/dokumen/{document}/download', [PartnerSubmissionController::class, 'download'])->name('mitra.documents.download');
    Route::get('pengajuan-mitra', [PartnerSubmissionController::class, 'adminIndex'])->name('mitra.admin.index');
    Route::put('pengajuan-mitra/{submission}/finalize', [PartnerSubmissionController::class, 'finalize'])->name('mitra.admin.finalize');
    Route::put('pengajuan-mitra/{submission}/reopen', [PartnerSubmissionController::class, 'reopen'])->name('mitra.admin.reopen');
    Route::delete('pengajuan-mitra/{submission}', [PartnerSubmissionController::class, 'destroy'])->name('mitra.admin.destroy');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
     
    // --- 02. KELOLA USER (Khusus Superadmin) ---
    Route::middleware(['can:superadmin-only'])->group(function () {
        Route::resource('users', UserController::class);
        Route::put('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::put('users/{user}/approve-type', [UserController::class, 'approveUserType'])->name('users.approve-type');
    });

    // --- PENGATURAN PROFIL UMUM ---
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // =========================================================================
    // --- SETUP PROFIL PENGAJAR (Wajib saat pertama kali Pengajar login) ---
    // =========================================================================
    Route::get('/pengajar/setup-profil', [PengajarSetupController::class, 'index'])->name('pengajar.setup');
    Route::post('/pengajar/setup-profil', [PengajarSetupController::class, 'store'])->name('pengajar.setup.store');

    // --- SETUP PROFIL PESERTA ---
    Route::get('/complete-profile', [ParticipantController::class, 'completeProfile'])->name('participant.profile.complete');
    Route::post('/complete-profile', [ParticipantController::class, 'storeProfile'])->name('participant.profile.store');

    // Rute Menu Jadwal Mengajar (Hanya untuk Pengajar)
    Route::get('/pengajar/jadwal-mengajar', [\App\Http\Controllers\TrainingController::class, 'pengajarSchedules'])->name('pengajar.schedule');
    Route::get('/pengajar', [PengajarController::class, 'index'])->name('pengajar.index');
    Route::get('/pengajar/pelatihan/{training}', [PengajarController::class, 'manage'])->name('pengajar.manage');
    Route::put('/pengajar/profil', [PengajarController::class, 'updateProfile'])->name('pengajar.profile.update');
    Route::post('/pengajar/pelatihan/{training}/kelengkapan', [PengajarController::class, 'uploadRequirements'])->name('pengajar.requirements.upload');
    Route::post('/pengajar/sesi/{schedule}/dokumen', [PengajarController::class, 'uploadSession'])->name('pengajar.session.upload');

    // --- 03 & 04. KELOLA PELATIHAN ---
    Route::resource('trainings', TrainingController::class);

    // Kelola Dokumen & Folder
    Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('documents/folder', [DocumentController::class, 'createFolder'])->name('documents.folder.create');
    Route::post('documents/upload', [DocumentController::class, 'uploadFiles'])->name('documents.upload');
    Route::put('documents/folder/{id}/privacy', [DocumentController::class, 'togglePrivacy'])->name('documents.folder.privacy');
    Route::get('documents/share-users/search', [DocumentController::class, 'searchShareUsers'])->name('documents.share-users.search');
    Route::get('documents/folder/{folder}/sharing', [DocumentController::class, 'sharing'])->name('documents.folder.sharing');
    Route::post('documents/folder/{folder}/sharing', [DocumentController::class, 'shareWithUser'])->name('documents.folder.sharing.store');
    Route::delete('documents/folder/{folder}/sharing/{user}', [DocumentController::class, 'revokeShare'])->name('documents.folder.sharing.destroy');
    Route::get('documents/file/{file}/versions', [DocumentController::class, 'fileVersions'])->name('documents.file.versions');
    Route::get('documents/file-versions/{version}/download', [DocumentController::class, 'downloadVersion'])->name('documents.file-versions.download');
    Route::post('documents/file-versions/{version}/restore', [DocumentController::class, 'restoreVersion'])->name('documents.file-versions.restore');
    Route::delete('documents/file/{id}', [DocumentController::class, 'destroyFile'])->name('documents.file.destroy');
    Route::delete('documents/folder/{id}', [DocumentController::class, 'destroyFolder'])->name('documents.folder.destroy');

    // Kelola Pertanyaan
    Route::get('questions/download-template', [QuestionController::class, 'downloadTemplate'])->name('questions.template');
    Route::post('questions/import', [QuestionController::class, 'import'])->name('questions.import');
    Route::delete('questions/delete-bundle', [QuestionController::class, 'destroyBundle'])->name('questions.destroy-bundle');
    Route::post('questions/duplicate-bundle', [QuestionController::class, 'duplicateBundle'])->name('questions.duplicate-bundle');
    Route::post('questions/{question}/duplicate', [QuestionController::class, 'duplicateQuestion'])->name('questions.duplicate');
    Route::resource('questions', QuestionController::class);
    Route::get('questions', [QuestionController::class, 'index'])->name('questions.index');
    Route::post('questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::put('questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    
    // Sub-Modul: Import Peserta & Kelola Jadwal
    Route::get('trainings/{id}/export-participants-data', [TrainingController::class, 'exportParticipants'])->name('participants.export_data');
    Route::get('trainings/{id}/export-invitation-l34', [EvaluationLevel34Controller::class, 'exportInvitation'])->name('evall34.export_invitation');
    Route::get('trainings/{id}/export-l34', [EvaluationLevel34Controller::class, 'exportExcel'])->name('evall34.export');
    Route::get('trainings/{id}/export-evaluation', [TrainingController::class, 'exportEvaluation'])->name('trainings.export_evaluation');
    Route::get('trainings/{id}/export-word-l12', [EvaluationLevel12ReportController::class, 'exportWord'])->name('evall12.export_word');
    Route::get('trainings/{id}/participants', [TrainingController::class, 'showParticipants'])->name('trainings.participants');
    Route::get('trainings/{id}/manage', [TrainingController::class, 'manage'])->name('trainings.manage');
    Route::post('trainings/{id}/participants/import', [TrainingController::class, 'importParticipants'])->name('participants.import');
    Route::put('participants/{id}', [TrainingController::class, 'updateParticipant'])->name('participants.update');
    Route::delete('participants/{id}', [TrainingController::class, 'destroyParticipant'])->name('participants.destroy');
    Route::post('trainings/{id}/participants/manual', [TrainingController::class, 'storeParticipant'])->name('participants.store');
    
    Route::get('trainings/{id}/schedules', [TrainingController::class, 'showSchedules'])->name('trainings.schedules');
    Route::post('trainings/{id}/schedules', [TrainingController::class, 'storeSchedule'])->name('schedules.store');
    Route::put('trainings/{id}/set-lms', [TrainingController::class, 'setLmsLink'])->name('trainings.set_lms');
    Route::put('schedules/{id}', [TrainingController::class, 'updateSchedule'])->name('schedules.update');
    Route::delete('schedules/{id}', [TrainingController::class, 'destroySchedule'])->name('schedules.destroy');
    
    Route::put('participants/{id}/approve', [TrainingController::class, 'approveParticipant'])->name('participants.approve');
    Route::put('trainings/{id}/participants/approve-bulk', [TrainingController::class, 'approveParticipantsBulk'])->name('participants.approve-bulk');
    Route::put('participants/{id}/reject', [TrainingController::class, 'rejectParticipant'])->name('participants.reject');
    Route::get('trainings/{id}/new-code', [TrainingController::class, 'generateNewCode'])->name('trainings.new_code');
    
    Route::get('trainings/{id}/export-word-l34', [EvaluationLevel34Controller::class, 'exportWord'])->name('evall34.export_word');
    Route::get('trainings/{id}/schedules/pdf', [TrainingController::class, 'downloadSchedulePdf'])->name('schedules.pdf');
    Route::get('trainings/{id}/evaluasi-l1/progres', [EvaluationLevel1Controller::class, 'showProgres'])->name('evall1.progres');
    Route::delete('trainings/{id}/evaluasi-l1/destroy', [EvaluationLevel1Controller::class, 'destroyForm'])->name('evall1.destroy');
    Route::post('trainings/{id}/evaluasi-l1/create-form', [EvaluationLevel1Controller::class, 'storeForm'])->name('evall1.storeForm');
    Route::delete('evaluasi-l1/form/{id}', [EvaluationLevel1Controller::class, 'destroyForm'])->name('evall1.destroyForm');
    Route::get('trainings/{id}/evaluasi-l2/download-template', [EvaluationLevel2Controller::class, 'downloadTemplate'])->name('evall2.template');
    
    Route::get('evaluasi-l1/export/{form_id}', [EvaluationLevel1Controller::class, 'exportExcel'])->name('evall1.export');
    
    // Rute Admin Kehadiran Harian
    Route::get('attendance/detail/{id}/{date}', [AttendanceController::class, 'showDetailDaily'])->name('attendance.detail.daily');
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
    Route::put('follow-up/{id}/start', [FollowUpController::class, 'start'])->name('followup.start');
    Route::put('follow-up/{id}/resolve', [FollowUpController::class, 'resolve'])->name('followup.resolve');
    Route::put('follow-up/{id}/verify', [FollowUpController::class, 'verify'])->name('followup.verify');

    // Export & Import Jadwal Pelatihan
    Route::get('schedules/download-template', [\App\Http\Controllers\TrainingController::class, 'downloadScheduleTemplate'])->name('schedules.template');
    Route::post('trainings/{id}/schedules/import', [\App\Http\Controllers\TrainingController::class, 'importSchedules'])->name('schedules.import');

    // RUTE RIWAYAT PELATIHAN PENGAJAR
    Route::get('/pengajar/riwayat-pelatihan', [\App\Http\Controllers\TrainingController::class, 'pengajarHistory'])->name('pengajar.history');

    
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

    // Kelola Alumni
    Route::get('alumni-statistics/export', [AlumniController::class, 'exportExcel'])->name('alumni.export');
    Route::get('alumni', [AlumniController::class, 'index'])->name('alumni.index');
        
    // --- 3. RUTE KHUSUS PESERTA (Sudah Login & Role Participant) ---
    Route::middleware(['can:isParticipant'])->prefix('participant')->group(function () {
        Route::get('/dashboard', [ParticipantController::class, 'index'])->name('participant.dashboard');
        Route::get('/trainings', [ParticipantController::class, 'availableTrainings'])->name('participant.trainings');
    
        // Detail pelatihan dan kelengkapan peserta
        Route::get('/training/{id}/detail', [ParticipantController::class, 'showTrainingDetail'])->name('participant.training.show');
        Route::post('/join-training', [ParticipantController::class, 'enrollByCode'])->name('participant.training.join_by_code');
        Route::post('/training/{id}/detail/upload', [ParticipantController::class, 'uploadRequirement'])
            ->name('participant.training.upload');
        
        // Riwayat
        Route::get('/history', [ParticipantController::class, 'myHistory'])->name('participant.history');
    });

});
