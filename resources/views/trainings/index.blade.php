@extends('layouts.master')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-3 mb-0">Daftar Pelatihan</h4>
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bx bx-plus me-1"></i> Buat Pelatihan
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('trainings.create', ['model' => 'standar']) }}">Model Standar</a></li>
            <li><a class="dropdown-item" href="{{ route('trainings.create', ['model' => 'blended']) }}">Model Blended Learning</a></li>
        </ul>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Pelatihan</th>
                    <th>Bidang / Model</th>
                    <th>Tanggal</th>
                    <th>Status & Kegiatan</th> {{-- Judul Kolom Diperbarui --}}
                    <th width="50">Aksi</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach($trainings as $t)
                <tr>
                    <td>
                        <span class="fw-bold text-dark">{{ $t->nama_pelatihan }}</span><br>
                        <small class="text-muted">Angkatan {{ $t->angkatan }} - {{ $t->lokasi }}</small>
                        
                        {{-- INFO KEGIATAN BERJALAN DENGAN PERBAIKAN WRAP TEXT --}}
                        @php $current = $t->current_activity; @endphp
                        @if($current)
                            <div class="mt-2" style="max-width: 450px;"> {{-- Batasi lebar maksimal agar tidak mepet ke kanan --}}
                                <span class="badge bg-label-secondary text-wrap d-inline-block animate__animated animate__pulse animate__infinite" 
                                    style="font-size: 11px; line-height: 1.5; text-align: left; white-space: normal;">
                                    <i class="bx bx-play-circle me-1 text-success"></i> 
                                    <span class="text-uppercase">Sedang berlangsung:</span> 
                                    <strong class="text-dark">{{ $current->activity }}</strong> 
                                    <span class="text-muted">({{ substr($current->start_time, 0, 5) }} - {{ substr($current->end_time, 0, 5) }})</span>
                                </span>
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-label-info mb-1">{{ $t->bidang }}</span><br>
                        <small class="text-uppercase fw-bold text-muted">{{ $t->model }}</small>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <small><i class="bx bx-calendar-event me-1 text-primary"></i>{{ \Carbon\Carbon::parse($t->tgl_mulai)->format('d M Y') }}</small>
                            <small><i class="bx bx-calendar-check me-1 text-danger"></i>{{ \Carbon\Carbon::parse($t->tgl_selesai)->format('d M Y') }}</small>
                        </div>
                    </td>
                    <td>
                        @php
                            $sisa = $t->sisa_hari;
                        @endphp

                        @if($sisa < 0)
                            <span class="badge bg-label-danger">
                                <i class="bx bx-check-double me-1"></i> Pelatihan Selesai
                            </span>
                        @elseif($sisa == 0)
                            <span class="badge bg-label-warning animate__animated animate__flash animate__infinite">
                                <i class="bx bx-timer me-1"></i> Hari Terakhir
                            </span>
                        @elseif($sisa <= 7)
                            <span class="badge bg-label-warning">
                                <i class="bx bx-time-five me-1"></i> {{ $sisa }} Hari Tersisa
                            </span>
                        @else
                            <span class="badge bg-label-success">
                                <i class="bx bx-play-circle me-1"></i> Berjalan
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item text-warning" href="{{ route('trainings.edit', $t->id) }}">
                                    <i class="bx bx-edit-alt me-1"></i> Edit Pelatihan
                                </a>
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header small text-muted">Manajemen Data</h6>
                                <a class="dropdown-item" href="{{ route('trainings.schedules', $t->id) }}">
                                    <i class="bx bx-calendar me-1"></i> Buat Jadwal
                                </a>
                                <a class="dropdown-item" href="{{ route('trainings.participants', $t->id) }}">
                                    <i class="bx bx-group me-1"></i> Lihat Peserta {{ $t->participants_count }}
                                </a>
                                <a class="dropdown-item" href="{{ route('attendance.index', $t->id) }}">
                                    <i class="bx bx-user-check me-1"></i> Kehadiran / Absensi
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalLms{{ $t->id }}">
                                    <i class="bx bx-link-external me-1"></i> Input Link LMS
                                </a>
                                <a class="dropdown-item text-primary fw-bold" href="javascript:void(0);" 
                                    onclick="copyInvitationCode('{{ $t->invitation_code }}', this)">
                                    <i class="bx bx-copy-alt me-1"></i> Copy Kode: <span class="text-dark">{{ $t->invitation_code }}</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header small text-muted">Monitoring & Evaluasi</h6>
                                <a class="dropdown-item" href="{{ route('monitoring.fill', $t->id) }}">
                                    <i class="bx bx-desktop me-1 text-info"></i> Monitoring
                                </a>
                                <a class="dropdown-item" href="{{ route('evall1.index', $t->id) }}">
                                    <i class="bx bx-smile me-1 text-warning"></i> Level 1: Reaksi
                                </a>
                                <a class="dropdown-item text-success" href="{{ route('trainings.export_evaluation', $t->id) }}">
                                    <i class="bx bxs-file-export me-1"></i> Download Hasil L1 & L2
                                </a>
                                <a class="dropdown-item" href="{{ route('evall2.index', $t->id) }}">
                                    <i class="bx bx-book-open me-1 text-success"></i> Level 2: Learning
                                </a>
                                <a class="dropdown-item" href="{{ route('evall34.index', $t->id) }}">
                                    <i class="bx bx-trending-up me-1 text-primary"></i> Level 3 & 4: Dampak
                                </a>
                                <a class="dropdown-item text-primary fw-bold" href="{{ route('evall34.export', $t->id) }}">
                                    <i class="bx bxs-spreadsheet me-1"></i> Download Laporan L3 & L4
                                </a>
                                <a class="dropdown-item text-info" href="{{ route('evall34.export_word', $t->id) }}">
                                    <i class="bx bx-file me-1"></i> Download Laporan Akhir (Word)
                                </a>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('trainings.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Hapus pelatihan ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bx bx-trash me-1"></i> Hapus Pelatihan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>


@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>
<script>
    
/**
 * Fungsi untuk menyalin kode undangan dan menampilkan notifikasi
 */
function copyInvitationCode(code, element) {
    // 1. Proses Salin ke Clipboard
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(code).then(() => {
            showSuccessNotify(code, element);
        });
    } else {
        // Fallback untuk browser lama atau koneksi non-HTTPs
        var textArea = document.createElement("textarea");
        textArea.value = code;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            showSuccessNotify(code, element);
        } catch (err) {
            console.error('Gagal menyalin kode');
        }
        document.body.removeChild(textArea);
    }
}

