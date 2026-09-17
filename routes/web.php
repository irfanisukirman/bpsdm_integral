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
use App\Http\Controllers\InternalAiAssistantController;
use App\Http\Controllers\LoginHelpSettingController;
use App\Http\Controllers\InternshipController;
use App\Http\Controllers\PublicInternshipController;
use App\Http\Controllers\InternshipDashboardController;
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
use App\Http\Controllers\AssetLoanRequestController;
use App\Http\Controllers\AssetRentalController;
use App\Http\Controllers\ElectronicSignatureController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\TrainingCertificateController;
use App\Http\Controllers\TrainingActivityReportController;
use App\Http\Controllers\PublicCertificationBiodataController;
use App\Http\Controllers\PublicCertificationSpeakerController;
use App\Http\Controllers\PublicCertificationCertificateController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\PublicDailyScheduleController;
use App\Http\Controllers\PartnerSubmissionController;
use App\Http\Controllers\ActivityAttendanceController;
use App\Http\Controllers\PublicActivityAttendanceController;
use App\Http\Controllers\GuestBookController;
use App\Http\Controllers\PublicGuestBookController;
use App\Http\Controllers\HotlineController;
use App\Http\Controllers\Admin\TicketingController;
use App\Http\Controllers\Admin\TicketingMasterController;

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
// HOTLINE (Widget & Tracking)
Route::get('hotline/availability', [HotlineController::class, 'availability'])->name('hotline.availability');
Route::post('hotline', [HotlineController::class, 
'store'])->middleware('throttle:10,1')->name('hotline.store');
Route::get('hotline/success/{ticket_number}', [HotlineController::class, 'success'])->name('hotline.success');
Route::get('hotline/tracking/{token}', [HotlineController::class, 'tracking'])->where('token', '[a-f0-9]{64}')->name('hotline.tracking');
Route::post('hotline/tracking/{token}', [HotlineController::class, 'replyTracking'])->where('token', '[a-f0-9]{64}')->middleware('throttle:10,1')->name('hotline.tracking.reply');

// LANDING PAGE (Satu-satunya rute untuk '/')
Route::get('/', [App\Http\Controllers\LandingController::class, 'index'])->name('landing');
Route::get('jadwalharian/{token?}', [PublicDailyScheduleController::class, 'index'])->where('token', '[a-f0-9]{64}')->name('public.daily-schedule');
Route::get('reservasi-fasilitas', [AssetRentalController::class, 'catalog'])->name('public.asset-rentals.index');
Route::post('reservasi-fasilitas/lacak', [AssetRentalController::class, 'lookup'])->middleware('throttle:10,1')->name('public.asset-rentals.lookup');
Route::get('reservasi-fasilitas/{asset}', [AssetRentalController::class, 'show'])->name('public.asset-rentals.show');
Route::post('reservasi-fasilitas/{asset}', [AssetRentalController::class, 'store'])->middleware('throttle:5,1')->name('public.asset-rentals.store');
Route::get('reservasi/status/{token}/kwitansi', [AssetRentalController::class, 'receipt'])->name('public.asset-rentals.receipt');
Route::get('reservasi/status/{token}', [AssetRentalController::class, 'status'])->name('public.asset-rentals.status');
Route::post('reservasi/status/{token}/pembayaran', [AssetRentalController::class, 'uploadPayment'])->middleware('throttle:5,1')->name('public.asset-rentals.payment');

Route::get('buku-tamu/publik/{token}', [PublicGuestBookController::class, 'show'])->name('guest-book.public.show');
Route::post('buku-tamu/publik/{token}', [PublicGuestBookController::class, 'store'])->middleware('throttle:10,1')->name('guest-book.public.store');
Route::get('buku-tamu/publik/{token}/selesai/{code}', [PublicGuestBookController::class, 'success'])->name('guest-book.public.success');
Route::get('presensi-kegiatan/publik/{token}', [PublicActivityAttendanceController::class, 'show'])->middleware('throttle:60,1')->name('activity-attendance.public.show');
Route::post('presensi-kegiatan/publik/{token}', [PublicActivityAttendanceController::class, 'store'])->middleware('throttle:10,1')->name('activity-attendance.public.store');
Route::get('presensi-kegiatan/publik/{token}/selesai/{responseToken}', [PublicActivityAttendanceController::class, 'success'])->middleware('throttle:30,1')->name('activity-attendance.public.success');
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
Route::get('magang/daftar/{token}', [PublicInternshipController::class, 'register'])->name('internships.public.register');
Route::post('magang/daftar/{token}', [PublicInternshipController::class, 'store'])->middleware('throttle:10,1')->name('internships.public.store');

