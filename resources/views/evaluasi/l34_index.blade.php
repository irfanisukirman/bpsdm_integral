@extends('layouts.master')

@section('title', 'Monitoring L3 & L4')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Evaluasi /</span> Level 3 & 4: Perilaku & Dampak
        </h4>
        <div class="d-flex gap-2">
            <a href="{{ route('evaluasi.l34') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-share-alt me-1"></i> Bagikan Link Survei
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" onclick="copyLink('{{ route('public.l34.gateway', $training->id) }}', this)">
                            <i class="bx bx-copy me-2"></i> Salin Link Gateway 360°
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li class="dropdown-header small text-uppercase">Link Spesifik:</li>
                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="copyLink('{{ route('public.l34.form', [$training->id, 'mandiri']) }}', this)">Link Alumni (Mandiri)</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="copyLink('{{ route('public.l34.form', [$training->id, 'atasan']) }}', this)">Link Atasan</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="copyLink('{{ route('public.l34.form', [$training->id, 'rekan']) }}', this)">Link Rekan Kerja</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- CARD STATISTIK RINGKASAN -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-label-primary">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1 text-primary">Total Alumni</h6>
                        <h4 class="mb-0 fw-bold">{{ count($participants) }}</h4>
                    </div>
                    <div class="avatar bg-primary rounded p-2">
                        <i class="bx bx-group text-white h3 mb-0"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card bg-label-info">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div class="w-100">
                        <h6 class="mb-2 text-info">Progres Pengisian (Target 3 Penilai per Alumni)</h6>
                        <div class="progress" style="height: 12px;">
                            @php
                                $totalTarget = count($participants) * 3;
                                $currentTotal = \App\Models\EvaluationResultL34::where('training_id', $training->id)->distinct('participant_id', 'evaluator_role')->count();
                                $totalPercent = $totalTarget > 0 ? ($currentTotal / $totalTarget) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-info progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $totalPercent }}%"></div>
                        </div>
                        <small class="mt-2 d-block text-dark fw-bold">{{ round($totalPercent) }}% Respon Terkumpul</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL UTAMA -->
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="mb-0">Status Penilaian Alumni: <span class="text-primary">{{ $training->nama_pelatihan }}</span></h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr class="table-light">
                        <th>Alumni (Peserta)</th>
                        <th class="text-center">Mandiri (L3)</th>
                        <th class="text-center">Atasan (L3)</th>
                        <th class="text-center">Rekan (L3)</th>
                        <th width="200">Skor Dampak (L4)</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach($participants as $p)
                    <tr>
                        <td>
                            <div class="d-flex justify-content-start align-items-center">
                                <div class="avatar-wrapper me-3">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($p->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">{{ $p->name }}</span>
                                    <small class="text-muted" style="font-size: 11px;">{{ $p->nip_nik }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($p->hasFilledL34('mandiri'))
                                <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i> Selesai</span>
                            @else
                                <span class="badge bg-label-secondary text-muted">Belum</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($p->hasFilledL34('atasan'))
                                <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i> Selesai</span>
                            @else
                                <span class="badge bg-label-secondary text-muted">Belum</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($p->hasFilledL34('rekan'))
                                <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i> Selesai</span>
                            @else
                                <span class="badge bg-label-secondary text-muted">Belum</span>
                            @endif
                        </td>
                        <td>
                            @php $score = round($p->avg_l4, 1); @endphp
                            <div class="d-flex align-items-center">
                                <div class="progress w-100 me-3" style="height: 6px;">
                                    <div class="progress-bar {{ $score >= 80 ? 'bg-success' : ($score >= 70 ? 'bg-info' : 'bg-warning') }}" 
                                         role="progressbar" style="width: {{ $score }}%"></div>
                                </div>
                                <span class="fw-bold {{ $score >= 80 ? 'text-success' : 'text-primary' }}">{{ $score }}</span>
                            </div>
                            <small class="text-muted" style="font-size: 9px;">Rata-rata 360°</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
/**
 * Fungsi Copy Link dengan Feedback Visual
 */
function copyLink(url, element) {
    navigator.clipboard.writeText(url).then(() => {
        const originalText = element.innerHTML;
        element.innerHTML = '<i class="bx bx-check text-success me-2"></i> Tersalin!';
        
        // Kembalikan teks asli setelah 2 detik
        setTimeout(() => {
            element.innerHTML = originalText;
        }, 2000);
    }).catch(err => {
        alert('Gagal menyalin link.');
    });
}
</script>
@endpush