/**
 * Fungsi untuk menampilkan feedback visual
 */
function showSuccessNotify(code, el) {
    // 1. Feedback pada teks menu (agar user tahu tombol bekerja)
    const originalHTML = el.innerHTML;
    el.innerHTML = '<i class="bx bx-check text-success me-1"></i> <span class="text-success">Tersalin!</span>';
    
    // 2. Notifikasi SweetAlert (Pastikan penulisan S-nya besar: Swal)
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Kode ' + code + ' disalin ke clipboard.',
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            timerProgressBar: true
        });
    } else {
        // Jika SweetAlert gagal dimuat, gunakan alert biasa agar tidak macet
        console.error('SweetAlert2 tidak ditemukan!');
    }

    // Kembalikan teks menu ke semula
    setTimeout(() => {
        el.innerHTML = originalHTML;
    }, 2000);
}
</script>
@endpush

@push('css')
<style>
    /* Mengatur tumpukan agar di atas navbar */
    .swal2-container {
        z-index: 9999 !important;
    }
    /* Memberikan jarak dari atas agar tidak tertutup navbar (sekitar 75px) */
    .swal2-toast {
        margin-top: 75px !important; 
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>
@endpush

@foreach($trainings as $t)
<div class="modal fade" id="modalLms{{ $t->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('trainings.set_lms', $t->id) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Pengaturan Link LMS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">URL Learning Management System (LMS)</label>
                    <input type="url" name="link_lms" class="form-control" placeholder="https://lms.bpsdm.jabarprov.go.id/..." value="{{ $t->link_lms }}" required>
                    <div class="form-text">Link ini akan muncul di dashboard peserta sebagai tombol akses pelatihan.</div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Link</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection


