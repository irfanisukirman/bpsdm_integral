@extends('layouts.master')

@section('title', 'Kontrol Evaluasi Pasca')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Evaluasi /</span> Kontroling Sebar Kuisioner L3 & L4
    </h4>

    <div class="card">
        <h5 class="card-header border-bottom">Monitoring Distribusi Instrumen Dampak</h5>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Pelatihan</th>
                        <th>Jadwal Sebar</th>
                        <th>Sisa Waktu</th>
                        <th class="text-center">Aksi Distribusi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach($trainings as $t)
                    @php
                        $sisa = $t->sisa_hari_sebar;
                        $gatewayLink = route('public.l34.gateway', $t->id);
                        
                        // Penentuan Warna & Status
                        if($sisa <= 0) {
                            $colorClass = 'bg-label-danger';
                            $statusText = 'WAKTUNYA SEBAR';
                        } elseif($sisa <= 7) {
                            $colorClass = 'bg-label-warning';
                            $statusText = 'PERSIAPAN (H-'.$sisa.')';
                        } else {
                            $colorClass = 'bg-label-success';
                            $statusText = $sisa . ' HARI LAGI';
                        }
                    @endphp
                    <tr>
                        <td>
                            <span class="fw-bold text-dark">{{ $t->nama_pelatihan }}</span><br>
                            <small class="text-muted">Target: {{ str_contains($t->bidang, 'Manajerial') ? '1 Tahun' : '4 Bulan' }} pasca pelatihan</small>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{ $t->tgl_sebar_l34->translatedFormat('d F Y') }}</span>
                                <small class="text-muted">Selesai: {{ \Carbon\Carbon::parse($t->tgl_selesai)->format('d/m/y') }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $colorClass }} fw-bold">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($sisa <= 7)
                                {{-- Tombol Terbuka 1 Minggu Sebelumnya --}}
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary" onclick="copyToClipboard('{{ $gatewayLink }}', this)">
                                        <i class="bx bx-copy-alt me-1"></i> Copy Link Gateway
                                    </button>
                                    <a href="{{ route('evall34.index', $t->id) }}" class="btn btn-sm btn-outline-primary" title="Lihat Progres">
                                        <i class="bx bx-show"></i>
                                    </a>
                                </div>
                            @else
                                {{-- Masih Terkunci --}}
                                <button class="btn btn-sm btn-outline-secondary" disabled>
                                    <i class="bx bx-lock-alt me-1"></i> Terkunci s.d H-7
                                </button>
                            @endif
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
 * Fungsi Copy Link dengan Feedback Visual pada Tombol
 */
function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bx bx-check me-1"></i> Berhasil Disalin';
        btn.classList.replace('btn-primary', 'btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.replace('btn-success', 'btn-primary');
        }, 2000);
    }).catch(err => {
        alert('Gagal menyalin link: ' + err);
    });
}
</script>
@endpush