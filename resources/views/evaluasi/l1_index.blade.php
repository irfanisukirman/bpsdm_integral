@extends('layouts.master')

@section('title', 'Evaluasi Level 1')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Evaluasi /</span> Level 1: Reaksi ({{ $training->nama_pelatihan }})
        </h4>
        <div class="d-flex gap-2">
            <a href="{{ route('trainings.manage', $training->id) }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali ke Pengelolaan
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateL1">
                <i class="bx bx-plus me-1"></i> Buat Form Evaluasi
            </button>
        </div>
    </div>

    <!-- Status Pelatihan sesuai Gambar -->
    <div class="card bg-label-primary border-0 shadow-none mb-4">
        <div class="card-body d-flex align-items-center justify-content-between py-3">
            <div>
                <h5 class="mb-1 text-primary text-uppercase fw-bold" style="letter-spacing: 1px;">Status Pelatihan</h5>
                <p class="mb-0 text-dark">Angkatan: <strong>{{ $training->angkatan }}</strong> | Target Peserta: <strong>{{ $training->jumlah_peserta }} Orang</strong></p>
            </div>
            <div class="avatar flex-shrink-0">
                <span class="avatar-initial rounded bg-primary"><i class="bx bx-line-chart h3 mb-0"></i></span>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Form -->
    <div class="card">
        <h5 class="card-header pb-3">Daftar Formulir Evaluasi (Level 1)</h5>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="py-3">NAMA FORM EVALUASI</th>
                        <th class="py-3">INFORMASI OBJEK</th>
                        <th class="py-3">STATUS PENGISIAN</th>
                        <th class="py-3">AKSI</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($forms as $form)
                    @php
                        // Hitung jumlah pengisi
                        $count = \App\Models\EvaluationResultL1::where('training_id', $training->id)
                                    ->where('schedule_id', $form->schedule_id)
                                    ->distinct('participant_id')
                                    ->count();
                        $percent = ($training->jumlah_peserta > 0) ? round(($count / $training->jumlah_peserta) * 100) : 0;
                        
                        // Link Publik
                        $publicLink = route('public.evall1.form', ['training_id' => $training->id, 'type' => $form->type, 'sid' => $form->schedule_id]);
                    @endphp
                    <tr>
                        <td class="fw-bold">{{ $form->name }}</td>
                        <td>
                            @if($form->type == 'narasumber')
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">{{ $form->schedule?->pengajar?->name ?? $form->target_name }}</span>
                                    <small class="text-muted">{{ $form->materi }}</small>
                                </div>
                            @else
                                <small class="text-muted">Instansi: {{ $form->target_name }}</small>
                            @endif
                        </td>
                        <td style="width: 250px;">
                            <div class="d-flex align-items-center">
                                <div class="progress w-100 me-3" style="height: 8px; background-color: #eee;">
                                    <div class="progress-bar bg-primary" style="width: {{ $percent }}%"></div>
                                </div>
                                <span class="small fw-bold text-muted">{{ $count }}/{{ $training->jumlah_peserta }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="copyLink('{{ $publicLink }}')">
                                        <i class="bx bx-copy me-1"></i> Copy Link Publik
                                    </a>
                                    <a class="dropdown-item" href="{{ route('evall1.progres', ['id' => $training->id, 'sid' => $form->schedule_id ?? 'null']) }}">
                                        <i class="bx bx-group me-1"></i> Lihat Progres
                                    </a>
                                    
                                    <!-- TOMBOL DOWNLOAD EXCEL (TAMBAHKAN INI) -->
                                    <a class="dropdown-item text-success" href="{{ route('evall1.export', $form->id) }}">
                                        <i class="bx bxs-file-export me-1"></i> Download Excel
                                    </a>

                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('evall1.destroyForm', $form->id) }}" method="POST" onsubmit="return confirm('Hapus form?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger"><i class="bx bx-trash me-1"></i> Hapus Form</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-4">Belum ada form evaluasi yang dibuat. Klik "Buat Form" di atas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL BUAT FORM -->
<div class="modal fade" id="modalCreateL1" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('evall1.storeForm', $training->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Buat Formulir Evaluasi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">NAMA FORM EVALUASI</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Evaluasi Materi Kebijakan" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">PILIH JENIS EVALUASI</label>
                    <select name="type" class="form-select" id="select-type-l1" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="penyelenggara">Evaluasi Penyelenggara</option>
                        <option value="narasumber">Evaluasi Narasumber</option>
                    </select>
                </div>

                <div id="field-penyelenggara" style="display:none">
                    <div class="mb-3">
                        <label class="form-label">INSTANSI PENYELENGGARA</label>
                        <input type="text" name="instansi_penyelenggara" class="form-control" value="{{ $training->bidang }}">
                    </div>
                </div>

                <div id="field-narasumber" style="display:none">
                    <div class="mb-3">
                        <label class="form-label fw-bold">PILIH SESI JADWAL</label>
                        <select name="schedule_id" class="form-select" id="schedule-select">
                            <option value="">-- Pilih Sesi --</option>
                            @forelse($training->schedules as $s)
                                <option value="{{ $s->id }}"
                                    data-narsum="{{ $s->pengajar->name }}"
                                    data-materi="{{ $s->activity }}"
                                    {{-- Pastikan format tanggal sudah benar --}}
                                    data-tgl="{{ \Carbon\Carbon::parse($s->date)->translatedFormat('d F Y') }}">
                                    {{ $s->activity }} - {{ $s->pengajar->name }}
                                </option>
                            @empty
                                <option value="" disabled>Belum ada sesi yang memiliki tenaga pengajar</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="mb-3 p-3 bg-light rounded border border-dashed">
                        <div class="mb-2">
                            <small class="text-muted d-block">Nama Pengajar</small>
                            <input type="text" id="display-narsum" class="form-control form-control-sm border-0 bg-transparent fw-bold p-0" readonly>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Materi Ajar</small>
                            <input type="text" id="display-materi" class="form-control form-control-sm border-0 bg-transparent fw-bold p-0" readonly>
                        </div>
                        {{-- TAMBAHKAN INI --}}
                        <div>
                            <small class="text-muted d-block">Tanggal Materi</small>
                            <input type="text" id="display-tgl" class="form-control form-control-sm border-0 bg-transparent fw-bold p-0" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Form</button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function() {
        $('#select-type-l1').on('change', function() {
            const val = $(this).val();
            if(val === 'narasumber') {
                $('#field-narasumber').slideDown();
                $('#field-penyelenggara').hide();
                $('#schedule-select').prop('required', true);
            } else if(val === 'penyelenggara') {
                $('#field-penyelenggara').slideDown();
                $('#field-narasumber').hide();
                $('#schedule-select').prop('required', false).val('').trigger('change');
            } else {
                $('#field-penyelenggara, #field-narasumber').hide();
                $('#schedule-select').prop('required', false);
            }
        });

        $('#schedule-select').on('change', function() {
            const sel = $(this).find(':selected');
            $('#display-narsum').val(sel.data('narsum') || '-');
            $('#display-materi').val(sel.data('materi') || '-');
            $('#display-tgl').val(sel.data('tgl') || '-'); // TAMBAHKAN INI
        });

        window.copyLink = function(text) {
            navigator.clipboard.writeText(text).then(() => alert("Link Berhasil Disalin!"));
        }
    });
</script>
@endpush
@endsection
