@extends('layouts.master')
@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Evaluasi /</span> Kelola : {{ $training->nama_pelatihan }}</h4>

<div class="row">
    <!-- Menu Navigasi Samping -->
    <div class="col-md-3">
        <div class="list-group">
            <a href="#peserta" class="list-group-item list-group-item-action active" data-bs-toggle="list">Peserta</a>
            <a href="#kehadiran" class="list-group-item list-group-item-action" data-bs-toggle="list">Kehadiran</a>
            <a href="#l1" class="list-group-item list-group-item-action" data-bs-toggle="list">Level 1: Reaksi</a>
            <a href="#l2" class="list-group-item list-group-item-action" data-bs-toggle="list">Level 2: Learning</a>
            <a href="#l34" class="list-group-item list-group-item-action" data-bs-toggle="list">Level 3 & 4: Dampak</a>
        </div>
    </div>

    <!-- Konten Navigasi -->
    <div class="col-md-9">
        <div class="tab-content p-0">
            <!-- PESERTA TAB -->
            <div class="tab-pane fade show active" id="peserta">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Manajemen Peserta</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddParticipant">Tambah Peserta</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>NIP/NIK</th><th>Nama</th><th>Aksi</th></tr></thead>
                            <tbody>
                                @foreach($training->participants as $p)
                                <tr>
                                    <td>{{ $p->nip_nik }}</td>
                                    <td>{{ $p->name }}</td>
                                    <td><button class="btn btn-sm text-danger"><i class="bx bx-trash"></i></button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- LEVEL 1 TAB -->
            <div class="tab-pane fade" id="l1">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Evaluasi Level 1</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateL1">Buat Form L1</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr><th>Nama Form</th><th>Info</th><th>Status Isi</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                @foreach($evaluations_l1 as $l1)
                                <tr>
                                    <td>{{ $l1->name }}</td>
                                    <td><small>{{ $l1->narasumber_name ?? 'Penyelenggara' }}</small></td>
                                    <td>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar" style="width: 20%"></div>
                                        </div>
                                        <small>1/30 (3%)</small>
                                    </td>
                                    <td>
                                        <button class="btn btn-xs btn-outline-primary" onclick="copyLink('{{ route('public.l1', $l1->id) }}')">Link</button>
                                        <a href="#" class="btn btn-xs btn-outline-info">Progres</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            {{-- Tab Kehadiran, L2, L34 lainnya dengan struktur serupa... --}}
        </div>
    </div>
</div>

<!-- MODAL FORM INPUT EVALUASI L1 (DINAMIS) -->
<div class="modal fade" id="modalCreateL1" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('evall1.store', $training->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5>Buat Form Evaluasi L1</h5></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Form</label>
                    <input type="text" name="form_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jenis Form</label>
                    <select name="type" class="form-select" id="select-type-l1">
                        <option value="penyelenggara">Evaluasi Penyelenggara</option>
                        <option value="narasumber">Evaluasi Narasumber</option>
                    </select>
                </div>

                <!-- Field Dinamis Penyelenggara -->
                <div id="field-penyelenggara">
                    <div class="mb-3">
                        <label class="form-label">Instansi Penyelenggara</label>
                        <input type="text" name="instansi_penyelenggara" class="form-control">
                    </div>
                </div>

                <!-- Field Dinamis Narasumber (Ambil dari Jadwal) -->
                <div id="field-narasumber" style="display:none">
                    <div class="mb-3">
                        <label class="form-label">Pilih Sesi Jadwal (Otomatis)</label>
                        <select name="schedule_id" class="form-select" id="schedule-select">
                            <option value="">-- Pilih Sesi --</option>
                            @foreach($training->schedules as $s)
                                <option value="{{ $s->id }}" data-pic="{{ $s->responsible_person }}" data-materi="{{ $s->activity }}" data-tgl="{{ $s->date }}">
                                    {{ $s->activity }} - {{ $s->responsible_person }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Pengajar</label>
                        <input type="text" name="pic_name" id="pic_name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Materi Ajar</label>
                        <input type="text" name="materi" id="materi" class="form-control" readonly>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary w-100">Simpan Form</button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    // Logika Show/Hide Field L1
    $('#select-type-l1').change(function() {
        if($(this).val() == 'narasumber'){
            $('#field-narasumber').show();
            $('#field-penyelenggara').hide();
        } else {
            $('#field-narasumber').hide();
            $('#field-penyelenggara').show();
        }
    });

    // Logika Auto-fill dari Jadwal
    $('#schedule-select').change(function() {
        let pic = $(this).find(':selected').data('pic');
        let materi = $(this).find(':selected').data('materi');
        $('#pic_name').val(pic);
        $('#materi').val(materi);
    });
</script>
@endpush
@endsection