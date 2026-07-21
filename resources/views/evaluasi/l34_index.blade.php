@extends('layouts.master')
@section('title', 'Evaluasi Dampak L3 & L4')
@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Evaluasi /</span> Level 3 & 4: Perilaku & Dampak</h4>

<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Status Penilaian 360°: {{ $training->nama_pelatihan }}</h5>
        <div class="dropdown">
            <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">Bagikan Link Survei</button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="javascript:void(0)" onclick="copyToClipboard('{{ route('public.l34.gateway', ['training_id' => $training->id]) }}')">
                    <i class="bx bx-copy me-1"></i> Salin Link Survei Pasca Pelatihan
                </a>
            </div>
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Alumni (Peserta)</th>
                    <th class="text-center">Mandiri (L3)</th>
                    <th class="text-center">Atasan (L3)</th>
                    <th class="text-center">Rekan (L3)</th>
                    <th>Skor Dampak (L4)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($participants as $p)
                <tr>
                    <td>
                        <strong>{{ $p->name }}</strong><br>
                        <small class="text-muted">{{ $p->instansi }}</small>
                    </td>
                    <td class="text-center">
                        {!! $p->hasVoted('mandiri') ? '<span class="badge bg-label-success">Selesai</span>' : '<span class="badge bg-label-secondary">Belum</span>' !!}
                    </td>
                    <td class="text-center">
                        {!! $p->hasVoted('atasan') ? '<span class="badge bg-label-success">Selesai</span>' : '<span class="badge bg-label-secondary">Belum</span>' !!}
                    </td>
                    <td class="text-center">
                        {!! $p->hasVoted('rekan') ? '<span class="badge bg-label-success">Selesai</span>' : '<span class="badge bg-label-secondary">Belum</span>' !!}
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="progress w-100 me-2" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: {{ $p->avg_l4 ?? 0 }}%"></div>
                            </div>
                            <span>{{ number_format($p->avg_l4 ?? 0, 1) }}</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
    alert('Link berhasil disalin ke clipboard!');
}
</script>
@endsection