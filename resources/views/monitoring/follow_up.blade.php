@extends('layouts.master')

@section('title', 'Rekomendasi Monitoring')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><span class="text-muted fw-light">Monitoring /</span> Rekomendasi & Tindak Lanjut</h4>
            <small class="text-muted">Temuan indikator TIDAK harus diselesaikan oleh bidang tujuan dan diverifikasi.</small>
        </div>
        @if(request('training_id'))
            <a href="{{ route('trainings.manage', request('training_id')) }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali ke Pengelolaan
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible border-0 shadow-sm">
            <i class="bx bx-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3 mb-4">
        @foreach([
            ['Total', $summary['total'], 'primary', 'bx-list-check'],
            ['Perlu Dikerjakan', $summary['open'], 'warning', 'bx-loader-circle'],
            ['Menunggu Verifikasi', $summary['submitted'], 'info', 'bx-time-five'],
            ['Selesai Terverifikasi', $summary['verified'], 'success', 'bx-check-shield'],
            ['Terlambat', $summary['overdue'], 'danger', 'bx-alarm-exclamation'],
        ] as [$label, $value, $color, $icon])
            <div class="col-6 col-lg">
                <div class="card border shadow-none h-100">
                    <div class="card-body py-3">
                        <i class="bx {{ $icon }} text-{{ $color }} fs-4"></i>
                        <div class="h4 fw-bold mb-0">{{ $value }}</div>
                        <small class="text-muted">{{ $label }}</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border shadow-none mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                @if(request('training_id'))
                    <input type="hidden" name="training_id" value="{{ request('training_id') }}">
                @endif
                <div class="col-md-4">
                    <label class="form-label small fw-bold mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach(['open' => 'Open', 'in_progress' => 'Sedang Dikerjakan', 'submitted' => 'Menunggu Verifikasi', 'verified' => 'Selesai', 'rejected' => 'Perlu Revisi'] as $value => $label)
                            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i>Terapkan</button>
                    <a href="{{ route('followup.index', array_filter(['training_id' => request('training_id')])) }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Temuan & Rekomendasi</th>
                        <th>Tujuan / Tenggat</th>
                        <th>Status</th>
                        <th>Respons & Bukti</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($followUps as $fu)
                        @php
                            $isOverdue = $fu->workflow_status !== 'verified' && $fu->due_date && $fu->due_date->isPast();
                            $statusMap = [
                                'open' => ['warning', 'Open'],
                                'in_progress' => ['primary', 'Sedang Dikerjakan'],
                                'submitted' => ['info', 'Menunggu Verifikasi'],
                                'verified' => ['success', 'Selesai Terverifikasi'],
                                'rejected' => ['danger', 'Perlu Revisi'],
                            ];
                            [$statusColor, $statusLabel] = $statusMap[$fu->workflow_status] ?? ['secondary', ucfirst($fu->workflow_status)];
                            $canResolve = Auth::user()->role === 'superadmin' || (Auth::user()->role === 'admin_bidang' && Auth::user()->bidang === $fu->follow_up_target);
                            $canVerify = Auth::user()->role === 'superadmin' || (
                                Auth::user()->role === 'admin_bidang' &&
                                Auth::user()->bidang === $fu->training?->bidang &&
                                Auth::user()->bidang !== $fu->follow_up_target
                            );
                        @endphp
                        <tr>
                            <td style="min-width:320px;max-width:440px">
                                <div class="small text-primary fw-bold">{{ $fu->training?->nama_pelatihan }}</div>
                                <div class="small text-muted mb-1">
                                    {{ $fu->stage?->nama_tahapan ?? optional($fu->monitoring_date)->translatedFormat('d F Y') ?? 'Pelatihan' }}
                                    · {{ $fu->category }}
                                </div>
                                <div class="fw-bold text-wrap mb-2">{{ $fu->question?->question_text ?? 'Indikator tidak ditemukan' }}</div>
                                <div class="small text-wrap"><strong>Temuan:</strong> {{ $fu->notes ?: '-' }}</div>
                                <div class="small text-wrap mt-1 text-primary"><strong>Rekomendasi:</strong> {{ $fu->recommendation ?: '-' }}</div>
                            </td>
                            <td style="min-width:180px">
                                <div class="fw-bold">{{ $fu->follow_up_target ?: '-' }}</div>
                                <span class="badge bg-label-{{ in_array($fu->priority, ['tinggi','kritis']) ? 'danger' : 'warning' }}">
                                    {{ strtoupper($fu->priority ?? 'sedang') }}
                                </span>
                                <div class="small mt-2 {{ $isOverdue ? 'text-danger fw-bold' : 'text-muted' }}">
                                    <i class="bx bx-calendar me-1"></i>{{ optional($fu->due_date)->translatedFormat('d F Y') ?? 'Tanpa tenggat' }}
                                    @if($isOverdue)<br>TERLAMBAT@endif
                                </div>
                            </td>
                            <td style="min-width:150px">
                                <span class="badge bg-label-{{ $statusColor }}">{{ $statusLabel }}</span>
                                @if($fu->submitted_at)
                                    <div class="small text-muted mt-2">Diajukan {{ $fu->submitted_at->translatedFormat('d M Y H:i') }}</div>
                                @endif
                                @if($fu->verified_at)
                                    <div class="small text-muted">Diverifikasi {{ $fu->verified_at->translatedFormat('d M Y H:i') }}</div>
                                @endif
                            </td>
                            <td style="min-width:230px;max-width:320px">
                                <div class="small text-wrap">{{ $fu->resolution_notes ?: 'Belum ada respons bidang.' }}</div>
                                @if($fu->evidence_file)
                                    <a href="{{ asset('storage/'.$fu->evidence_file) }}" target="_blank" class="btn btn-xs btn-outline-info mt-2">
                                        <i class="bx bx-file me-1"></i>Lihat Evidence
                                    </a>
                                @endif
                                @if($fu->verification_notes)
                                    <div class="small text-wrap mt-2 p-2 bg-light rounded"><strong>Verifikasi:</strong> {{ $fu->verification_notes }}</div>
                                @endif
                            </td>
                            <td class="text-end" style="min-width:150px">
                                @if($canResolve && in_array($fu->workflow_status, ['open', 'in_progress', 'rejected']))
                                    <button class="btn btn-sm btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#resolve-{{ $fu->id }}">
                                        <i class="bx bx-upload me-1"></i>Ajukan Bukti
                                    </button>
                                @endif
                                @if($canVerify && $fu->workflow_status === 'submitted')
                                    <button class="btn btn-sm btn-success mb-1" data-bs-toggle="modal" data-bs-target="#verify-{{ $fu->id }}">
                                        <i class="bx bx-check-shield me-1"></i>Verifikasi
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">Tidak ada rekomendasi monitoring pada filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($followUps as $fu)
    <div class="modal fade" id="resolve-{{ $fu->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('followup.resolve', $fu->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title">Ajukan Tindak Lanjut</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    @if($fu->workflow_status === 'rejected' && $fu->verification_notes)
                        <div class="alert alert-danger"><strong>Alasan revisi:</strong><br>{{ $fu->verification_notes }}</div>
                    @endif
                    <label class="form-label fw-bold">Tindakan Perbaikan yang Dilaksanakan</label>
                    <textarea name="resolution_notes" class="form-control mb-3" rows="5" required>{{ $fu->resolution_notes }}</textarea>
                    <label class="form-label fw-bold">Evidence PDF</label>
                    <input type="file" name="evidence_file" class="form-control" accept="application/pdf" required>
                    <small class="text-muted">Maksimal 10 MB. Status berubah menjadi Menunggu Verifikasi.</small>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Ajukan Verifikasi</button></div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="verify-{{ $fu->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('followup.verify', $fu->id) }}" method="POST" class="modal-content">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title">Verifikasi Tindak Lanjut</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label fw-bold">Catatan Verifikasi</label>
                    <textarea name="verification_notes" class="form-control mb-3" rows="4" required placeholder="Jelaskan hasil pemeriksaan bukti dan tindakan perbaikan..."></textarea>
                    <label class="form-label fw-bold">Keputusan</label>
                    <select name="decision" class="form-select" required>
                        <option value="approve">Setujui dan nyatakan selesai</option>
                        <option value="reject">Kembalikan untuk revisi</option>
                    </select>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-success">Simpan Verifikasi</button></div>
            </form>
        </div>
    </div>
@endforeach
@endsection
