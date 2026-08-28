@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">INTEGRAL /</span> Pengelolaan Dokumen
            @if(isset($currentBidang))
                <span class="text-primary small"> / {{ $currentBidang }}</span>
            @endif
        </h4>
        
        <div class="d-flex gap-2">
            @if(isset($currentBidang))
                @if(Auth::user()->role === 'superadmin' && !request()->query('folder'))
                    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Daftar Bidang
                    </a>
                @endif
                
                @if($currentFolder)
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadFile">
                        <i class="bx bx-upload me-1"></i> Upload File
                    </button>
                @endif
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFolder">
                    <i class="bx bx-folder-plus me-1"></i> Folder Baru
                </button>
            @endif
        </div>
    </div>
    

    {{-- KONDISI 1: LANDING PAGE SUPERADMIN (DAFTAR BIDANG) --}}
    @if(Auth::user()->role === 'superadmin' && !isset($currentBidang))
    <h5 class="mb-3">Folder Global (Terlihat di Semua Bidang)</h5>
        <div class="row g-4 mb-5">
            @foreach($globalFolders as $folder)
                <div class="col-md-3 col-6">
                    <div class="card shadow-none border text-center h-100 folder-card border-primary">
                        <div class="card-body position-relative">
                            {{-- Dropdown aksi hapus/privacy tetap sama --}}
                            <a href="{{ route('documents.index', ['folder' => $folder->id, 'bidang' => 'Semua Bidang']) }}" class="text-body d-block mt-2">
                                <i class="bx bxs-folder text-primary mb-2" style="font-size: 4rem;"></i>
                                <h6 class="fw-bold mb-1">{{ $folder->name }}</h6>
                                <span class="badge bg-label-primary btn-xs">GLOBAL</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="col-md-3 col-6">
                <div class="card shadow-none border border-dashed text-center h-100" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#addFolder">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <i class="bx bx-plus-circle display-4 text-muted"></i>
                        <p class="text-muted mb-0">Tambah Folder Global</p>
                    </div>
                </div>
            </div>
        </div>
        <h5 class="mb-3">Daftar Dokumen Per Bidang</h5>
        <div class="row g-4">
            @foreach($listBidang as $b)
            <div class="col-md-4 col-lg-3">
                <div class="card shadow-none border text-center h-100 folder-card">
                    <div class="card-body py-5">
                        <div class="avatar avatar-xl bg-label-primary mx-auto mb-3">
                            <i class="bx bxs-briefcase"></i>
                        </div>
                        <h6 class="fw-bold mb-3">{{ $b->bidang }}</h6>
                        <a href="{{ route('documents.index', ['bidang' => $b->bidang]) }}" class="btn btn-sm btn-primary">
                            Buka Dokumen
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    {{-- KONDISI 2: FILE MANAGER --}}
    @else
        <!-- Breadcrumbs Modern -->
        <div class="card mb-4 shadow-none border bg-light">
            <div class="card-body py-2 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        @if(Auth::user()->role === 'superadmin')
                            <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Daftar Bidang</a></li>
                        @endif
                        <li class="breadcrumb-item">
                            <a href="{{ route('documents.index', ['bidang' => $currentBidang]) }}">Root</a>
                        </li>
                        @if($currentFolder)
                            @if($currentFolder->parent)
                                <li class="breadcrumb-item"><a href="{{ route('documents.index', ['folder' => $currentFolder->parent->id, 'bidang' => $currentBidang]) }}">...</a></li>
                            @endif
                            <li class="breadcrumb-item active text-primary fw-bold">{{ $currentFolder->name }}</li>
                        @endif
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Grid Folder -->
        <div class="row g-4 mb-5">
            @forelse($folders as $folder)
            <div class="col-md-3 col-6">
                <div class="card shadow-none border text-center h-100 folder-card position-relative">
                    <div class="card-body">
                        @php
                            // Logika Izin: 
                            // 1. Jika dia Superadmin, dia bisa melakukan apa saja.
                            // 2. Jika dia Admin Bidang, dia hanya bisa ubah/hapus foldernya sendiri.
                            $canManage = (Auth::user()->role === 'superadmin') || ($folder->user_id === Auth::id());
                        @endphp
                        <div class="dropdown position-absolute end-0 top-0 me-2 mt-2">
                            <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                @if($canManage)
                                    <li>
                                         <form action="{{ route('documents.folder.privacy', $folder->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mengatur folder ini menjadi {{ $folder->is_public ? 'Private' : 'Public' }}? \n\nPERINGATAN: Seluruh file dan sub-folder di dalamnya akan ikut menjadi {{ $folder->is_public ? 'Private' : 'Public' }}!')">
                                         @csrf @method('PUT')
                                            <button class="dropdown-item">
                                                <i class="bx {{ $folder->is_public ? 'bx-lock text-danger' : 'bx-globe text-success' }} me-2"></i>
                                                  Set {{ $folder->is_public ? 'Private' : 'Public' }}
                                            </button>
                                        </form>
                                    </li>
                                @endif
                                @if($folder->is_public)
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" 
                                        onclick="copyShareLink('{{ route('documents.public', $folder->share_token) }}')">
                                            <i class="bx bx-link me-2 text-info"></i> Copy Link Publik
                                        </a>
                                    </li>
                                @endif
                                @if($canManage)
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('documents.folder.destroy', $folder->id) }}" method="POST" onsubmit="return confirm('Hapus folder dan seluruh isinya?')">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="bx bx-trash me-2"></i> Hapus Folder</button>
                                        </form>
                                    </li>
                                @else
                                    {{-- Opsional: Berikan info jika tidak bisa menghapus --}}
                                    <li><a class="dropdown-item disabled" href="javascript:void(0)"><i class="bx bx-info-circle me-2"></i> Folder Read-Only</a></li>
                                @endif
                            </ul>
                        </div>
                        <a href="{{ route('documents.index', ['folder' => $folder->id, 'bidang' => $currentBidang]) }}" class="text-body d-block mt-2">
                            <i class="bx bxs-folder {{ $folder->bidang == 'Semua Bidang' ? 'text-primary' : 'text-warning' }} mb-2 animate-folder" style="font-size: 4.5rem;"></i>
                            <h6 class="fw-bold mb-1 text-truncate px-2">{{ $folder->name }}</h6>
                            @if($folder->bidang == 'Semua Bidang')
                                <span class="badge bg-primary btn-xs">GLOBAL</span>
                                <small class="text-muted d-block" style="font-size: 10px;">Dibuat oleh: {{ $folder->user->name }}</small>
                            @else
                                <span class="badge {{ $folder->is_public ? 'bg-label-success' : 'bg-label-secondary' }} btn-xs">
                                    {{ $folder->is_public ? '🌐 Public' : '🔒 Private' }}
                                </span>
                                <small class="text-muted d-block" style="font-size: 10px;">Dibuat oleh: {{ $folder->user->name }}</small>
                            @endif
                        </a>
                    </div>
                </div>
            </div>
            @empty
                @if($files->isEmpty())
                <div class="col-12 text-center py-5">
                    <img src="{{ asset('assets/img/illustrations/empty-folder.png') }}" alt="empty" width="120" class="opacity-50 mb-3">
                    <p class="text-muted">Folder ini masih kosong.</p>
                </div>
                @endif
            @endforelse
        </div>

        <!-- Tabel File -->
        @if($files->count() > 0)
        <div class="card shadow-sm border">
            <div class="card-header border-bottom py-3">
                <h6 class="m-0 fw-bold"><i class="bx bx-file me-2"></i>Daftar Berkas Dokumen</h6>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nama File</th>
                            <th>Ukuran</th>
                            <th>Diunggah Pada</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($files as $file)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bx {{ getFileIcon($file->file_type) }} h4 mb-0 me-2 text-primary"></i>
                                    <span class="fw-semibold">{{ $file->display_name }}</span>
                                </div>
                            </td>
                            
                            <td><small class="text-muted">{{ formatSizeUnits($file->file_size) }}</small></td>
                            <td><small>{{ $file->created_at->format('d M Y, H:i') }}</small></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank" class="btn btn-sm btn-icon btn-outline-primary" title="Download">
                                        <i class="bx bx-download"></i>
                                    </a>
                                    <form action="{{ route('documents.file.destroy', $file->id) }}" method="POST" onsubmit="return confirm('Hapus file ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    @endif
