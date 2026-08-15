@extends('layouts.master')

@section('title', 'Manajemen Peserta')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header dengan Tombol Aksi Terkelompok -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">Pelatihan /</span> Peserta: {{ $training->nama_pelatihan }}
            </h4>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <!-- TOMBOL KEMBALI -->
            <a href="{{ route('trainings.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
            <!-- TOMBOL TAMBAH MANUAL -->
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdd">
                <i class="bx bx-plus me-1"></i> Tambah Peserta
            </button>
            <!-- TOMBOL IMPORT EXCEL -->
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport">
                <i class="bx bx-file me-1"></i> Import Excel
            </button>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible border-0 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-check-circle me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible border-0 shadow-sm" role="alert">
            <div class="d-flex">
                <i class="bx bx-error-circle me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tabel Peserta Modern -->
    <div class="card shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Peserta Pelatihan</h5>
            <span class="badge bg-label-primary">{{ count($participants) }} ORANG</span>
        </div>
        
        <div class="table-responsive text-nowrap">
            <table class="table table-hover" style="table-layout: fixed; width: 100%;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 160px;">NIP / NIK</th>
                        <th style="width: 200px;">NAMA LENGKAP</th>
                        <th style="width: 130px;">GENDER/STATUS</th>
                        <th style="width: 180px;">JABATAN</th>
                        <th>INSTANSI & WILAYAH</th>
                        <th style="width: 90px;" class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($participants as $p)
                    <tr>
                        <!-- Kolom NIP -->
                        <td class="align-top">
                            <code class="fw-bold text-danger" style="font-size: 0.8rem;">{{ $p->nip_nik }}</code>
                        </td>

                        <!-- Kolom Nama -->
                        <td class="align-top text-wrap">
                            <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $p->name }}</span>
                        </td>

                        <!-- Kolom Gender & Status -->
                        <td class="align-top">
                            <small class="d-block fw-bold text-secondary">{{ $p->gender }}</small>
                            <span class="badge bg-label-info" style="font-size: 0.65rem;">{{ $p->status_kepegawaian }}</span>
                        </td>

                        <!-- Kolom Jabatan -->
                        <td class="align-top text-wrap">
                            <small class="text-dark fw-semibold" style="line-height: 1.2; display: block;">
                                {{ $p->jabatan }}
                            </small>
                        </td>

                        <!-- Kolom Instansi & Wilayah -->
                        <td class="align-top text-wrap">
                            <span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $p->instansi }}</span><br>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                <i class="bx bx-map-pin" style="font-size: 10px;"></i> {{ $p->kabupaten_kota }}, {{ $p->provinsi }}
                            </small>
                        </td>

                        <!-- Kolom Aksi -->
                        <td class="align-top text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <!-- Tombol Edit -->
                                <button class="btn btn-xs btn-icon btn-outline-warning" 
                                    onclick="editParticipant({{ json_encode($p) }})"
                                    data-bs-toggle="modal" data-bs-target="#modalEdit">
                                    <i class="bx bx-edit"></i>
                                </button>

                                <!-- Tombol Hapus -->
                                <form action="{{ route('participants.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus peserta ini?')">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-icon btn-outline-danger">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bx bx-info-circle mb-2" style="font-size: 2rem;"></i>
                            <p>Belum ada data peserta dalam pelatihan ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH PESERTA MANUAL -->
<div class="modal fade" id="modalAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('participants.store', $training->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Tambah Peserta Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">NIP / NIK</label>
                        <input type="text" name="nip_nik" class="form-control" placeholder="Masukkan NIP/NIK" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" placeholder="Nama tanpa gelar disarankan" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Jabatan</label>
                        <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Analis SDM" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Instansi</label>
                        <input type="text" name="instansi" class="form-control" placeholder="Contoh: BPSDM" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="Laki-Laki">Laki-Laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Status Kepegawaian</label>
                        <input type="text" name="status_kepegawaian" class="form-control" placeholder="Contoh: PNS / PPPK / ASN" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Provinsi</label>
                        <input type="text" name="provinsi" class="form-control" placeholder="Jawa Barat" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Kabupaten / Kota</label>
                        <input type="text" name="kabupaten_kota" class="form-control" placeholder="Kota Bandung" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top p-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL IMPORT EXCEL -->
<div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('participants.import', $training->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Import Peserta via Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info shadow-none" style="background-color: #e7e7ff; border-color: #d2d2ff;">
                    <small class="text-primary fw-bold d-block mb-1">PENTING:</small>
                    <ul class="small mb-0 text-primary">
                        <li>Gunakan tanda <strong>'</strong> (kutip satu) di awal NIP/NIK agar tidak berubah menjadi 0.</li>
                        <li>Format kolom: <strong>nip_nik, nama_lengkap, gender, jabatan, instansi, provinsi, kabupaten_kota, status_kepegawaian</strong>.</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih File Excel (.xlsx / .xls)</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required>
                </div>
            </div>
            <div class="modal-footer border-top p-3">
                <a href="{{ route('participants.template') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-download me-1"></i> Template
                </a>
                <button type="submit" class="btn btn-success">Mulai Import</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT PESERTA -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="formEditParticipant" class="modal-content">
            @csrf 
            @method('PUT')
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Edit Data Peserta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">NIP / NIK</label>
                    <input type="text" name="nip_nik" id="edit_nip" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Jabatan</label>
                        <input type="text" name="jabatan" id="edit_jabatan" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Instansi</label>
                        <input type="text" name="instansi" id="edit_instansi" class="form-control" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Gender</label>
                        <select name="gender" id="edit_gender" class="form-select" required>
                            <option value="Laki-Laki">Laki-Laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Status Kepegawaian</label>
                        <input type="text" name="status_kepegawaian" id="edit_status" class="form-control" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Provinsi</label>
                        <input type="text" name="provinsi" id="edit_provinsi" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Kabupaten / Kota</label>
                        <input type="text" name="kabupaten_kota" id="edit_kabupaten" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top p-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Update Data</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('css')
<style>
    /* Memaksa teks yang panjang turun ke bawah */
    .text-wrap {
        white-space: normal !important;
        word-wrap: break-word;
    }
    table.table {
        width: 100% !important;
        margin: 0 !important;
    }
    .table td {
        padding: 0.85rem 0.75rem !important;
    }
    .align-top {
        vertical-align: top !important;
    }
</style>
@endpush

@push('js')
<script>
    function editParticipant(data) {
        // Set Action URL dinamis berdasarkan ID
        const url = "{{ url('participants') }}/" + data.id;
        $('#formEditParticipant').attr('action', url);

        // Isi data ke field modal
        $('#edit_nip').val(data.nip_nik);
        $('#edit_name').val(data.name);
        $('#edit_gender').val(data.gender); 
        $('#edit_jabatan').val(data.jabatan);
        $('#edit_instansi').val(data.instansi);
        $('#edit_provinsi').val(data.provinsi); 
        $('#edit_kabupaten').val(data.kabupaten_kota); 
        $('#edit_status').val(data.status_kepegawaian);
    }
</script>
@endpush