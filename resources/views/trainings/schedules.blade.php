@extends('layouts.master')

@section('title', 'Jadwal Pelatihan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-3 mb-0">
        <span class="text-muted fw-light">Pelatihan /</span> Jadwal: {{ $training->nama_pelatihan }}
    </h4>
    <a href="{{ route('trainings.manage', $training->id) }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Kembali ke Pengelolaan
    </a>
    <a href="{{ route('schedules.pdf', $training->id) }}" class="btn btn-danger">
        <i class="bx bxs-file-pdf me-1"></i> Download PDF
    </a>

</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <!-- KOLOM KIRI: FORM TAMBAH -->
    <div class="col-md-4">
        <div class="card mb-4">
            <h5 class="card-header">Tambah Sesi Jadwal</h5>
            <div class="card-body">
                <form action="{{ route('schedules.store', $training->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="date" class="form-control" required>
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
                        <input type="text" name="activity" class="form-control" placeholder="..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-primary fw-bold">Penanggung Jawab</label>
                        <input type="text" name="pic" class="form-control" placeholder="..." required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-plus me-1"></i> Simpan Jadwal
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: TABEL DAFTAR -->
    <div class="col-md-8">
        <div class="card">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Materi & Penanggung Jawab</th> 
                            <th width="150" class="text-center">Aksi</th>
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
                                <small class="text-primary"><i class="bx bx-user me-1"></i>{{ $s->pic }}</small>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <!-- TOMBOL EDIT -->
                                    <button class="btn btn-sm btn-icon btn-outline-warning" 
                                        onclick="editSchedule({{ json_encode($s) }})"
                                        data-bs-toggle="modal" data-bs-target="#modalEditSchedule">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <!-- TOMBOL HAPUS -->
                                    <form action="{{ route('schedules.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger"><i class="bx bx-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-4">Belum ada jadwal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT JADWAL -->
<div class="modal fade" id="modalEditSchedule" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="formEditSchedule" class="modal-content">
            @csrf 
            @method('PUT')
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Edit Sesi Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" id="edit_date" class="form-control" required>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Jam Mulai</label>
                        <input type="time" name="start_time" id="edit_start" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Jam Selesai</label>
                        <input type="time" name="end_time" id="edit_end" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Materi / Kegiatan</label>
                    <input type="text" name="activity" id="edit_activity" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Penanggung Jawab (PIC)</label>
                    <input type="text" name="pic" id="edit_pic" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    function editSchedule(data) {
        // Tentukan URL rute update secara dinamis
        const url = "{{ url('schedules') }}/" + data.id;
        $('#formEditSchedule').attr('action', url);

        // Masukkan data ke dalam field modal
        $('#edit_date').val(data.date);
        $('#edit_start').val(data.start_time);
        $('#edit_end').val(data.end_time);
        $('#edit_activity').val(data.activity);
        $('#edit_pic').val(data.pic);
    }
</script>
@endpush