Auth::routes(['register' => false]);
Route::get('sertifikasi/biodata/{token}', [PublicCertificationBiodataController::class, 'index'])->name('certifications.public');
Route::post('sertifikasi/biodata/{token}', [PublicCertificationBiodataController::class, 'verify'])->name('certifications.public.verify');
Route::get('sertifikasi/biodata/{token}/{participantToken}', [PublicCertificationBiodataController::class, 'form'])->name('certifications.public.form');
Route::post('sertifikasi/biodata/{token}/{participantToken}', [PublicCertificationBiodataController::class, 'submit'])->name('certifications.public.submit');
Route::get('sertifikasi/narasumber/{token}', [PublicCertificationSpeakerController::class, 'form'])->middleware('throttle:30,1')->name('certifications.speakers.public');
Route::post('sertifikasi/narasumber/{token}', [PublicCertificationSpeakerController::class, 'submit'])->middleware('throttle:10,1')->name('certifications.speakers.public.submit');
Route::get('sertifikasi/sertifikat/{token}', [PublicCertificationCertificateController::class, 'index'])->middleware('throttle:30,1')->name('certifications.certificates.public');
Route::post('sertifikasi/sertifikat/{token}', [PublicCertificationCertificateController::class, 'verify'])->middleware('throttle:20,1')->name('certifications.certificates.public.verify');
Route::get('sertifikasi/sertifikat/{token}/{participantToken}', [PublicCertificationCertificateController::class, 'form'])->middleware('throttle:30,1')->name('certifications.certificates.public.form');
Route::post('sertifikasi/sertifikat/{token}/{participantToken}', [PublicCertificationCertificateController::class, 'submit'])->middleware('throttle:10,1')->name('certifications.certificates.public.submit');

Route::get('/logout', function() {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/')->with('success', 'Anda telah berhasil keluar.');
});

Route::get('verifikasi-tte/{token}/unduh', [ElectronicSignatureController::class, 'verifyDownload'])
    ->middleware('throttle:30,1')->name('electronic-signatures.verify.download');
Route::get('verifikasi-tte/{token}', [ElectronicSignatureController::class, 'verify'])
    ->middleware('throttle:30,1')->name('electronic-signatures.verify');

