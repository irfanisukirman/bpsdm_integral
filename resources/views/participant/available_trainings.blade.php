@extends('layouts.master')

@section('title', 'Pelatihan Aktif & Evaluasi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
        <div class="animate__animated animate__fadeInLeft">
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Portal Peserta /</span> Pelatihan Aktif
            </h4>
            <p class="text-muted mb-0">Kelola pendaftaran dan tuntaskan kewajiban evaluasi Anda.</p>
        </div>
        
        <div class="animate__animated animate__fadeInRight">
            <button class="btn btn-primary btn-lg shadow-md px-4 pulse-button" data-bs-toggle="modal" data-bs-target="#modalJoinGlobal">
                <i class="bx bx-plus-circle me-2 fs-4"></i> IKUTI PELATIHAN BARU
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 animate__animated animate__fadeIn">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
    @endif

    <div class="row">
        @forelse($myTrainings as $t)
            @php
                // 1. Ambil data peserta (user login)
                $p = $t->participants->where('user_id', auth()->id())->first();
                
                // 2. Deteksi Status Approval
                $regStatus = $p->registration_status ?? 'pending';
                $isApproved = ($regStatus == 'approved');
                $isPending = ($regStatus == 'pending');
                $isRejected = ($regStatus == 'rejected');

                // 3. Deteksi Waktu Selesai
                $isExpired = \Carbon\Carbon::parse($t->tgl_selesai)->isPast();
                
                // 4. Cek Kelengkapan (Hanya jika sudah approved)
                $hasDocs = $isApproved && ($p->biodata_file_id && $p->surat_tugas_file_id && $p->pas_foto_file_id);
                $hasL1 = $isApproved && $p->hasFilledL1Any();
                $hasL2 = $isApproved && \App\Models\EvaluationResultL2::where('participant_id', $p->id)->exists();
                $hasL34 = $isApproved && $p->hasFilledL34Mandiri();
            @endphp
            
            <div class="col-md-6 col-lg-4 mb-4">
                {{-- Card UI: Berubah warna border sesuai status --}}
                <div class="card h-100 shadow-sm border-0 transition-all 
                    {{ $isPending ? 'border-top border-warning border-3' : ($isExpired ? 'border-top border-danger border-3' : 'border-top border-primary border-3') }} 
                    hover-shadow-lg">
                    
                    <div class="card-header pb-2 d-flex justify-content-between align-items-center">
                        <div>
                            @if($isPending)
                                <span class="badge bg-label-warning animate__animated animate__flash animate__infinite">MENUNGGU APPROVAL</span>
                            @elseif($isRejected)
                                <span class="badge bg-label-danger">DITOLAK</span>
                            @elseif($isExpired)
                                <span class="badge bg-label-danger"><i class="bx bx-error-circle me-1"></i>PELATIHAN SELESAI</span>
                            @else
                                <span class="badge bg-label-success"><i class="bx bx-play-circle me-1"></i>SEDANG BERJALAN</span>
                            @endif
                        </div>
                        <small class="text-muted fw-bold">ANGKATAN {{ $t->angkatan }}</small>
                    </div>

                    <div class="card-body">
                        <h5 class="card-title fw-extrabold text-dark mb-3" style="min-height: 50px;">{{ $t->nama_pelatihan }}</h5>
                        
                        {{-- Logika Tampilan Jadwal --}}
                        <div class="mb-4 p-3 rounded-3 {{ $isExpired ? 'bg-label-danger' : 'bg-label-primary' }} text-center">
                            @if($isExpired)
                                <h6 class="mb-0 fw-bold text-danger">Masa Pelaksanaan Berakhir</h6>
                                <small class="text-danger opacity-75">Tuntaskan evaluasi agar masuk riwayat</small>
                            @else
                                <small class="d-block text-uppercase fw-bold mb-1" style="font-size: 10px;">Jadwal Pelaksanaan</small>
                                <span class="fw-bold text-primary">
                                    {{ \Carbon\Carbon::parse($t->tgl_mulai)->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($t->tgl_selesai)->translatedFormat('d M Y') }}
                                </span>
                            @endif
                        </div>

                        {{-- SEKSI CHECKLIST (Hanya untuk yang sudah Approved) --}}
                        @if($isApproved)
                            <div class="task-section p-3 border rounded-3 bg-white">
                                <h6 class="small fw-bold text-muted mb-3 text-uppercase border-bottom pb-2">Checklist Kewajiban:</h6>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small>1. Kelengkapan Berkas</small>
                                    <i class="bx {{ $hasDocs ? 'bxs-check-circle text-success' : 'bx-x-circle text-danger animate__animated animate__heartBeat animate__infinite' }} fs-5"></i>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small>2. Evaluasi Level 1</small>
                                    <i class="bx {{ $hasL1 ? 'bxs-check-circle text-success' : 'bx-x-circle text-danger animate__animated animate__heartBeat animate__infinite' }} fs-5"></i>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small>3. Evaluasi Level 2</small>
                                    <i class="bx {{ $hasL2 ? 'bxs-check-circle text-success' : 'bx-x-circle text-danger' }} fs-5"></i>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small>4. Evaluasi Dampak (L3&4)</small>
                                    <i class="bx {{ $hasL34 ? 'bxs-check-circle text-success' : 'bx-x-circle text-danger animate__animated animate__heartBeat animate__infinite' }} fs-5"></i>
                                </div>
                            </div>
                        @elseif($isPending)
                            <div class="alert bg-label-secondary border-0 p-3 text-center">
                                <i class="bx bx-loader-circle bx-spin me-1"></i> <small>Menunggu verifikasi NIP oleh Admin...</small>
                            </div>
                        @else
                            <div class="alert bg-label-danger border-0 p-3 text-center">
                                <i class="bx bx-x-circle me-1"></i> <small>Maaf, pendaftaran Anda ditolak.</small>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer border-top bg-transparent pt-3 pb-3">
                        @if($isApproved)
                            <a href="{{ route('participant.training.show', $t->id) }}" 
                               class="btn {{ $isExpired ? 'btn-danger' : 'btn-primary' }} w-100 shadow-sm fw-bold">
                                {{ $isExpired ? 'LENGKAPI DATA SEKARANG' : 'BUKA DASHBOARD KELAS' }}
                                <i class="bx bx-right-arrow-alt ms-1"></i>
                            </a>
                        @else
                            <button class="btn btn-secondary w-100 opacity-50 shadow-none" disabled>
                                <i class="bx bx-lock-alt me-1"></i> DASHBOARD TERKUNCI
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 glass-effect">
                <i class="bx bx-folder-open text-muted mb-3" style="font-size: 5rem;"></i>
                <h4 class="text-muted">Tidak ada pelatihan aktif.</h4>
                <p class="text-muted">Gunakan kode undangan dari panitia untuk bergabung.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- MODAL JOIN GLOBAL --}}
<div class="modal fade" id="modalJoinGlobal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form action="{{ route('participant.training.join_by_code') }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-primary text-white border-0 py-4">
                <h5 class="modal-title text-white fw-bold"><i class="bx bx-key me-2"></i>Gabung Pelatihan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="text-muted small">Masukkan 6 digit <b>Kode Undangan</b>:</p>
                <input type="text" name="invitation_code" class="form-control form-control-lg text-center fw-bold border-primary" 
                    placeholder="------" maxlength="6" style="letter-spacing: 5px; text-transform: uppercase;" required>
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
    .hover-shadow-lg:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(105, 108, 255, 0.15) !important; }
    .bg-label-danger { background-color: #ffebe6 !important; }
    .bg-label-primary { background-color: #e7e7ff !important; }
    .pulse-button { animation: pulse-blue 2s infinite; }
    @keyframes pulse-blue { 0% { box-shadow: 0 0 0 0 rgba(105, 108, 255, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(105, 108, 255, 0); } 100% { box-shadow: 0 0 0 0 rgba(105, 108, 255, 0); } }
    .glass-effect { background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(10px); border-radius: 20px; border: 2px dashed #d9dee3; }
</style>
@endsection