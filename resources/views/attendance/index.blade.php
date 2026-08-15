@extends('layouts.master')

@section('title', 'Kehadiran Harian')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Pelaksanaan /</span> Kehadiran Harian
        </h4>
        <div class="d-flex gap-2">
            {{-- TOMBOL EXCEL --}}
            <a href="{{ route('attendance.excel.all', $training->id) }}" class="btn btn-success">
                <i class="bx bx-spreadsheet me-1"></i> Download Excel
            </a>
            {{-- TOMBOL PDF --}}
            <a href="{{ route('attendance.pdf.all', $training->id) }}" class="btn btn-danger">
                <i class="bx bxs-file-pdf me-1"></i> Download PDF
            </a>
            <a href="{{ route('trainings.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">{{ $training->nama_pelatihan }}</h5>
            <small class="text-muted text-uppercase">Penyelenggara: {{ $training->bidang }}</small>
        </div>
        
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Hari / Tanggal</th>
                        <th>Jendela Waktu Absensi</th>
                        <th>Progres Kehadiran</th>
                        <th>Aksi & Link</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($dates as $index => $d)
                        @php
                            // Ambil ID semua sesi di tanggal ini
                            $scheduleIds = \App\Models\Schedule::where('training_id', $training->id)
                                            ->where('date', $d->date)
                                            ->pluck('id');

                            // Hitung jumlah peserta unik yang sudah absen hari ini
                            $attendedCount = \App\Models\Attendance::whereIn('schedule_id', $scheduleIds)
                                            ->distinct('participant_id')
                                            ->count();
                            
                            $percent = $totalParticipants > 0 ? round(($attendedCount / $totalParticipants) * 100) : 0;
                            
                            // Link Publik Harian
                            $publicLink = route('public.attendance.daily', ['training_id' => $training->id, 'date' => $d->date]);
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="badge bg-label-primary p-2 me-2"><i class="bx bx-calendar"></i></div>
                                    <div>
                                        <span class="fw-bold d-block text-dark">{{ \Carbon\Carbon::parse($d->date)->translatedFormat('l') }}</span>
                                        <small>{{ \Carbon\Carbon::parse($d->date)->translatedFormat('d F Y') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($d->attendance_open && $d->attendance_close)
                                    <span class="badge bg-label-success">
                                        {{ \Carbon\Carbon::parse($d->attendance_open)->format('H:i') }} - {{ \Carbon\Carbon::parse($d->attendance_close)->format('H:i') }}
                                    </span>
                                @else
                                    <span class="badge bg-label-secondary">Belum Diatur</span>
                                @endif
                                <button class="btn btn-xs btn-icon btn-primary ms-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalTime{{ $index }}"
                                        title="Atur Waktu">
                                    <i class="bx bx-time"></i>
                                </button>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress w-100 me-3" style="height: 8px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="fw-bold">{{ $attendedCount }}/{{ $totalParticipants }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <!-- Tombol Copy Link -->
                                    <button class="btn btn-sm btn-outline-primary" onclick="copyLink('{{ $publicLink }}', this)">
                                        <i class="bx bx-copy me-1"></i> Copy Link
                                    </button>
                                    <!-- Tombol Lihat Detail -->
                                    <a href="{{ route('attendance.detail.daily', ['id' => $training->id, 'date' => $d->date]) }}" class="btn btn-sm btn-primary">
                                        <i class="bx bx-list-check me-1"></i> Daftar Hadir
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="bx bx-calendar-x mb-2 text-light" style="font-size: 3rem;"></i>
                                <p class="text-muted">Belum ada jadwal yang diinput untuk pelatihan ini.</p>
                                <a href="{{ route('trainings.schedules', $training->id) }}" class="btn btn-sm btn-primary">Buat Jadwal Sekarang</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@foreach($dates as $index => $d)
<div class="modal fade" id="modalTime{{ $index }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-sm modal-dialog-centered">
                                <form action="{{ route('attendance.set-time-date', $training->id) }}" method="POST" class="modal-content">
                                    @csrf 
                                    @method('PUT')
                                    <input type="hidden" name="date" value="{{ $d->date }}">
                                    
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title">Atur Jam Absen</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="text-center mb-3">
                                            <div class="p-2 bg-light rounded border">
                                                <small class="text-muted d-block">Tanggal Terpilih</small>
                                                <span class="fw-bold text-primary">{{ \Carbon\Carbon::parse($d->date)->translatedFormat('l, d M Y') }}</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label class="form-label small fw-bold">JAM BUKA</label>
                                                <input type="time" name="attendance_open" class="form-control" 
                                                       value="{{ $d->attendance_open ?? '07:30' }}" required>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="form-label small fw-bold">JAM TUTUP</label>
                                                <input type="time" name="attendance_close" class="form-control" 
                                                       value="{{ $d->attendance_close ?? '10:00' }}" required>
                                            </div>
                                        </div>
                                        <p class="mb-0 text-muted small"><i class="bx bx-info-circle me-1"></i> Jam ini akan berlaku untuk semua materi di tanggal ini.</p>
                                    </div>
                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
@endforeach

@push('js')
<script>
/**
 * Fungsi untuk menyalin link ke clipboard
 */
function copyLink(url, btn) {
    navigator.clipboard.writeText(url).then(() => {
        // Berikan feedback visual pada tombol
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bx bx-check me-1"></i> Tersalin';
        btn.classList.replace('btn-outline-primary', 'btn-success');
        
        // Kembalikan tombol ke semula setelah 2 detik
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.replace('btn-success', 'btn-outline-primary');
        }, 2000);
    }).catch(err => {
        console.error('Gagal menyalin: ', err);
        alert('Gagal menyalin link.');
    });
}
</script>
@endpush

@endsection