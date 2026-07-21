@extends('layouts.master')

@section('title', 'Detail Progres Evaluasi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Evaluasi / Progres /</span> 
            {{-- Cek jika schedule ada (Narasumber) atau tidak (Penyelenggara) --}}
            @if($schedule)
                Narasumber: {{ $schedule->pic }}
            @else
                Penyelenggara Diklat
            @endif
        </h4>
        <a href="{{ route('evall1.index', $training->id) }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    @if($schedule)
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <i class="bx bx-info-circle me-1"></i> Menampilkan progres pengisian untuk materi: <strong>{{ $schedule->activity }}</strong>
    </div>
    @endif

    <div class="card">
        <h5 class="card-header">Status Pengisian Peserta ({{ count($filledParticipantIds) }}/{{ $training->jumlah_peserta }})</h5>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>NIP / NIK</th>
                        <th>Nama Lengkap</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($training->participants as $p)
                    @php $isFilled = in_array($p->id, $filledParticipantIds); @endphp
                    <tr>
                        <td><code>{{ $p->nip_nik }}</code></td>
                        <td><strong>{{ $p->name }}</strong></td>
                        <td>
                            @if($isFilled)
                                <span class="badge bg-label-success"><i class="bx bx-check me-1"></i> Sudah Mengisi</span>
                            @else
                                <span class="badge bg-label-danger"><i class="bx bx-x me-1"></i> Belum Mengisi</span>
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