@extends('layouts.master')

@section('title', 'Kelola Soal Evaluasi')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem /</span> Bank Soal Evaluasi</h4>

@if($isSuperadmin && blank($selectedBidang))
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h5 class="mb-1"><i class="bx bx-copy-alt text-primary me-2"></i>Duplikasi Bundel Evaluasi</h5>
                <p class="text-muted mb-0">Salin seluruh pertanyaan evaluasi dari satu bidang ke bidang lain.</p>
            </div>
            <span class="badge bg-label-primary">Khusus Superadmin</span>
        </div>
        <form action="{{ route('questions.duplicate-bundle') }}" method="POST" onsubmit="return confirm('Duplikat seluruh bundel pertanyaan ke bidang tujuan?')">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Bidang Sumber</label>
                    <select name="source_bidang" class="form-select border-primary" required>
                        <option value="">-- Pilih Bidang Sumber --</option>
                        @foreach($bundleStats->where('total', '>', 0) as $bundle)
                            <option value="{{ $bundle['bidang'] }}">{{ $bundle['bidang'] }} ({{ $bundle['total'] }} pertanyaan)</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold">Bidang Tujuan</label>
                    <select name="target_bidang" class="form-select border-primary" required>
                        <option value="">-- Pilih Bidang Tujuan --</option>
                        @foreach($bidangOptions as $bidang)
                            <option value="{{ $bidang }}">{{ $bidang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-copy me-1"></i>Duplikat</button>
                </div>
            </div>
            <div class="form-text mt-2">Pertanyaan yang identik di bidang tujuan akan dilewati sehingga tidak terjadi duplikasi ganda.</div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-1">Bundel Evaluasi per Bidang</h5>
        <small class="text-muted">Pilih bidang untuk membuat dan mengelola pertanyaannya.</small>
    </div>
</div>
<div class="row g-3">
    @foreach($bundleStats as $bundle)
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="avatar-initial rounded bg-label-primary p-3"><i class="bx bx-folder-open fs-4"></i></span>
                    <span class="badge {{ $bundle['total'] > 0 ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $bundle['total'] }} pertanyaan</span>
                </div>
                <h6 class="mb-3" style="line-height:1.45">{{ $bundle['bidang'] }}</h6>
                <div class="d-flex gap-2 mb-4">
                    <span class="badge bg-label-info">L1: {{ $bundle['l1'] }}</span>
                    <span class="badge bg-label-warning">L3 & L4: {{ $bundle['l34'] }}</span>
                </div>
                <a href="{{ route('questions.index', ['bidang' => $bundle['bidang']]) }}" class="btn btn-outline-primary mt-auto">
                    <i class="bx bx-cog me-1"></i>Kelola Evaluasi
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
@if($isSuperadmin)
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div class="alert alert-primary mb-0 py-2 px-3">
        <i class="bx bx-folder-open me-1"></i>Bundel: <strong>{{ $selectedBidang }}</strong>
    </div>
    <a href="{{ route('questions.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali ke Daftar Bidang</a>
</div>
@endif

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
        <form id="bulkDeleteQuestionsForm" action="{{ route('questions.destroy-selected') }}" method="POST" onsubmit="return confirmSelectedQuestions()">
            @csrf @method('DELETE')
            <input type="hidden" name="bidang" value="{{ $selectedBidang }}">
            @if($selectedProgram)<input type="hidden" name="program" value="{{ $selectedProgram }}">@endif
            <button type="submit" id="deleteSelectedQuestions" class="btn btn-danger" disabled>
                <i class="bx bx-trash me-1"></i>Hapus Terpilih
                <span id="selectedQuestionCount" class="badge bg-white text-danger ms-1">0</span>
            </button>
        </form>
        <small class="text-muted d-none d-md-inline">Centang butir yang ingin dihapus.</small>
    </div>
    <form action="{{ route('questions.destroy-bundle') }}" method="POST"
          onsubmit="return confirm('Hapus seluruh pertanyaan evaluasi pada bidang ini? Semua jawaban yang terkait dengan pertanyaan tersebut juga akan dihapus permanen.')">
        @csrf @method('DELETE')
        <input type="hidden" name="bidang" value="{{ $selectedBidang }}">
        <button type="submit" class="btn btn-outline-danger" {{ $questions->isEmpty() ? 'disabled' : '' }}>
            <i class="bx bx-trash me-1"></i>Hapus Semua Pertanyaan Bidang Ini
        </button>
    </form>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('questions.index') }}" class="row g-3 align-items-end">
            @if($isSuperadmin)<input type="hidden" name="bidang" value="{{ $selectedBidang }}">@endif
            <div class="col-md-5">
                <label class="form-label fw-bold mb-1">Filter Program Evaluasi</label>
                <select name="program" class="form-select" onchange="this.form.submit()">
                    <option value="">Tampilkan Semua Program</option>
                    @foreach($programOptions as $program)
                        <option value="{{ $program }}" @selected($selectedProgram === $program)>
                            {{ $program === 'semua' ? 'Semua Program' : $program }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-7">
                <div class="alert alert-info py-2 mb-0">
                    <small><i class="bx bx-info-circle me-1"></i>Bidang selain Manajerial otomatis menggunakan <strong>PKTI/PKTU</strong>. CPNS, PKP, PKA, dan PKN khusus Bidang Manajerial.</small>
                </div>
            </div>
        </form>
    </div>
</div>

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
                    <label class="form-label fw-bold text-dark">Bidang</label>
                    @if($isSuperadmin)
                        <select name="bidang" class="form-select border-primary" required>
                            @foreach($bidangOptions as $bidang)
                                <option value="{{ $bidang }}" {{ $selectedBidang === $bidang ? 'selected' : '' }}>{{ $bidang }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="hidden" name="bidang" value="{{ Auth::user()->bidang }}">
                        <input type="text" class="form-control bg-light" value="{{ Auth::user()->bidang }}" readonly>
                    @endif
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-dark">Kategori / Level</label>
                    <select name="category" id="create_category" class="form-select border-primary" onchange="syncMethodField('create')" required>
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
                    <label class="form-label fw-bold text-dark">Metode Pelatihan</label>
                    <select name="metode" id="create_metode" class="form-select border-primary">
                        <option value="semua" data-global="true">Semua Metode</option>
                        <option value="klasikal">Klasikal</option>
                        <option value="full learning">Full Learning</option>
                        <option value="blended">Blended Learning</option>
                    </select>
                    <div id="create_method_help" class="form-text">Digunakan untuk evaluasi Level 1.</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-dark">Program Evaluasi</label>
                    <select name="program_evaluasi" id="create_program_evaluasi" class="form-select border-primary">
                        <option value="semua">Semua Program</option>
                        @foreach(['PKTI/PKTU', 'CPNS', 'PKP', 'PKA', 'PKN'] as $program)
                            <option value="{{ $program }}">{{ $program }}</option>
                        @endforeach
                    </select>
                    <div id="create_program_help" class="form-text">Digunakan untuk evaluasi Level 3 & 4.</div>
                </div>
                <div class="col-md-4 mb-3" id="create_subcategory_wrapper" style="display:none">
                    <label class="form-label fw-bold text-dark">Bagian Evaluasi L3/L4</label>
                    <select name="sub_category" id="create_sub_category" class="form-select border-primary" disabled>
                        <option value="Data Diri Alumni">1. Data Diri Alumni</option>
                        <option value="Penempatan Tugas dan Transfer Learning">2. Penempatan Tugas dan Transfer Learning</option>
                        <option value="Perubahan Perilaku">3. Perubahan Perilaku</option>
                        <option value="Dampak Pelatihan">4. Dampak Pelatihan</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-dark">Tipe Jawaban</label>
                    <select name="type" class="form-select border-primary" onchange="handleTypeChange(this, 'create-options-wrapper')" required>
                        <option value="slider">Slider Angka (10-100)</option>
                        <option value="dropdown">Dropdown (Pilihan)</option>
                        <option value="checkbox">Checkbox (Bisa Pilih Lebih dari Satu)</option>
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
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImportSoal">
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
                    <th style="width: 52px;" class="text-center">
                        <input type="checkbox" id="selectAllQuestions" class="form-check-input" title="Pilih semua pertanyaan yang tampil" aria-label="Pilih semua pertanyaan">
                    </th>
                    <th style="width: 240px;">Bidang</th>
                    <th style="width: 190px;">Klasifikasi</th>
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
                        <tr class="question-row">
                            <td class="text-center align-top">
                                <input type="checkbox" name="question_ids[]" value="{{ $q->id }}" form="bulkDeleteQuestionsForm" class="form-check-input question-select" aria-label="Pilih pertanyaan {{ $count }}">
                            </td>
                            <td class="align-top"><span class="badge bg-label-primary text-wrap text-start">{{ $q->bidang ?: $q->training_type }}</span></td>
                            <td class="align-top text-wrap">
                                <small class="fw-bold text-uppercase text-muted" style="font-size: 10px;">
                                    {{ str_replace('_', ' ', $q->category) }}
                                </small>
                                <div class="mt-1"><span class="badge bg-label-info">{{ in_array($q->category, ['l1_penyelenggara', 'l1_narasumber']) ? ucfirst($q->metode ?: 'semua') : 'Semua metode' }}</span></div>
                                @if(str_starts_with($q->category, 'l34_'))
                                    <div class="mt-1"><span class="badge bg-label-primary">{{ $q->program_evaluasi === 'semua' ? 'Semua Program' : ($q->program_evaluasi ?: 'PKTI/PKTU') }}</span></div>
                                    <div class="mt-1"><span class="badge bg-label-warning">{{ $q->sub_category ?: 'Belum dikategorikan' }}</span></div>
                                @endif
                            </td>
                            <td class="align-top">
                                <div class="fw-bold text-dark mb-2 text-wrap" style="line-height: 1.4;">{{ $q->question_text }}</div>
                                
                                @if($q->type == 'slider')
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="range" class="form-range w-25" disabled>
                                        <span class="badge bg-label-secondary" style="font-size: 9px;">SKALA 10-100</span>
                                    </div>
                                @elseif(in_array($q->type, ['dropdown', 'checkbox']) && is_array($q->options))
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($q->options as $opt)
                                            <span class="badge bg-label-info" style="font-size: 9px;">
                                                @if($q->type === 'checkbox')<i class="bx bx-checkbox me-1"></i>@endif{{ $opt }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <small class="text-muted"><i class="bx bx-align-left me-1"></i>Input Teks Paragraf</small>
                                @endif
                            </td>
                            <td class="text-center align-top">
                                <div class="d-flex justify-content-center gap-1">
                                    @if($isSuperadmin)
                                    <button type="button"
                                            class="btn btn-xs btn-icon btn-outline-primary"
                                            title="Duplikat pertanyaan"
                                            onclick="duplicateQuestion({{ $q->id }}, {{ Illuminate\Support\Js::from($q->question_text) }}, {{ Illuminate\Support\Js::from($q->bidang) }})"
                                            data-bs-toggle="modal"
                                            data-bs-target="#duplicateQuestionModal">
                                        <i class="bx bx-copy"></i>
                                    </button>
                                    @endif
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
                <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada soal evaluasi (L1, L3, L4).</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalImportSoal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('questions.import') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <input type="hidden" name="bidang" value="{{ $selectedBidang }}">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Import Bank Soal (Excel)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Panduan Singkat -->
                <div class="alert alert-info py-2 shadow-none mb-3">
                    <small class="fw-bold d-block mb-1">ATURAN PENGISIAN EXCEL:</small>
                    <ul class="small mb-0 ps-3">
                        <li><strong>bidang</strong>: otomatis mengikuti bidang yang sedang dikelola.</li>
                        <li><strong>metode</strong>: klasikal, full learning, blended, atau semua.</li>
                        <li><strong>program_evaluasi</strong>: semua, CPNS, PKP, PKA, PKN, atau PKTI/PKTU.</li>
                        <li>CPNS, PKP, PKA, dan PKN hanya digunakan pada Bidang Pengembangan Kompetensi Manajerial.</li>
                        <li><strong>level_peran</strong>: Mandiri, Atasan, Rekan, Penyelenggara, Narasumber.</li>
                        <li><strong>tipe_jawaban</strong>: slider, dropdown, checkbox, atau text.</li>
                        <li><strong>pilihan_jawaban</strong>: Isi jika tipe dropdown/checkbox (pisahkan dengan koma).</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih File Excel</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required>
                </div>
            </div>
            <div class="modal-footer border-top">
                <a href="{{ route('questions.template', ['bidang' => $selectedBidang]) }}" class="btn btn-outline-secondary">
                    <i class="bx bx-download me-1"></i> Download Template
                </a>
                <button type="submit" class="btn btn-primary">Mulai Import</button>
            </div>
        </form>
    </div>
</div>

@if($isSuperadmin)
<div class="modal fade" id="duplicateQuestionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="duplicateQuestionForm" class="modal-content">
            @csrf
            <div class="modal-header border-bottom">
                <h5 class="modal-title"><i class="bx bx-copy me-1"></i>Duplikat Pertanyaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <small class="d-block mb-1">Pertanyaan yang akan disalin:</small>
                    <strong id="duplicateQuestionText"></strong>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Bidang</label>
                    <input type="text" id="duplicateSourceBidang" class="form-control bg-light" readonly>
                </div>
                <div class="alert alert-warning mb-0">
                    Salinan dibuat pada bidang yang sama. Gunakan duplikasi bundel untuk menyalin antarbidang.
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bx bx-copy me-1"></i>Duplikat Sekarang</button>
            </div>
        </form>
    </div>
</div>
@endif

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
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bidang</label>
                        @if($isSuperadmin)
                            <select name="bidang" id="edit_bidang" class="form-select">
                                @foreach($bidangOptions as $bidang)
                                    <option value="{{ $bidang }}">{{ $bidang }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="bidang" value="{{ Auth::user()->bidang }}">
                            <input type="text" class="form-control bg-light" value="{{ Auth::user()->bidang }}" readonly>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category" id="edit_category" class="form-select" onchange="syncMethodField('edit')">
                            <option value="l1_penyelenggara">L1 - Penyelenggara</option>
                            <option value="l1_narasumber">L1 - Narasumber</option>
                            <option value="l34_mandiri">L3 & L4 - Mandiri</option>
                            <option value="l34_rekan">L3 & L4 - Rekan Kerja</option>
                            <option value="l34_atasan">L3 & L4 - Atasan Langsung</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Metode Pelatihan</label>
                        <select name="metode" id="edit_metode" class="form-select">
                            <option value="semua" data-global="true">Semua Metode</option>
                            <option value="klasikal">Klasikal</option>
                            <option value="full learning">Full Learning</option>
                            <option value="blended">Blended Learning</option>
                        </select>
                        <div id="edit_method_help" class="form-text">Digunakan untuk evaluasi Level 1.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Program Evaluasi</label>
                        <select name="program_evaluasi" id="edit_program_evaluasi" class="form-select">
                            <option value="semua">Semua Program</option>
                            @foreach(['PKTI/PKTU', 'CPNS', 'PKP', 'PKA', 'PKN'] as $program)
                                <option value="{{ $program }}">{{ $program }}</option>
                            @endforeach
                        </select>
                        <div id="edit_program_help" class="form-text">Digunakan untuk evaluasi Level 3 & 4.</div>
                    </div>
                    <div class="col-md-6 mb-3" id="edit_subcategory_wrapper" style="display:none">
                        <label class="form-label">Bagian Evaluasi L3/L4</label>
                        <select name="sub_category" id="edit_sub_category" class="form-select" disabled>
                            <option value="Data Diri Alumni">1. Data Diri Alumni</option>
                            <option value="Penempatan Tugas dan Transfer Learning">2. Penempatan Tugas dan Transfer Learning</option>
                            <option value="Perubahan Perilaku">3. Perubahan Perilaku</option>
                            <option value="Dampak Pelatihan">4. Dampak Pelatihan</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipe</label>
                        <select name="type" id="edit_type" class="form-select" onchange="handleTypeChange(this, 'edit-options-wrapper')">
                            <option value="slider">Slider</option>
                            <option value="dropdown">Dropdown</option>
                            <option value="checkbox">Checkbox (Pilihan Ganda)</option>
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
@endif
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
        if (['dropdown', 'checkbox'].includes(select.value)) {
            $(`#${wrapperId}`).slideDown();
        } else {
            $(`#${wrapperId}`).slideUp();
        }
    }

    function syncMethodField(prefix) {
        const category = document.getElementById(prefix + '_category');
        const method = document.getElementById(prefix + '_metode');
        const help = document.getElementById(prefix + '_method_help');
        const subcategoryWrapper = document.getElementById(prefix + '_subcategory_wrapper');
        const subcategory = document.getElementById(prefix + '_sub_category');
        const program = document.getElementById(prefix + '_program_evaluasi');
        const programHelp = document.getElementById(prefix + '_program_help');
        if (!category || !method || !help) return;
        const isLevelOne = ['l1_penyelenggara', 'l1_narasumber'].includes(category.value);
        const isLevel34 = category.value.startsWith('l34_');

        method.disabled = !isLevelOne;
        if (!isLevelOne) method.value = 'semua';
        help.textContent = isLevelOne
            ? 'Pilih metode tertentu atau Semua Metode agar pertanyaan dapat digunakan bersama.'
            : 'Kategori ini otomatis berlaku untuk semua metode.';
        if (subcategoryWrapper && subcategory) {
            subcategoryWrapper.style.display = isLevel34 ? '' : 'none';
            subcategory.disabled = !isLevel34;
        }
        if (program) {
            program.disabled = false;
            if (!isLevel34) program.value = 'PKTI/PKTU';
        }
        if (programHelp) {
            programHelp.textContent = isLevel34
                ? 'Pilih program tertentu atau Semua Program agar pertanyaan digunakan bersama.'
                : 'Kategori Level 1 otomatis berlaku untuk semua program.';
        }
    }

    function editQuestion(data) {
        const url = "{{ url('questions') }}/" + data.id;
        $('#editForm').attr('action', url);
        $('#edit_bidang').val(data.bidang || data.training_type);
        $('#edit_category').val(data.category);
        $('#edit_metode').val(data.metode || 'semua');
        $('#edit_program_evaluasi').val(data.program_evaluasi || 'PKTI/PKTU');
        $('#edit_sub_category').val(data.sub_category || 'Data Diri Alumni');
        $('#edit_type').val(data.type);
        $('#edit_question_text').val(data.question_text);

        const container = $('#edit-options-wrapper .options-container');
        container.empty();

        if (['dropdown', 'checkbox'].includes(data.type)) {
            $('#edit-options-wrapper').show();
            if (data.options && data.options.length > 0) {
                data.options.forEach(opt => addOptionField('edit-options-wrapper', opt));
            } else {
                addOptionField('edit-options-wrapper');
            }
        } else {
            $('#edit-options-wrapper').hide();
        }
        syncMethodField('edit');
    }

    function duplicateQuestion(id, questionText, sourceBidang) {
        $('#duplicateQuestionForm').attr('action', "{{ url('questions') }}/" + id + '/duplicate');
        $('#duplicateQuestionText').text(questionText);
        $('#duplicateSourceBidang').val(sourceBidang);
    }

    function updateQuestionSelection() {
        const checkboxes = Array.from(document.querySelectorAll('.question-select'));
        const selected = checkboxes.filter(checkbox => checkbox.checked);
        const selectAll = document.getElementById('selectAllQuestions');
        const deleteButton = document.getElementById('deleteSelectedQuestions');
        const countBadge = document.getElementById('selectedQuestionCount');

        if (selectAll) {
            selectAll.checked = checkboxes.length > 0 && selected.length === checkboxes.length;
            selectAll.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
        }
        if (deleteButton) deleteButton.disabled = selected.length === 0;
        if (countBadge) countBadge.textContent = selected.length;

        checkboxes.forEach(checkbox => {
            checkbox.closest('.question-row')?.classList.toggle('table-active', checkbox.checked);
        });
    }

    function confirmSelectedQuestions() {
        const total = document.querySelectorAll('.question-select:checked').length;
        if (total === 0) return false;
        return confirm(`Hapus ${total} pertanyaan terpilih? Semua jawaban yang terkait juga akan dihapus permanen.`);
    }

    document.addEventListener('DOMContentLoaded', function() {
        syncMethodField('create');

        const selectAll = document.getElementById('selectAllQuestions');
        const checkboxes = document.querySelectorAll('.question-select');
        selectAll?.addEventListener('change', function() {
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
            updateQuestionSelection();
        });
        checkboxes.forEach(checkbox => checkbox.addEventListener('change', updateQuestionSelection));
        updateQuestionSelection();
    });
</script>
@endpush