Route::middleware(['auth'])->group(function () {
    Route::get('tanda-tangan-elektronik', [ElectronicSignatureController::class, 'index'])->name('electronic-signatures.index');
    Route::get('tanda-tangan-elektronik/akun', [ElectronicSignatureController::class, 'accounts'])->name('electronic-signatures.accounts');
    Route::post('tanda-tangan-elektronik/akun', [ElectronicSignatureController::class, 'storeAccount'])->name('electronic-signatures.accounts.store');
    Route::put('tanda-tangan-elektronik/akun/{user}/password', [ElectronicSignatureController::class, 'updateAccountPassword'])->name('electronic-signatures.accounts.password');
    Route::delete('tanda-tangan-elektronik/akun/{user}', [ElectronicSignatureController::class, 'destroyAccount'])->name('electronic-signatures.accounts.destroy');
    Route::get('tanda-tangan-elektronik/integral', fn (\Illuminate\Http\Request $request) => app(ElectronicSignatureController::class)->category($request, 'training_certificates'))->name('electronic-signatures.integral');
    Route::get('tanda-tangan-elektronik/jct', fn (\Illuminate\Http\Request $request) => app(ElectronicSignatureController::class)->category($request, 'jct_certificates'))->name('electronic-signatures.jct');
    Route::get('tanda-tangan-elektronik/dokumen-lain', fn (\Illuminate\Http\Request $request) => app(ElectronicSignatureController::class)->category($request, 'other_documents'))->name('electronic-signatures.documents');
    Route::get('tanda-tangan-elektronik/integral/create', [ElectronicSignatureController::class, 'createIntegral'])->name('electronic-signatures.integral.create');
    Route::post('tanda-tangan-elektronik/integral', [ElectronicSignatureController::class, 'storeIntegral'])->name('electronic-signatures.integral.store');
    Route::get('tanda-tangan-elektronik/jct/create', [ElectronicSignatureController::class, 'createJct'])->name('electronic-signatures.jct.create');
    Route::post('tanda-tangan-elektronik/jct', [ElectronicSignatureController::class, 'storeJct'])->name('electronic-signatures.jct.store');
    Route::get('tanda-tangan-elektronik/dokumen-lain/create', [ElectronicSignatureController::class, 'createDocuments'])->name('electronic-signatures.documents.create');
    Route::post('tanda-tangan-elektronik/dokumen-lain', [ElectronicSignatureController::class, 'storeDocuments'])->name('electronic-signatures.documents.store');
    Route::get('tanda-tangan-elektronik/create', [ElectronicSignatureController::class, 'create'])->name('electronic-signatures.create');
    Route::post('tanda-tangan-elektronik', [ElectronicSignatureController::class, 'store'])->name('electronic-signatures.store');
    Route::get('tanda-tangan-elektronik/{electronicSignature}/download-zip', [ElectronicSignatureController::class, 'downloadZip'])->name('electronic-signatures.download-zip');
    Route::get('tanda-tangan-elektronik/{electronicSignature}', [ElectronicSignatureController::class, 'show'])->name('electronic-signatures.show');
    Route::delete('tanda-tangan-elektronik/{electronicSignature}', [ElectronicSignatureController::class, 'destroyRequest'])->name('electronic-signatures.destroy');
    Route::post('tanda-tangan-elektronik/actions/{action}/sign', [ElectronicSignatureController::class, 'sign'])->middleware('throttle:5,1')->name('electronic-signatures.sign');
    Route::get('tanda-tangan-elektronik/documents/{document}/download', [ElectronicSignatureController::class, 'download'])->name('electronic-signatures.download');
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
    Route::put('certifications/{event}/participants/pass-all', [CertificationController::class, 'passAllParticipants'])->name('certifications.participants.pass-all');
    Route::delete('certifications/{event}/participants', [CertificationController::class, 'destroyAllParticipants'])->name('certifications.participants.destroy-all');
    Route::delete('certification-participants/{participant}', [CertificationController::class, 'destroyParticipant'])->name('certifications.participants.destroy');
    Route::post('certifications/{event}/minutes', [CertificationController::class, 'uploadMinutes'])->name('certifications.minutes');
    Route::put('certification-participants/{participant}/result', [CertificationController::class, 'updateResult'])->name('certifications.participants.result');
    Route::get('assets/dashboard', [AssetController::class, 'dashboard'])->name('assets.dashboard');
    Route::get('assets/reservasi', [AssetRentalController::class, 'adminIndex'])->name('asset-rentals.admin.index');
    Route::put('assets/reservasi/pengaturan', [AssetRentalController::class, 'updateSettings'])->name('asset-rentals.settings.update');
    Route::put('assets/reservasi/{reservation}', [AssetRentalController::class, 'review'])->name('asset-rentals.admin.review');
    Route::delete('assets/reservasi/{reservation}', [AssetRentalController::class, 'destroy'])->name('asset-rentals.admin.destroy');
    Route::get('assets/reservasi/{reservation}/bukti', [AssetRentalController::class, 'paymentProof'])->name('asset-rentals.admin.payment-proof');
    Route::get('assets/monitoring', [AssetController::class, 'monitoring'])->name('assets.monitoring');
    Route::get('assets/persetujuan', [AssetLoanRequestController::class, 'index'])->name('asset-loans.index');
    Route::get('assets/persetujuan/{loan}/surat', [AssetLoanRequestController::class, 'document'])->name('asset-loans.document');
    Route::put('assets/persetujuan/{loan}', [AssetLoanRequestController::class, 'review'])->name('asset-loans.review');
    Route::get('monitoring/jadwal-harian', [AssetController::class, 'dailySchedule'])->name('daily-schedule.index');
    // Jangan gunakan URI tepat /assets karena bertabrakan dengan folder public/assets.
    Route::get('assets/kelola', [AssetController::class, 'index'])->name('assets.index');
    Route::post('assets/kelola', [AssetController::class, 'store'])->name('assets.store');
    Route::put('assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
    Route::delete('assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');
    Route::get('buku-tamu', [GuestBookController::class, 'index'])->name('guest-book.index');
    Route::post('buku-tamu/lokasi', [GuestBookController::class, 'storeLocation'])->name('guest-book.locations.store');
    Route::put('buku-tamu/lokasi/{location}/status', [GuestBookController::class, 'toggleLocation'])->name('guest-book.locations.toggle');
    Route::get('buku-tamu/lokasi/{location}/qr', [GuestBookController::class, 'qr'])->name('guest-book.qr');
    Route::put('buku-tamu/kunjungan/{visit}/bidang', [GuestBookController::class, 'updateTargetBidang'])->name('guest-book.target-bidang');
    Route::put('buku-tamu/kunjungan/{visit}/keluar', [GuestBookController::class, 'checkout'])->name('guest-book.checkout');
    Route::get('buku-tamu/export/excel', [GuestBookController::class, 'exportExcel'])->name('guest-book.export.excel');
    Route::get('buku-tamu/export/pdf', [GuestBookController::class, 'exportPdf'])->name('guest-book.export.pdf');
    Route::get('buku-tamu/pengaturan/akun', [GuestBookController::class, 'accounts'])->name('guest-book.accounts');
    Route::post('buku-tamu/pengaturan/akun', [GuestBookController::class, 'storeAccount'])->name('guest-book.accounts.store');
    Route::put('buku-tamu/pengaturan/akun/{user}/password', [GuestBookController::class, 'resetAccount'])->name('guest-book.accounts.reset');    Route::get('presensi-kegiatan', [ActivityAttendanceController::class, 'index'])->name('activity-attendance.index');
    Route::get('presensi-kegiatan/create', [ActivityAttendanceController::class, 'create'])->name('activity-attendance.create');
    Route::post('presensi-kegiatan', [ActivityAttendanceController::class, 'store'])->name('activity-attendance.store');
    Route::post('presensi-kegiatan/{activityAttendance}/duplicate', [ActivityAttendanceController::class, 'duplicate'])->name('activity-attendance.duplicate');
    Route::get('presensi-kegiatan/{activityAttendance}/qr-code', [ActivityAttendanceController::class, 'qrCode'])->name('activity-attendance.qr');
    Route::get('presensi-kegiatan/{activityAttendance}/export-excel', [ActivityAttendanceController::class, 'exportExcel'])->name('activity-attendance.export.excel');
    Route::get('presensi-kegiatan/{activityAttendance}/export-pdf', [ActivityAttendanceController::class, 'exportPdf'])->name('activity-attendance.export.pdf');
    Route::get('presensi-kegiatan/{activityAttendance}/attachments', [ActivityAttendanceController::class, 'downloadAttachments'])->name('activity-attendance.attachments');
    Route::get('presensi-kegiatan/{activityAttendance}/edit', [ActivityAttendanceController::class, 'edit'])->name('activity-attendance.edit');
    Route::put('presensi-kegiatan/{activityAttendance}', [ActivityAttendanceController::class, 'update'])->name('activity-attendance.update');
    Route::delete('presensi-kegiatan/{activityAttendance}', [ActivityAttendanceController::class, 'destroy'])->name('activity-attendance.destroy');
    Route::post('presensi-kegiatan/{activityAttendance}/questions', [ActivityAttendanceController::class, 'storeQuestion'])->name('activity-attendance.questions.store');
    Route::post('presensi-kegiatan/{activityAttendance}/questions/reorder', [ActivityAttendanceController::class, 'reorderQuestions'])->name('activity-attendance.questions.reorder');
    Route::put('presensi-kegiatan/questions/{question}', [ActivityAttendanceController::class, 'updateQuestion'])->name('activity-attendance.questions.update');
    Route::delete('presensi-kegiatan/questions/{question}', [ActivityAttendanceController::class, 'destroyQuestion'])->name('activity-attendance.questions.destroy');
    Route::get('presensi-kegiatan/{activityAttendance}/responses', [ActivityAttendanceController::class, 'responses'])->name('activity-attendance.responses');
    Route::get('presensi-kegiatan/responses/{response}', [ActivityAttendanceController::class, 'response'])->name('activity-attendance.responses.show');
    Route::get('presensi-kegiatan/answers/{answer}/download', [ActivityAttendanceController::class, 'download'])->name('activity-attendance.answers.download');
    Route::get('agendas', [AgendaController::class, 'index'])->name('agendas.index');
    Route::get('agendas/create', [AgendaController::class, 'create'])->name('agendas.create');
    Route::get('agendas-availability', [AgendaController::class, 'availability'])->name('agendas.availability');
    Route::post('agendas', [AgendaController::class, 'store'])->name('agendas.store');
    Route::get('agendas/{agenda}/edit', [AgendaController::class, 'edit'])->name('agendas.edit');
    Route::put('agendas/{agenda}', [AgendaController::class, 'update'])->name('agendas.update');
    Route::delete('agendas/{agenda}', [AgendaController::class, 'destroy'])->name('agendas.destroy');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/asset-loans/{loan}/{status}', [NotificationController::class, 'openAssetLoan'])->where('status', 'approved|revision|rejected')->name('notifications.asset-loan.open');
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
    Route::get('magang/dashboard', [InternshipDashboardController::class, 'index'])->name('internships.dashboard');
    Route::post('magang/presensi/masuk', [InternshipDashboardController::class, 'checkIn'])->name('internships.attendance.check-in');
    Route::post('magang/presensi/pulang', [InternshipDashboardController::class, 'checkOut'])->name('internships.attendance.check-out');
    Route::post('magang/presensi/ketidakhadiran', [InternshipDashboardController::class, 'absence'])->name('internships.attendance.absence');
    Route::get('magang/sertifikat', [InternshipDashboardController::class, 'downloadCertificate'])->name('internships.certificate.download');
    Route::get('presensi-magang', [InternshipController::class, 'index'])->name('internships.index');
    Route::get('presensi-magang/pengelola/akun', [InternshipController::class, 'managerAccounts'])->name('internships.managers.index');
    Route::post('presensi-magang/pengelola/akun', [InternshipController::class, 'storeManagerAccount'])->name('internships.managers.store');
    Route::put('presensi-magang/pengelola/akun/{user}/password', [InternshipController::class, 'resetManagerPassword'])->name('internships.managers.password');
    Route::delete('presensi-magang/pengelola/akun/{user}', [InternshipController::class, 'destroyManagerAccount'])->name('internships.managers.destroy');
    Route::post('presensi-magang', [InternshipController::class, 'store'])->name('internships.store');
    Route::get('presensi-magang/{program}', [InternshipController::class, 'show'])->name('internships.show');
    Route::get('presensi-magang/{program}/rekap/export', [InternshipController::class, 'exportProgramRecap'])->name('internships.recap.export');
    Route::put('presensi-magang/{program}', [InternshipController::class, 'update'])->name('internships.update');
    Route::put('presensi-magang/peserta/{participant}/approve', [InternshipController::class, 'approve'])->name('internships.participants.approve');
    Route::put('presensi-magang/peserta/{participant}/reject', [InternshipController::class, 'reject'])->name('internships.participants.reject');
    Route::put('presensi-magang/peserta/{participant}', [InternshipController::class, 'updateParticipant'])->name('internships.participants.update');
    Route::put('presensi-magang/peserta/{participant}/password', [InternshipController::class, 'resetParticipantPassword'])->name('internships.participants.password');
    Route::put('presensi-magang/peserta/{participant}/predikat', [InternshipController::class, 'updateParticipantGrade'])->name('internships.participants.grade');
    Route::put('presensi-magang/{program}/sertifikat', [InternshipController::class, 'updateCertificateSettings'])->name('internships.certificates.settings');
    Route::post('presensi-magang/{program}/sertifikat/generate', [InternshipController::class, 'generateCertificates'])->name('internships.certificates.generate');
    Route::post('presensi-magang/{program}/sertifikat/kirim', [InternshipController::class, 'sendReadyCertificates'])->name('internships.certificates.send-ready');
    Route::get('presensi-magang/template-sertifikat/unduh', [InternshipController::class, 'downloadCertificateTemplate'])->name('internships.certificates.template');
    Route::get('presensi-magang/peserta/{participant}/presensi', [InternshipController::class, 'participantAttendance'])->name('internships.participants.attendance');
    Route::post('presensi-magang/peserta/{participant}/absenkan', [InternshipController::class, 'adminMarkPresent'])->name('internships.participants.mark-present');
    Route::get('presensi-magang/peserta/{participant}/presensi/export', [InternshipController::class, 'exportParticipantAttendance'])->name('internships.participants.attendance.export');
    Route::get('presensi-magang/ketidakhadiran/{attendance}/bukti', [InternshipController::class, 'downloadEvidence'])->name('internships.absences.evidence');
    Route::get('/asisten-ai', [InternalAiAssistantController::class, 'index'])->name('ai-assistant.index');
     
    // --- 02. KELOLA USER (Khusus Superadmin) ---
    Route::middleware(['can:superadmin-only'])->group(function () {
        Route::resource('users', UserController::class);
        Route::put('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::put('users/{user}/approve-type', [UserController::class, 'approveUserType'])->name('users.approve-type');
        Route::get('pengaturan/bantuan-login', [LoginHelpSettingController::class, 'edit'])->name('settings.login-help.edit');
        Route::put('pengaturan/bantuan-login', [LoginHelpSettingController::class, 'update'])->name('settings.login-help.update');
    });

    // --- PENGATURAN PROFIL UMUM ---
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('profile/address-search', [ProfileController::class, 'searchAddress'])->middleware('throttle:10,1')->name('profile.address-search');
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
    Route::get('monitoring-pengajar', [TrainingController::class, 'teacherMonitoring'])->name('teacher-monitoring.index');
    Route::get('monitoring-pengajar/export', [TrainingController::class, 'exportTeacherMonitoring'])->name('teacher-monitoring.export');
    Route::get('trainings/{training}/certificates', [TrainingCertificateController::class, 'index'])->name('training-certificates.index');
    Route::get('trainings/{training}/activity-report', [TrainingActivityReportController::class, 'index'])->name('training-activity-report.index');
    Route::put('trainings/{training}/activity-report', [TrainingActivityReportController::class, 'update'])->name('training-activity-report.update');
    Route::post('trainings/{training}/activity-report/ai-draft', [TrainingActivityReportController::class, 'generateAiDraft'])->name('training-activity-report.ai-draft');
    Route::post('trainings/{training}/activity-report/template', [TrainingActivityReportController::class, 'uploadTemplate'])->name('training-activity-report.template.upload');
    Route::delete('trainings/{training}/activity-report/template', [TrainingActivityReportController::class, 'resetTemplate'])->name('training-activity-report.template.reset');
    Route::get('trainings/{training}/activity-report/template', [TrainingActivityReportController::class, 'downloadTemplate'])->name('training-activity-report.template.download');
    Route::post('trainings/{training}/activity-report/photos', [TrainingActivityReportController::class, 'storePhotos'])->name('training-activity-report.photos.store');
    Route::put('activity-report/photos/{documentation}', [TrainingActivityReportController::class, 'updatePhoto'])->name('training-activity-report.photos.update');
    Route::delete('activity-report/photos/{documentation}', [TrainingActivityReportController::class, 'destroyPhoto'])->name('training-activity-report.photos.destroy');
    Route::get('activity-report/photos/{documentation}', [TrainingActivityReportController::class, 'viewPhoto'])->name('training-activity-report.photos.view');
    Route::post('trainings/{training}/activity-report/generate', [TrainingActivityReportController::class, 'generate'])->name('training-activity-report.generate');
    Route::get('activity-report/versions/{version}/{format}', [TrainingActivityReportController::class, 'downloadVersion'])->name('training-activity-report.versions.download');
    Route::post('trainings/{training}/certificates/setting', [TrainingCertificateController::class, 'storeSetting'])->name('training-certificates.setting');
    Route::get('trainings/{training}/certificates/preview', [TrainingCertificateController::class, 'preview'])->name('training-certificates.preview');
    Route::post('trainings/{training}/certificates/generate', [TrainingCertificateController::class, 'generate'])->name('training-certificates.generate');
    Route::post('trainings/{training}/certificates/send-ready', [TrainingCertificateController::class, 'sendReady'])->name('training-certificates.send-ready');
    Route::post('participant-certificates/{certificate}/send', [TrainingCertificateController::class, 'send'])->name('training-certificates.send');
    Route::get('participant-certificates/{certificate}/download', [TrainingCertificateController::class, 'downloadFinal'])->name('participant-certificates.download');
    Route::get('jadwal-pengajar', [TrainingController::class, 'teacherSchedulesGlobal'])->name('teacher-schedules.index');
    Route::get('jadwal-pengajar/export', [TrainingController::class, 'exportTeacherSchedulesGlobal'])->name('teacher-schedules.export');

    // Kelola Dokumen & Folder
    Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('documents/folder', [DocumentController::class, 'createFolder'])->name('documents.folder.create');
    Route::get('documents/archives', [DocumentController::class, 'archives'])->name('documents.archives');
    Route::put('documents/folder/{folder}/archive', [DocumentController::class, 'archiveFolder'])->name('documents.folder.archive');
    Route::put('documents/folder/{folder}/restore', [DocumentController::class, 'restoreFolder'])->name('documents.folder.restore');
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
    Route::get('questions/export', [QuestionController::class, 'exportAll'])->name('questions.export');
    Route::get('questions/download-template', [QuestionController::class, 'downloadTemplate'])->name('questions.template');
    Route::post('questions/import', [QuestionController::class, 'import'])->name('questions.import');
    Route::delete('questions/delete-bundle', [QuestionController::class, 'destroyBundle'])->name('questions.destroy-bundle');
    Route::delete('questions/delete-selected', [QuestionController::class, 'destroySelected'])->name('questions.destroy-selected');
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
    Route::get('trainings/{id}/evaluasi-l12/dashboard', [EvaluationLevel12ReportController::class, 'dashboard'])->name('evall12.dashboard');
    Route::post('trainings/{id}/evaluasi-l12/dashboard/ai', [EvaluationLevel12ReportController::class, 'generateAiAnalysis'])->name('evall12.dashboard.ai');
    Route::get('trainings/{id}/participants', [TrainingController::class, 'showParticipants'])->name('trainings.participants');
    Route::get('trainings/{id}/manage', [TrainingController::class, 'manage'])->name('trainings.manage');
    Route::get('trainings/{id}/execution-notes', [TrainingController::class, 'executionNotes'])->name('trainings.execution-notes.index');
    Route::post('trainings/{id}/execution-notes', [TrainingController::class, 'storeExecutionNote'])->name('trainings.execution-notes.store');
    Route::post('trainings/{id}/organizer-documents', [TrainingController::class, 'uploadOrganizerDocument'])->name('trainings.organizer-documents.store');
    Route::post('trainings/{id}/participants/import', [TrainingController::class, 'importParticipants'])->name('participants.import');
    Route::get('trainings/{id}/participants/import-result', [TrainingController::class, 'downloadParticipantImportResult'])->name('participants.import-result');
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
    Route::get('trainings/{id}/evaluasi-l1/rangkuman-penyelenggara', [EvaluationLevel1Controller::class, 'organizerTextSummary'])->name('evall1.organizer-summary');
    Route::put('trainings/{id}/evaluasi-l1/rangkuman-penyelenggara', [EvaluationLevel1Controller::class, 'storeOrganizerTextSummary'])->name('evall1.organizer-summary.store');
    Route::post('trainings/{id}/evaluasi-l1/rangkuman-penyelenggara/ai', [EvaluationLevel1Controller::class, 'generateOrganizerTextSummary'])->name('evall1.organizer-summary.ai');
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
    Route::get('monitoring-indicators/export', [MonitoringIndicatorController::class, 'export'])->name('indicators.export');
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
    Route::get('trainings/{id}/evaluasi-l34/dashboard', [EvaluationLevel34Controller::class, 'dashboard'])->name('evall34.dashboard');
    Route::post('trainings/{id}/evaluasi-l34/dashboard/ai', [EvaluationLevel34Controller::class, 'generateAiAnalysis'])->name('evall34.dashboard.ai');
    Route::get('trainings/{id}/evaluasi-l34', [EvaluationLevel34Controller::class, 'index'])->name('evall34.index'); // Detail L34

    // Kelola Alumni
    Route::get('alumni-statistics/export', [AlumniController::class, 'exportExcel'])->name('alumni.export');
    Route::get('alumni', [AlumniController::class, 'index'])->name('alumni.index');
        
    // --- KELOLA TICKETING (Admin Bidang & Superadmin) ---
    Route::middleware(['can:ticketing-access'])->prefix('ticketing')->group(function () {
        Route::get('/', [TicketingController::class, 'dashboard'])->name('ticketing.dashboard');
        Route::post('/availability', [TicketingController::class, 'updateAvailability'])->name('ticketing.availability');
        Route::get('/tiket', [TicketingController::class, 'index'])->name('ticketing.index');
        Route::get('/tiket/{ticket}', [TicketingController::class, 'show'])->name('ticketing.show');
        Route::put('/tiket/{ticket}/status', [TicketingController::class, 'updateStatus'])->name('ticketing.update-status');
        Route::post('/tiket/{ticket}/reply', [TicketingController::class, 'reply'])->name('ticketing.reply');
        Route::put('/tiket/{ticket}/assign', [TicketingController::class, 'assign'])->name('ticketing.assign');
        Route::put('/tiket/{ticket}/transfer', [TicketingController::class, 'transfer'])->name('ticketing.transfer');
        Route::get('/export/excel', [TicketingController::class, 'exportExcel'])->name('ticketing.export.excel');
        Route::get('/export/pdf', [TicketingController::class, 'exportPdf'])->name('ticketing.export.pdf');
        // Master Data (Superadmin only)
        Route::middleware(['can:superadmin-only'])->group(function () {
            Route::get('/master/layanan', [TicketingMasterController::class, 'services'])->name('ticketing.master.services');
            Route::post('/master/layanan', [TicketingMasterController::class, 'storeService'])->name('ticketing.master.services.store');
            Route::put('/master/layanan/{service}', [TicketingMasterController::class, 'updateService'])->name('ticketing.master.services.update');
            Route::delete('/master/layanan/{service}', [TicketingMasterController::class, 'destroyService'])->name('ticketing.master.services.destroy');
            Route::get('/master/kategori', [TicketingMasterController::class, 'categories'])->name('ticketing.master.categories');
            Route::post('/master/kategori', [TicketingMasterController::class, 'storeCategory'])->name('ticketing.master.categories.store');
            Route::put('/master/kategori/{category}', [TicketingMasterController::class, 'updateCategory'])->name('ticketing.master.categories.update');
            Route::delete('/master/kategori/{category}', [TicketingMasterController::class, 'destroyCategory'])->name('ticketing.master.categories.destroy');
            Route::get('/master/bidang', [TicketingMasterController::class, 'bidang'])->name('ticketing.master.bidang');
            Route::post('/master/bidang', [TicketingMasterController::class, 'storeBidang'])->name('ticketing.master.bidang.store');
            Route::put('/master/bidang/{bidang}', [TicketingMasterController::class, 'updateBidang'])->name('ticketing.master.bidang.update');
            Route::delete('/master/bidang/{bidang}', [TicketingMasterController::class, 'destroyBidang'])->name('ticketing.master.bidang.destroy');
            Route::get('/master/routing', [TicketingMasterController::class, 'routing'])->name('ticketing.master.routing');
            Route::post('/master/routing', [TicketingMasterController::class, 'storeRouting'])->name('ticketing.master.routing.store');
            Route::delete('/master/routing/{rule}', [TicketingMasterController::class, 'destroyRouting'])->name('ticketing.master.routing.destroy');
            Route::get('/master/sla', [TicketingMasterController::class, 'sla'])->name('ticketing.master.sla');
            Route::post('/master/sla', [TicketingMasterController::class, 'storeSla'])->name('ticketing.master.sla.store');
            Route::delete('/master/sla/{sla}', [TicketingMasterController::class, 'destroySla'])->name('ticketing.master.sla.destroy');
        });
    });

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

    // --- ADUAN SAYA (Daftar tiket milik pengguna yang login) ---
    Route::get('aduan-saya', [HotlineController::class, 'myTickets'])->name('hotline.my');

});
