@extends('layouts.master')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Kehadiran /</span> Detail Harian: {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</h4>
    <a href="{{ route('attendance.pdf.daily', ['id' => $training->id, 'date' => $date]) }}" class="btn btn-danger">
        <i class="bx bxs-file-pdf me-1"></i> Download PDF Hari Ini
    </a>
    <a href="{{ route('trainings.manage', $training->id) }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali ke Pengelolaan
            </a>
</div>

<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>NIP / NIK</th>
                    <th>Nama Lengkap</th>
                    <th>Instansi</th>
                    <th>Status</th>
                    <th>Jam Check-in</th>
                </tr>
            </thead>
            <tbody>
                @foreach($participants as $p)
                @php 
                    $att = \App\Models\Attendance::whereIn('schedule_id', $scheduleIds)
                           ->where('participant_id', $p->id)->first(); 
                @endphp
                <tr>
                    <td><code>{{ $p->nip_nik }}</code></td>
                    <td><strong>{{ $p->name }}</strong></td>
                    <td>{{ $p->instansi }}</td>
                    <td>
                        @if($att)
                            <span class="fw-bold">{{ \Carbon\Carbon::parse($att->check_in_at)->format('H:i:s') }}</span>
                            <small class="text-primary fw-bold">{{ $att->timezone_label }}</small>
                        @else
                            -
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