</div>

<!-- Toast Container (Popup Notifikasi) -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="copyToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bx bx-check-circle me-2"></i> Tautan folder berhasil disalin ke papan klip!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Modal Folder -->
<div class="modal fade" id="addFolder" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <form action="{{ route('documents.folder.create') }}" method="POST" class="modal-content">
            @csrf
            {{-- ID Folder Induk --}}
            <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? '' }}">
            
            {{-- LOGIKA BIDANG: 
                 Jika sedang di dalam Bidang tertentu, kirim nama bidangnya.
                 Jika sedang di landing page Superadmin, kirim 'Semua Bidang'. --}}
            <input type="hidden" name="bidang" value="{{ $currentBidang ?? 'Semua Bidang' }}">
            
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Buat Folder {{ !isset($currentBidang) ? 'Global' : '' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <div class="mb-0">
                    <label class="form-label fw-bold">Nama Folder</label>
                    <input type="text" name="name" class="form-control" placeholder="Masukkan nama folder..." required autofocus>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Buat Folder</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal fade" id="uploadFile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? '' }}">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Upload Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih Berkas</label>
                    <input type="file" name="attachments[]" class="form-control" multiple required>
                    <div class="form-text small">Anda dapat memilih lebih dari satu file secara bersamaan.</div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success btn-sm">Mulai Upload</button>
            </div>
        </form>
    </div>
