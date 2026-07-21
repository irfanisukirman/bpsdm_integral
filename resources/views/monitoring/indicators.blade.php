@extends('layouts.master')

@section('title', 'Kelola Indikator Monitoring')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-3 mb-0">
        <span class="text-muted fw-light">Sistem /</span> Kelola Indikator Monitoring
    </h4>
    <div class="d-flex gap-2">
        <!-- Tombol Modal Import -->
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport">
            <i class="bx bx-file me-1"></i> Import Excel
        </button>
    </div>
</div>

<!-- FORM BUAT INDIKATOR -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Form Buat Indikator</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('indicators.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kategori Monitoring</label>
                    <select name="category" class="form-select" required>
                        <option value="Monitoring Penyelenggara">Monitoring Penyelenggara</option>
                        <option value="Monitoring Peserta">Monitoring Peserta</option>
                        <option value="Monitoring Tenaga Kediklatan">Monitoring Tenaga Kediklatan</option>
                        <option value="Monitoring Sarana Prasarana">Monitoring Sarana Prasarana</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Metode Pelatihan</label>
                    <select name="metode" class="form-select" required>
                        <option value="klasikal">Klasikal</option>
                        <option value="blended">Blended Learning</option>
                        <option value="full learning">Full Learning</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Indikator Pertanyaan</label>
                    <input type="text" name="question_text" class="form-control" placeholder="Contoh: Apakah berkas administrasi lengkap?" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bx bx-save me-1"></i> Simpan Indikator
            </button>
        </form>
    </div>
</div>

<div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('indicators.import') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Import Indikator Monitoring</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 shadow-none">
                    <small class="fw-bold d-block mb-1">Panduan Pengisian:</small>
                    <ul class="small mb-0">
                        <li>Kolom <strong>kategori</strong> diisi: Monitoring Penyelenggara, Monitoring Peserta, Monitoring Tenaga Kediklatan, atau Monitoring Sarana Prasarana.</li>
                        <li>Kolom <strong>metode</strong> diisi: klasikal, blended, atau full learning.</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih File Excel</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required>
                </div>
            </div>
            <div class="modal-footer border-top">
                <!-- Link Download Template -->
                <a href="{{ route('indicators.template') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-download me-1"></i> Template
                </a>
                <button type="submit" class="btn btn-primary">Mulai Import</button>
            </div>
        </form>
    </div>
</div>

<!-- TABEL KELOLA INDIKATOR -->
<div class="card">
    <h5 class="card-header">Daftar Indikator Monitoring</h5>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Kategori</th>
                    <th>Metode</th>
                    <th>Indikator</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($indicators as $index => $ind)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><span class="badge bg-label-info">{{ $ind->category }}</span></td>
                    <td><span class="badge bg-label-secondary text-capitalize">{{ $ind->metode }}</span></td>
                    <td class="text-wrap" style="min-width: 300px;">{{ $ind->question_text }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-icon btn-outline-warning" 
                                data-bs-toggle="modal" data-bs-target="#editModal" 
                                onclick="editIndicator({{ json_encode($ind) }})">
                                <i class="bx bx-edit"></i>
                            </button>
                            <form action="{{ route('indicators.destroy', $ind->id) }}" method="POST" onsubmit="return confirm('Hapus indikator ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-outline-danger"><i class="bx bx-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada indikator monitoring.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL EDIT INDIKATOR -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="editForm" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Indikator Monitoring</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="category" id="edit_category" class="form-select" required>
                        <option value="Monitoring Penyelenggara">Monitoring Penyelenggara</option>
                        <option value="Monitoring Peserta">Monitoring Peserta</option>
                        <option value="Monitoring Tenaga Kediklatan">Monitoring Tenaga Kediklatan</option>
                        <option value="Monitoring Sarana Prasarana">Monitoring Sarana Prasarana</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Metode</label>
                    <select name="metode" id="edit_metode" class="form-select" required>
                        <option value="klasikal">Klasikal</option>
                        <option value="blended">Blended Learning</option>
                        <option value="full learning">Full Learning</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Indikator Pertanyaan</label>
                    <textarea name="question_text" id="edit_question_text" class="form-control" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    function editIndicator(data) {
        const url = "{{ url('monitoring-indicators') }}/" + data.id;
        $('#editForm').attr('action', url);
        $('#edit_category').val(data.category);
        $('#edit_metode').val(data.metode);
        $('#edit_question_text').val(data.question_text);
    }
</script>
@endpush