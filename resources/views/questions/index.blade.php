@extends('layouts.master')

@section('title', 'Kelola Soal Evaluasi')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem /</span> Bank Soal Evaluasi</h4>

<!-- FORM BUAT SOAL -->
<div class="card mb-4">
    <div class="card-header border-bottom mb-3">
        <h5 class="mb-0">Form Buat Soal Evaluasi</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('questions.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jenis Pelatihan</label>
                    <select name="training_type" class="form-select" required>
                        <option value="PKTI/PKTU">PKTI/PKTU</option>
                        <option value="CPNS">CPNS</option>
                        <option value="PKP">PKP</option>
                        <option value="PKA">PKA</option>
                        <option value="PKN">PKN</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kategori / Level</label>
                    <select name="category" class="form-select" required>
                        <optgroup label="Level 1 & 2">
                            <option value="l1_penyelenggara">L1 - Penyelenggara</option>
                            <option value="l1_narasumber">L1 - Narasumber</option>
                            <option value="l2_pretest">L2 - Soal Pretest</option>
                            <option value="l2_posttest">L2 - Soal Posttest</option>
                        </optgroup>
                        <optgroup label="Level 3 & 4 (360°)">
                            <option value="l34_mandiri">L3 & L4 - Mandiri</option>
                            <option value="l34_rekan">L3 & L4 - Rekan</option>
                            <option value="l34_atasan">L3 & L4 - Atasan</option>
                        </optgroup>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipe Jawaban</label>
                    <select name="type" class="form-select" onchange="handleTypeChange(this, 'create-options-wrapper')" required>
                        <option value="ya_tidak">Pilihan (Ya / Tidak)</option>
                        <option value="slider">Slider Angka (10-100)</option>
                        <option value="dropdown">Dropdown (Pilihan)</option>
                        <option value="text">Teks Paragraf</option>
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Butir Pertanyaan</label>
                    <textarea name="question_text" class="form-control" rows="2" required></textarea>
                </div>

                <!-- DYNAMIC OPTIONS FOR DROPDOWN -->
                <div class="col-12 mb-4" id="create-options-wrapper" style="display:none;">
                    <label class="form-label text-primary fw-bold">Pilihan Jawaban</label>
                    <div class="options-container">
                        <div class="input-group mb-2">
                            <input type="text" name="options[]" class="form-control" placeholder="Pilihan 1">
                            <button class="btn btn-outline-primary" type="button" onclick="addOptionField('create-options-wrapper')"><i class="bx bx-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Simpan Pertanyaan</button>
        </form>
    </div>
</div>

<!-- DAFTAR SOAL -->
<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Jenis</th>
                    <th>Kategori</th>
                    <th>Pertanyaan & Preview</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($questions as $q)
                <tr>
                    <td><span class="badge bg-label-primary">{{ $q->training_type }}</span></td>
                    <td><small class="fw-bold text-uppercase">{{ str_replace('_', ' ', $q->category) }}</small></td>
                    <td>
                        <div class="fw-bold text-dark mb-2">{{ $q->question_text }}</div>
                        {{-- FIX: Hanya munculkan 1 slider preview yang bersih --}}
                        @if($q->type == 'slider')
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" class="form-range w-25" disabled>
                                <span class="badge bg-label-secondary small">Skala 10-100</span>
                            </div>
                        @elseif($q->type == 'dropdown')
                            @if($q->options)
                                @foreach($q->options as $opt)
                                    <span class="badge bg-label-info btn-xs">{{ $opt }}</span>
                                @endforeach
                            @endif
                        @else
                            <small class="text-muted"><i class="bx bx-align-left me-1"></i>Input Teks Paragraf</small>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-icon btn-outline-warning" onclick="editQuestion({{ json_encode($q) }})" data-bs-toggle="modal" data-bs-target="#editModal"><i class="bx bx-edit"></i></button>
                            <form action="{{ route('questions.destroy', $q->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-icon btn-outline-danger" onclick="return confirm('Hapus soal?')"><i class="bx bx-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="" method="POST" id="editForm" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Edit Pertanyaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jenis Pelatihan</label>
                        <select name="training_type" id="edit_training_type" class="form-select">
                            <option value="PKTI/PKTU">PKTI/PKTU</option>
                            <option value="CPNS">CPNS</option>
                            <option value="PKP">PKP</option>
                            <option value="PKA">PKA</option>
                            <option value="PKN">PKN</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category" id="edit_category" class="form-select">
                            <option value="l1_penyelenggara">L1 - Penyelenggara</option>
                            <option value="l1_narasumber">L1 - Narasumber</option>
                            <option value="l2_pretest">L2 - Soal Pretest</option>
                            <option value="l2_posttest">L2 - Soal Posttest</option>
                            <option value="l34_mandiri">L3 & L4 - Mandiri</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tipe</label>
                        <select name="type" id="edit_type" class="form-select" onchange="handleTypeChange(this, 'edit-options-wrapper')">
                            
                        <option value="slider">Slider</option>
                            <option value="dropdown">Dropdown</option>
                            <option value="text">Teks</option>
                            <option value="ya_tidak">Ya / Tidak</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Butir Pertanyaan</label>
                        <textarea name="question_text" id="edit_question_text" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-12 mb-3" id="edit-options-wrapper" style="display:none;">
                        <label class="form-label text-primary fw-bold">Pilihan Jawaban</label>
                        <div class="options-container">
                            <!-- JS akan mengisi input di sini -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="submit" class="btn btn-primary w-100">Update Pertanyaan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('js')
<script>
    // Fungsi Menambah Input Field untuk Opsi
    function addOptionField(wrapperId, value = '') {
        const container = $(`#${wrapperId} .options-container`);
        const html = `
            <div class="input-group mb-2 animate__animated animate__fadeIn">
                <input type="text" name="options[]" class="form-control" value="${value}" placeholder="Masukkan pilihan">
                <button class="btn btn-outline-danger" type="button" onclick="removeOptionField(this)"><i class="bx bx-x"></i></button>
            </div>`;
        container.append(html);
    }

    function removeOptionField(btn) {
        $(btn).closest('.input-group').remove();
    }

    // Fungsi Show/Hide Dropdown Options
    function handleTypeChange(select, wrapperId) {
        if (select.value === 'dropdown') {
            $(`#${wrapperId}`).slideDown();
        } else {
            $(`#${wrapperId}`).slideUp();
        }
    }

    // Fungsi Load Data ke Modal Edit
    function editQuestion(data) {
        $('#editForm').attr('action', `/questions/${data.id}`);
        $('#edit_training_type').val(data.training_type);
        $('#edit_category').val(data.category);
        $('#edit_type').val(data.type);
        $('#edit_question_text').val(data.question_text);

        const container = $('#edit-options-wrapper .options-container');
        container.empty();

        if (data.type === 'dropdown') {
            $('#edit-options-wrapper').show();
            if (data.options && data.options.length > 0) {
                data.options.forEach(opt => addOptionField('edit-options-wrapper', opt));
            } else {
                addOptionField('edit-options-wrapper');
            }
        } else {
            $('#edit-options-wrapper').hide();
        }
    }
</script>
@endpush