</div>

<style>
    .folder-card {
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    .folder-card:hover {
        transform: translateY(-5px);
        border-color: #696cff !important;
        box-shadow: 0 4px 15px rgba(105, 108, 255, 0.1) !important;
    }
    .folder-card:hover .animate-folder {
        transform: scale(1.1);
        transition: transform 0.2s ease;
    }
    .breadcrumb-item + .breadcrumb-item::before {
        content: "\ebdf";
        font-family: 'boxicons';
        font-size: 12px;
        color: #a1acb8;
    }
</style>

@endsection

@push('js')
<script>

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            timer: 3000,
            showConfirmButton: false
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: "{{ session('error') }}",
        });
    @endif
/**
 * Fungsi Copy Link dengan Bootstrap Toast (Popup)
 */
function copyShareLink(url) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(() => {
            showToast();
        });
    } else {
        var textArea = document.createElement("textarea");
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            showToast();
        } catch (err) {
            console.error('Gagal menyalin link');
        }
        document.body.removeChild(textArea);
    }
}

function showToast() {
    var toastEl = document.getElementById('copyToast');
    var toast = new bootstrap.Toast(toastEl);
    toast.show();
}
</script>
@endpush

@php
/**
 * Helper internal untuk icon file
 */
function getFileIcon($ext) {
    $ext = strtolower($ext);
    if(in_array($ext, ['jpg','jpeg','png','gif'])) return 'bx-image-alt';
    if(in_array($ext, ['pdf'])) return 'bxs-file-pdf text-danger';
    if(in_array($ext, ['doc','docx'])) return 'bxs-file-doc text-primary';
    if(in_array($ext, ['xls','xlsx'])) return 'bxs-file-blank text-success';
    if(in_array($ext, ['zip','rar'])) return 'bx-archive';
    return 'bx-file';
}

/**
 * Helper internal untuk format size
 */
function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) { $bytes = number_format($bytes / 1073741824, 2) . ' GB'; }
    elseif ($bytes >= 1048576) { $bytes = number_format($bytes / 1048576, 2) . ' MB'; }
    elseif ($bytes >= 1024) { $bytes = number_format($bytes / 1024, 2) . ' KB'; }
    elseif ($bytes > 1) { $bytes = $bytes . ' bytes'; }
    elseif ($bytes == 1) { $bytes = $bytes . ' byte'; }
    else { $bytes = '0 bytes'; }
    return $bytes;
}
@endphp