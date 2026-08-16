@extends('layouts.master')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-3 mb-0">
        <span class="text-muted fw-light">Kehadiran /</span> Detail: {{ $schedule->activity }}
    </h4>
    <a href="{{ route('attendance.pdf', $schedule->id) }}" class="btn btn-danger">
        <i class="bx bxs-file-pdf me-1"></i> Download PDF Hari Ini
    </a>
    <a href="{{ route('trainings.manage', $training->id) }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali ke Pengelolaan
            </a>
</div>

<div class="card">
    <div class="card-header bg-label-dark">
        <h5 class="mb-0">Daftar Peserta - {{ \Carbon\Carbon::parse($schedule->date)->format('d F Y') }}</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>NIP / NIK</th>
                    <th>Nama Lengkap</th>
                    <th>Status</th>
                    <th>Check-in Jam</th>
                </tr>
            </thead>
            <tbody>
                @foreach($participants as $p)
                @php $att = $schedule->attendances->where('participant_id', $p->id)->first(); @endphp
                <tr>
                    <td><code>{{ $p->nip_nik }}</code></td>
                    <td><strong>{{ $p->name }}</strong></td>
                    <td>
                        @if($att)
                            @if($att->status == 'hadir') <span class="badge bg-label-success">Hadir</span>
                            @elseif($att->status == 'izin') <span class="badge bg-label-warning">Izin</span>
                            @else <span class="badge bg-label-danger">Sakit</span> @endif
                        @else
                            <span class="badge bg-label-secondary">Belum Absen</span>
                        @endif
                    </td>
                    <td>{{ $att ? \Carbon\Carbon::parse($att->check_in_at)->format('H:i:s') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection