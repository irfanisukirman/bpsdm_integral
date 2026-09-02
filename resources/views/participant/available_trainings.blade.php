@extends('layouts.master')

@section('title', 'Pelatihan Aktif & Evaluasi')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <span class="text-muted fw-light">Portal Peserta /</span> Pelatihan Aktif
                </h4>
                <p class="text-muted mb-0">Pantau progres dan tuntaskan kewajiban evaluasi Anda.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4 animate__animated animate__fadeIn">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm mb-4 animate__animated animate__shakeX">
                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
            </div>
        @endif

        <div class="row justify-content-center g-4">
            @if($myTrainings->isNotEmpty())
                {{-- KONDISI: JIKA ADA PELATIHAN (DAFTAR CARD) --}}
                @foreach($myTrainings as $t)
                    @php
                        $p = $t->participants->where('user_id', auth()->id())->first();
                        $regStatus = $p->registration_status ?? 'pending';
                        $isApproved = $regStatus == 'approved';
                        $isPending = $regStatus == 'pending';
                        $isRejected = $regStatus == 'rejected';
                        $isExpired = \Carbon\Carbon::parse($t->tgl_selesai)->isPast();

                        $hasDocs = $isApproved && ($p->biodata_file_id && $p->surat_tugas_file_id && $p->pas_foto_file_id);
                        $hasL1 = $isApproved && $p->hasCompletedAllL1();
                        $hasL2 = $isApproved && \App\Models\EvaluationResultL2::where('participant_id', $p->id)->exists();
                        $hasL34 = $isApproved && $p->hasFilledL34Mandiri();
                        $isCoreComplete = $isApproved && $p->is_core_training_complete;
                        $isPostDue = $isApproved && $p->is_post_evaluation_due;
                        $needsPostEvaluation = $isCoreComplete && $isPostDue && !$hasL34;
                    @endphp

                    <div class="col-12">
                        <div class="card h-100 shadow-sm border-0 transition-all 
                            {{ $isPending ? 'border-top border-warning border-3' : ($needsPostEvaluation ? 'border-top border-warning border-3' : ($isExpired ? 'border-top border-danger border-3' : 'border-top border-primary border-3')) }}
                            hover-shadow-lg">

                            <div class="card-header pb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    @if ($isPending)
                                        <span class="badge bg-label-warning">MENUNGGU APPROVAL</span>
                                    @elseif($isRejected)
                                        <span class="badge bg-label-danger">DITOLAK</span>
                                    @elseif($needsPostEvaluation)
                                        <span class="badge bg-warning"><i class="bx bx-bell me-1"></i>EVALUASI PASCA TERSEDIA</span>
                                    @elseif($isExpired)
                                        <span class="badge bg-label-danger"><i class="bx bx-error-circle me-1"></i>PELATIHAN SELESAI</span>
                                    @else
                                        <span class="badge bg-label-success"><i class="bx bx-play-circle me-1"></i>SEDANG BERJALAN</span>
                                    @endif
                                </div>
                                <small class="text-muted fw-bold">ANGKATAN {{ $t->angkatan }}</small>
                            </div>

                            <div class="card-body">
                                <div class="training-landscape">
                                <div class="training-main-info">
                                    <small class="text-uppercase text-primary fw-bold d-block mb-2">Informasi Pelatihan</small>
                                    <h4 class="card-title fw-extrabold text-dark mb-3">{{ $t->nama_pelatihan }}</h4>

                                    <div class="training-meta">
                                        <span><i class="bx bx-map me-1"></i>{{ $t->lokasi ?? 'Lokasi belum ditentukan' }}</span>
                                        <span><i class="bx bx-book-open me-1"></i>{{ ucfirst($t->metode ?? $t->model ?? '-') }}</span>
                                        <span><i class="bx bx-time-five me-1"></i>{{ $t->jp ?? 0 }} JP</span>
                                    </div>
                                </div>

                                <div class="training-schedule-panel p-3 rounded-3 {{ $needsPostEvaluation ? 'bg-label-warning' : ($isExpired ? 'bg-label-danger' : 'bg-label-primary') }} text-center">
                                    @if ($needsPostEvaluation)
                                        <h6 class="mb-1 fw-bold text-warning"><i class="bx bx-time-five me-1"></i>Waktunya Evaluasi Pascapelatihan</h6>
                                        <small>Evaluasi dampak L3 & L4 dibuka sejak {{ $t->tgl_sebar_l34->translatedFormat('d F Y') }}.</small>
                                    @elseif ($isExpired)
                                        <h6 class="mb-0 fw-bold text-danger">Masa Pelaksanaan Berakhir</h6>
                                        <small class="text-danger opacity-75">Lengkapi berkas dan seluruh evaluasi Level 1.</small>
                                    @else
                                        <small class="d-block text-uppercase fw-bold mb-1" style="font-size: 10px;">Jadwal Pelaksanaan</small>
                                        <span class="fw-bold text-primary">
                                            {{ \Carbon\Carbon::parse($t->tgl_mulai)->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($t->tgl_selesai)->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                </div>

                                @if ($isApproved)
                                    <div class="task-section p-3 border rounded-3 bg-white">
                                        <h6 class="small fw-bold text-muted mb-3 text-uppercase border-bottom pb-2">Checklist Kewajiban:</h6>
                                        <div class="task-grid">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small>1. Kelengkapan Berkas</small>
                                            <i class="bx {{ $hasDocs ? 'bxs-check-circle text-success' : 'bx-x-circle text-danger' }} fs-5"></i>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small>2. Evaluasi Level 1</small>
                                            <i class="bx {{ $hasL1 ? 'bxs-check-circle text-success' : 'bx-x-circle text-danger' }} fs-5"></i>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small>3. Evaluasi Level 2</small>
                                            <i class="bx {{ $hasL2 ? 'bxs-check-circle text-success' : 'bx-x-circle text-danger' }} fs-5"></i>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small>4. Evaluasi Dampak (L3&4)</small>
                                            @if($hasL34)
                                                <i class="bx bxs-check-circle text-success fs-5"></i>
                                            @elseif($isPostDue)
                                                <span class="badge bg-label-danger">Wajib diisi</span>
                                            @else
                                                <span class="badge bg-label-secondary">Belum waktunya</span>
                                            @endif
                                        </div>
                                        </div>
                                    </div>
                                @elseif($isPending)
                                    <div class="alert bg-label-secondary border-0 p-3 text-center">
                                        <i class="bx bx-loader-circle bx-spin me-1"></i> <small>Menunggu verifikasi NIP oleh Admin...</small>
                                    </div>
                                @endif
                                </div>
                            </div>

                            <div class="card-footer border-top bg-transparent pt-3 pb-3">
                                @if ($isApproved)
                                    <div class="d-grid d-md-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary flex-md-shrink-0" data-bs-toggle="modal" data-bs-target="#scheduleModal{{ $t->id }}">
                                            <i class="bx bx-calendar-event me-1"></i> JADWAL ACARA
                                            <span class="badge bg-primary ms-1">{{ $t->schedules->count() }}</span>
                                        </button>
                                        @if($needsPostEvaluation)
                                            <a href="{{ route('public.l34.form', [$t->id, 'mandiri']) }}" class="btn btn-warning flex-grow-1 shadow-sm fw-bold">
                                                <i class="bx bx-edit me-1"></i>ISI EVALUASI PASCA SEKARANG
                                            </a>
                                        @else
                                            <a href="{{ route('participant.training.show', $t->id) }}" class="btn {{ $isExpired ? 'btn-danger' : 'btn-primary' }} flex-grow-1 shadow-sm fw-bold">
                                                {{ $isExpired ? 'LENGKAPI BERKAS & EVALUASI L1' : 'BUKA DASHBOARD KELAS' }} <i class="bx bx-right-arrow-alt ms-1"></i>
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <button class="btn btn-secondary w-100 opacity-50 shadow-none" disabled>
                                        <i class="bx bx-lock-alt me-1"></i> DASHBOARD TERKUNCI
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                {{-- KONDISI: JIKA TIDAK ADA PELATIHAN AKTIF (TAMPILAN TENGAH) --}}
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-lg border-0 text-center p-5 animate__animated animate__zoomIn" style="border-radius: 20px;">
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="avatar bg-label-primary rounded-circle mx-auto shadow-sm" style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center;">
                                    <i class="bx bx-book-open" style="font-size: 4rem;"></i>
                                </div>
                            </div>
                            <h3 class="fw-bold text-dark">Mulai Pelatihan Anda</h3>
                            <p class="text-muted px-lg-5 mb-5">
                                Anda belum terdaftar dalam pelatihan aktif manapun saat ini. 
                                Silakan masukkan <strong>Kode Undangan</strong> yang Anda terima dari panitia penyelenggara.
                            </p>
                            <button class="btn btn-primary btn-lg px-5 py-3 shadow pulse-button fw-bold" data-bs-toggle="modal" data-bs-target="#modalJoinGlobal">
                                <i class="bx bx-plus-circle me-2 fs-3"></i> IKUTI PELATIHAN BARU
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL JADWAL PER PELATIHAN --}}
    @foreach($myTrainings as $scheduleTraining)
        @php
            $scheduleParticipant = $scheduleTraining->participants->where('user_id', auth()->id())->first();
            $canViewSchedule = ($scheduleParticipant?->registration_status ?? 'pending') === 'approved';
        @endphp
        @if($canViewSchedule)
            <div class="modal fade" id="scheduleModal{{ $scheduleTraining->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header border-bottom p-4">
                            <div>
                                <span class="badge bg-label-primary mb-2">JADWAL ACARA</span>
                                <h5 class="modal-title fw-bold mb-1">{{ $scheduleTraining->nama_pelatihan }}</h5>
                                <div class="d-flex flex-wrap gap-3 text-muted small">
                                    <span><i class="bx bx-calendar me-1"></i>{{ \Carbon\Carbon::parse($scheduleTraining->tgl_mulai)->translatedFormat('d M') }}&ndash;{{ \Carbon\Carbon::parse($scheduleTraining->tgl_selesai)->translatedFormat('d M Y') }}</span>
                                    <span><i class="bx bx-list-ul me-1"></i>{{ $scheduleTraining->schedules->count() }} sesi</span>
                                    <span><i class="bx bx-time-five me-1"></i>{{ $scheduleTraining->schedules->sum('jp') }} JP</span>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body p-4 bg-light">
                            @forelse($scheduleTraining->schedules->groupBy(fn ($schedule) => (string) $schedule->date) as $date => $daySchedules)
                                <section class="schedule-modal-day bg-white border rounded-3 mb-3 overflow-hidden">
                                    <div class="px-3 py-3 border-bottom bg-label-primary d-flex justify-content-between align-items-center gap-2">
                                        <div class="fw-bold text-primary"><i class="bx bx-calendar me-1"></i>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</div>
                                        <span class="badge bg-primary">{{ $daySchedules->count() }} sesi</span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="min-width: 125px">Waktu</th>
                                                    <th style="min-width: 240px">Materi / Kegiatan</th>
                                                    <th>JP</th>
                                                    <th style="min-width: 190px">Pengajar / PIC</th>
                                                    <th style="min-width: 210px">Tempat & Akses</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($daySchedules as $schedule)
                                                    @php
                                                        $assetNames = $schedule->bookings->pluck('asset.name')->filter()->values();
                                                        $scheduleLocation = $schedule->venue_type === 'internal'
                                                            ? ($assetNames->implode(', ') ?: $scheduleTraining->lokasi)
                                                            : ($schedule->external_place ?: $scheduleTraining->lokasi);
                                                    @endphp
                                                    <tr>
                                                        <td><strong class="text-primary">{{ substr($schedule->start_time, 0, 5) }}&ndash;{{ substr($schedule->end_time, 0, 5) }}</strong><small class="text-muted d-block">WIB</small></td>
                                                        <td><span class="fw-semibold text-dark">{{ $schedule->activity }}</span></td>
                                                        <td><span class="badge bg-label-info">{{ $schedule->jp }} JP</span></td>
                                                        <td>
                                                            <span class="fw-semibold d-block">{{ $schedule->pengajar?->name ?: ($schedule->pic ?: 'Belum ditentukan') }}</span>
                                                            @if($schedule->pengajar && $schedule->pic && $schedule->pic !== $schedule->pengajar->name)
                                                                <small class="text-muted">PIC: {{ $schedule->pic }}</small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="d-block"><i class="bx bx-map text-danger me-1"></i>{{ $scheduleLocation ?: 'Belum ditentukan' }}</span>
                                                            @if($schedule->link_zoom)
                                                                <a href="{{ $schedule->link_zoom }}" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-xs mt-2"><i class="bx bx-video me-1"></i>Join Zoom</a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </section>
                            @empty
                                <div class="text-center py-5 bg-white border border-dashed rounded">
                                    <i class="bx bx-calendar-x display-5 text-muted d-block mb-2"></i>
                                    <h6 class="fw-bold mb-1">Jadwal belum tersedia</h6>
                                    <p class="text-muted small mb-0">Jadwal akan tampil setelah disusun oleh penyelenggara.</p>
                                </div>
                            @endforelse
                        </div>
                        <div class="modal-footer border-top">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bx bx-x me-1"></i>Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- MODAL JOIN GLOBAL --}}
    <div class="modal fade" id="modalJoinGlobal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <form action="{{ route('participant.training.join_by_code') }}" method="POST"
                class="modal-content border-0 shadow-lg">
                @csrf
                <div class="modal-header bg-primary text-white border-0 py-4">
                    <h5 class="modal-title text-white fw-bold"><i class="bx bx-key me-2"></i>Gabung Pelatihan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="text-muted small text-uppercase fw-bold">Kode Undangan</p>
                    <input type="text" name="invitation_code"
                        class="form-control form-control-lg text-center fw-bold border-primary" placeholder="------"
                        maxlength="6" style="letter-spacing: 5px; text-transform: uppercase;" required autofocus>
                </div>
                <div class="modal-footer border-0 p-3">
                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow">Verifikasi & Daftar</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .fw-extrabold { font-weight: 800; }
        .transition-all { transition: all 0.3s ease-in-out; }
        
        .hover-shadow-lg:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 30px rgba(105, 108, 255, 0.15) !important;
        }

        .pulse-button {
            animation: pulse-blue 2s infinite;
        }

        @keyframes pulse-blue {
            0% { box-shadow: 0 0 0 0 rgba(105, 108, 255, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(105, 108, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(105, 108, 255, 0); }
        }

        .schedule-modal-day:last-child { margin-bottom: 0 !important; }
        .schedule-modal-day th,
        .schedule-modal-day td { vertical-align: middle; }
        .task-section {
            background-color: #fcfcfd;
        }

        .training-meta {
            display: flex;
            flex-direction: column;
            gap: .7rem;
            color: #697a8d;
            font-size: .875rem;
        }

        .training-meta span {
            display: inline-flex;
            align-items: center;
        }

        .task-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem 1.5rem;
        }

        .training-landscape {
            display: grid;
            grid-template-columns: minmax(240px, 1.1fr) minmax(220px, .85fr) minmax(340px, 1.3fr);
            align-items: stretch;
            gap: 1.25rem;
        }

        .training-main-info,
        .training-schedule-panel,
        .task-section,
        .training-landscape > .alert {
            min-width: 0;
            height: 100%;
        }

        .training-schedule-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 132px;
        }

        .task-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        @media (max-width: 1199.98px) {
            .training-landscape {
                grid-template-columns: minmax(220px, 1fr) minmax(220px, 1fr);
            }
            .training-landscape .task-section,
            .training-landscape > .alert {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 575.98px) {
            .training-landscape { grid-template-columns: 1fr; }
            .training-landscape .task-section,
            .training-landscape > .alert { grid-column: auto; }
            .task-grid { grid-template-columns: 1fr; }
            .card-header { align-items: flex-start !important; gap: .75rem; }
            .card-footer .btn { white-space: normal; }
        }
        
        .bg-label-primary { background-color: #f0f0ff !important; }
        .bg-label-danger { background-color: #fff0f0 !important; }
    </style>
@endsection
