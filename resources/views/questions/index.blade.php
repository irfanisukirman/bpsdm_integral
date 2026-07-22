@extends('layouts.master')

@section('title', 'Kelola Soal Evaluasi')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem /</span> Bank Soal Evaluasi</h4>

<!-- FORM BUAT SOAL -->
<div class="card mb-4">
    <div class="card-header border-bottom mb-3">
        <h5 class="mb-0 text-primary">Form Buat Soal Evaluasi (Level 1, 3, & 4)</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('questions.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-dark">Jenis Pelatihan</label>
                    <select name="training_type" class="form-select border-primary" required>
                        <option value="PKTI/PKTU">PKTI/PKTU</option>
                        <option value="CPNS">CPNS</option>
                        <option value="PKP">PKP</option>
                        <option value="PKA">PKA</option>
                        <option value="PKN">PKN</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-dark">Kategori / Level</label>
                    <select name="category" class="form-select border-primary" required>
                        <optgroup label="Level 1: Reaksi">
                            <option value="l1_penyelenggara">L1 - Penyelenggara</option>
                            <option value="l1_narasumber">L1 - Narasumber</option>
                        </optgroup>
                        <optgroup label="Level 3 & 4: Dampak (360°)">
                            <option value="l34_mandiri">L3 & L4 - Mandiri (Alumni)</option>
                            <option value="l34_rekan">L3 & L4 - Rekan Kerja</option>
                            <option value="l34_atasan">L3 & L4 - Atasan Langsung</option>
                        </optgroup>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-dark">Tipe Jawaban</label>
                    <select name="type" class="form-select border-primary" onchange="handleTypeChange(this, 'create-options-wrapper')" required>
                        <option value="slider">Slider Angka (10-100)</option>
                        <option value="dropdown">Dropdown (Pilihan)</option>
                        <option value="text">Teks Paragraf</option>
                        {{-- Opsi ya_tidak dihapus sesuai permintaan --}}
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold text-dark">Butir Pertanyaan</label>
                    <textarea name="question_text" class="form-control border-primary" rows="2" placeholder="Tuliskan pertanyaan di sini..." required></textarea>
                </div>

                <!-- DYNAMIC OPTIONS FOR DROPDOWN -->
                <div class="col-12 mb-4" id="create-options-wrapper" style="display:none;">
                    <label class="form-label text-info fw-bold">Daftar Pilihan Jawaban</label>
                    <div class="options-container">
                        <div class="input-group mb-2">
                            <input type="text" name="options[]" class="form-control" placeholder="Isi pilihan jawaban...">
                            <button class="btn btn-outline-primary" type="button" onclick="addOptionField('create-options-wrapper')"><i class="bx bx-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImportSoal">
                            <i class="bx bx-file me-1"></i> Import Soal
                        </button>
                        <button type="submit" class="btn btn-primary w-100 shadow"><i class="bx bx-save me-1"></i> Simpan Pertanyaan Evaluasi</button>
                    </div>
           
        </form>
    </div>
</div>

<!-- DAFTAR SOAL -->
<div class="card shadow-sm">
    <div class="table-responsive text-wrap p-3">
        <table class="table table-hover" style="table-layout: fixed; width: 100%;">
            <thead class="table-light">
                <tr>
                    <th style="width: 120px;">Jenis</th>
                    <th style="width: 150px;">Kategori</th>
                    <th>Pertanyaan & Preview</th>
                    <th style="width: 100px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php $count = 0; @endphp
                @foreach($questions as $q)
                    {{-- Saringan: Hilangkan monitoring, level 2, dan tipe ya_tidak --}}
                    @if(!str_contains(strtolower($q->category), 'monitoring') && 
                        !str_contains(strtolower($q->category), 'l2') && 
                        $q->type !== 'ya_tidak')
                        
                        @php $count++; @endphp
                        <tr>
                            <td class="align-top"><span class="badge bg-label-primary">{{ $q->training_type }}</span></td>
                            <td class="align-top text-wrap">
                                <small class="fw-bold text-uppercase text-muted" style="font-size: 10px;">
                                    {{ str_replace('_', ' ', $q->category) }}
                                </small>
                            </td>
                            <td class="align-top">
                                <div class="fw-bold text-dark mb-2 text-wrap" style="line-height: 1.4;">{{ $q->question_text }}</div>
                                
                                @if($q->type == 'slider')
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="range" class="form-range w-25" disabled>
                                        <span class="badge bg-label-secondary" style="font-size: 9px;">SKALA 10-100</span>
                                    </div>
                                @elseif($q->type == 'dropdown' && is_array($q->options))
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($q->options as $opt)
                                            <span class="badge bg-label-info" style="font-size: 9px;">{{ $opt }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <small class="text-muted"><i class="bx bx-align-left me-1"></i>Input Teks Paragraf</small>
                                @endif
                            </td>
                            <td class="text-center align-top">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-xs btn-icon btn-outline-warning" onclick="editQuestion({{ json_encode($q) }})" data-bs-toggle="modal" data-bs-target="#editModal"><i class="bx bx-edit"></i></button>
                                    <form action="{{ route('questions.destroy', $q->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-icon btn-outline-danger" onclick="return confirm('Hapus soal ini?')"><i class="bx bx-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
                
                @if($count == 0)
                <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada soal evaluasi (L1, L3, L4).</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalImportSoal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('questions.import') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Import Bank Soal (Excel)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Panduan Singkat -->
                <div class="alert alert-info py-2 shadow-none mb-3">
                    <small class="fw-bold d-block mb-1">ATURAN PENGISIAN EXCEL:</small>
                    <ul class="small mb-0 ps-3">
                        <li><strong>level_peran</strong>: Mandiri, Atasan, Rekan, Penyelenggara, Narasumber.</li>
                        <li><strong>tipe_jawaban</strong>: slider, dropdown, atau text.</li>
                        <li><strong>pilihan_jawaban</strong>: Isi jika tipe=dropdown (pisahkan dengan koma).</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih File Excel</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required>
                </div>
            </div>
            <div class="modal-footer border-top">
                <a href="{{ route('questions.template') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-download me-1"></i> Download Template
                </a>
                <button type="submit" class="btn btn-primary">Mulai Import</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="" method="POST" id="editForm" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Edit Pertanyaan Evaluasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <option value="l34_mandiri">L3 & L4 - Mandiri</option>
                            <option value="l34_rekan">L3 & L4 - Rekan Kerja</option>
                            <option value="l34_atasan">L3 & L4 - Atasan Langsung</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tipe</label>
                        <select name="type" id="edit_type" class="form-select" onchange="handleTypeChange(this, 'edit-options-wrapper')">
                            <option value="slider">Slider</option>
                            <option value="dropdown">Dropdown</option>
                            <option value="text">Teks</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Butir Pertanyaan</label>
                        <textarea name="question_text" id="edit_question_text" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="col-12 mb-3" id="edit-options-wrapper" style="display:none;">
                        <label class="form-label text-primary fw-bold">Pilihan Jawaban</label>
                        <div class="options-container">
                            <!-- Diisi via JS -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Update Pertanyaan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
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

    function handleTypeChange(select, wrapperId) {
        if (select.value === 'dropdown') {
            $(`#${wrapperId}`).slideDown();
        } else {
            $(`#${wrapperId}`).slideUp();
        }
    }

    function editQuestion(data) {
        const url = "{{ url('questions') }}/" + data.id;
        $('#editForm').attr('action', url);
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