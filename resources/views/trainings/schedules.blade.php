@extends('layouts.master')

@section('title', 'Jadwal Pelatihan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-3 mb-0">
        <span class="text-muted fw-light">Pelatihan /</span> Jadwal: {{ $training->nama_pelatihan }}
    </h4>
    <!-- TOMBOL DOWNLOAD PDF: Menggunakan $training->id -->
    <a href="{{ route('schedules.pdf', $training->id) }}" class="btn btn-danger">
        <i class="bx bxs-file-pdf me-1"></i> Download PDF
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
@endif

<div class="row">
    <!-- KOLOM KIRI: FORM TAMBAH (Hanya gunakan $training) -->
    <div class="col-md-4">
        <div class="card mb-4">
            <h5 class="card-header">Tambah Sesi Jadwal</h5>
            <div class="card-body">
                <!-- Action menggunakan $training->id -->
                <form action="{{ route('schedules.store', $training->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ old('date') }}" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Mulai</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Selesai</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Materi / Kegiatan</label>
                        <input type="text" name="activity" class="form-control" placeholder="Contoh: Kebijakan Teknis" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-primary fw-bold">Penanggung Jawab</label>
                        <input type="text" name="pic" class="form-control" placeholder="Nama pengajar / PIC" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-plus me-1"></i> Simpan Jadwal
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: TABEL DAFTAR (Baru boleh gunakan $s) -->
    <div class="col-md-8">
        <div class="card">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Materi & Penanggung Jawab</th> 
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($schedules as $s)
                        <tr>
                            <td>
                                <span class="badge bg-label-secondary">
                                    {{ \Carbon\Carbon::parse($s->date)->format('d/m/Y') }}
                                </span><br>
                                <small class="fw-bold">{{ $s->start_time }} - {{ $s->end_time }}</small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $s->activity }}</div>
                                <small class="text-primary"><i class="bx bx-user me-1"></i>{{ $s->pic ?? '-' }}</small>
                            </td>
                            <td>
                                <!-- Tombol Hapus: Menggunakan $s->id karena berada di dalam loop -->
                                <form action="{{ route('schedules.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus sesi jadwal ini?')">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">Belum ada sesi jadwal yang dibuat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection