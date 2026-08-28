@extends('layouts.auth') {{-- Menggunakan layout bersih tanpa sidebar --}}

@section('content')
<div class="container-xxl py-5">
    <div class="row justify-content-center">
        <div class="col-md-11 col-lg-9">
            
            {{-- Breadcrumb Sederhana (Jika ini sub-folder dari suatu folder) --}}
            @if($folder->parent)
                <div class="mb-3">
                    <a href="{{ route('documents.public', $folder->parent->share_token ?? '') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                        <i class="bx bx-arrow-back me-1"></i> Kembali ke {{ $folder->parent->name }}
                    </a>
                </div>
            @endif

            <div class="card shadow-lg border-0 overflow-hidden">
                <!-- Header Dokumen -->
                <div class="card-header {{ $folder->is_public ? 'bg-primary' : 'bg-danger' }} text-white py-4 position-relative">
                    <div class="d-flex justify-content-between align-items-center position-relative" style="z-index: 2;">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3 bg-white p-1 rounded">
                                <img src="https://res.cloudinary.com/dnwyqw6gn/image/upload/v1786770700/Integral_1_ykmzxx.png" alt="Logo Integral">
                            </div>
                            <div>
                                <h4 class="text-white mb-0 fw-bold">
                                    {{ $folder->is_public ? $folder->name : 'Akses Dibatasi' }}
                                </h4>
                                <small class="opacity-75 text-uppercase" style="letter-spacing: 1px;">
                                    INTEGRAL Document Delivery System
                                </small>
                            </div>
                        </div>
                        <div class="text-end d-none d-sm-block">
                            <span class="badge bg-white text-primary fw-bold">
                                <i class="bx {{ $folder->is_public ? 'bx-share-alt' : 'bx-lock-alt' }} me-1"></i>
                                {{ $folder->is_public ? 'Folder Publik' : 'Terproteksi' }}
                            </span>
                        </div>
                    </div>
                    <div class="header-shape"></div>
                </div>

                <div class="card-body p-4 bg-white">
                    @if($folder->is_public)
                        
                        {{-- MENAMPILKAN SUB-FOLDER JIKA ADA --}}
                        @if($folder->children->count() > 0)
                            <h6 class="mb-3 text-dark fw-bold border-bottom pb-2"><i class="bx bx-folder-open me-2 text-warning"></i>Sub-Folder</h6>
                            <div class="row g-3 mb-5">
                                @foreach($folder->children->where('is_public', true) as $sub)
                                <div class="col-md-4 col-sm-6">
                                    <a href="{{ route('documents.public', $sub->share_token) }}" class="text-decoration-none">
                                        <div class="card shadow-sm border folder-card-public h-100">
                                            <div class="card-body p-3 d-flex align-items-center">
                                                <i class="bx bxs-folder text-warning fs-1 me-3"></i>
                                                <div>
                                                    <h6 class="mb-0 text-dark fw-bold text-truncate" style="max-width: 140px;" title="{{ $sub->name }}">{{ $sub->name }}</h6>
                                                    <small class="text-muted">{{ $sub->files->count() }} Berkas</small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- MENAMPILKAN FILE --}}
                        <div class="row mb-3 align-items-center mt-2">
                            <div class="col-md-8">
                                <h6 class="mb-0 text-dark fw-bold border-bottom pb-2"><i class="bx bx-file-blank me-2 text-primary"></i>Daftar Berkas Dokumen</h6>
                            </div>
                        </div>

                        <div class="table-responsive border rounded">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50%;">Nama Berkas</th>
                                        <th>Ukuran</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($folder->files as $file)
                                    <tr class="file-row">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="file-icon-box me-3">
                                                    <i class="bx {{ getFileIcon($file->file_type) }} h3 mb-0"></i>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-dark text-truncate" style="max-width: 250px;" title="{{ $file->display_name }}">
                                                        {{ $file->display_name }}
                                                    </span>
                                                    <small class="text-muted text-uppercase" style="font-size: 10px;">{{ $file->file_type }} File</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-secondary rounded-pill">
                                                {{ formatSizeUnits($file->file_size) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                @if(in_array(strtolower($file->file_type), ['pdf', 'jpg', 'jpeg', 'png']))
                                                    <a href="{{ asset('storage/'.$file->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Lihat Berkas">
                                                        <i class="bx bx-show-alt"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ asset('storage/'.$file->file_path) }}" download="{{ $file->display_name }}" class="btn btn-sm btn-primary" title="Unduh Berkas">
                                                    <i class="bx bx-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5">
                                            <img src="{{ asset('assets/img/illustrations/empty-folder.png') }}" width="100" class="mb-3 opacity-50">
                                            <p class="text-muted">Tidak ada dokumen di folder ini.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        {{-- TAMPILAN JIKA FOLDER PRIVATE --}}
                        <div class="text-center py-5 animate__animated animate__headShake">
                            <div class="lock-container mb-4">
                                <i class='bx bxs-lock-open text-danger lock-icon-main'></i>
                                <i class='bx bxs-shield-x text-danger opacity-25 position-absolute' style="font-size: 10rem; top: 10%; left: 40%;"></i>
                            </div>
                            <h2 class="fw-bold text-dark mt-3">Tidak Memiliki Izin Akses</h2>
                            <p class="text-muted fs-5 px-lg-5">
                                Tautan dokumen ini telah dinonaktifkan atau diubah menjadi <strong>Privat</strong> oleh pemilik folder.
                            </p>
                            <div class="alert bg-label-danger d-inline-block border-0 px-4">
                                <i class="bx bx-info-circle me-1"></i> Hubungi <strong>Bidang Penyelenggara</strong> terkait untuk mendapatkan akses dokumen kembali.
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card-footer bg-light text-center py-3 border-top">
                    <small class="text-muted fw-semibold">
                         &copy; {{ date('Y') }} INTEGRAL - BPSDM Provinsi Jawa Barat
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #f5f5f9; background-image: radial-gradient(#dcdcff 0.5px, #f5f5f9 0.5px); background-size: 20px 20px; }
    
    .header-shape {
        position: absolute; top: 0; right: 0; bottom: 0; left: 0;
        background: linear-gradient(120deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        clip-path: polygon(70% 0, 100% 0, 100% 100%, 40% 100%);
        z-index: 1;
    }

    .file-row { transition: all 0.2s; }
    .file-row:hover { background-color: #f8f9ff !important; transform: scale(1.01); }

    .folder-card-public { transition: all 0.2s ease-in-out; }
    .folder-card-public:hover { transform: translateY(-3px); border-color: #ffab00 !important; box-shadow: 0 4px 15px rgba(255, 171, 0, 0.15) !important; }

    .file-icon-box {
        width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;
        background-color: #f0f2ff; border-radius: 10px; color: #696cff;
    }

    .lock-icon-main { font-size: 8rem; filter: drop-shadow(0 10px 15px rgba(255, 62, 29, 0.3)); position: relative; z-index: 2; }
    .btn-group .btn { padding: 0.4375rem 0.75rem; }
    .text-danger { color: #ff3e1d !important; }
    .text-primary { color: #696cff !important; }
    .text-success { color: #71dd37 !important; }
</style>
@endsection

@php
function getFileIcon($ext) {
    $ext = strtolower($ext);
    if(in_array($ext, ['jpg','jpeg','png','gif'])) return 'bx-image-alt text-success';
    if(in_array($ext, ['pdf'])) return 'bxs-file-pdf text-danger';
    if(in_array($ext, ['doc','docx'])) return 'bxs-file-doc text-primary';
    if(in_array($ext, ['xls','xlsx'])) return 'bxs-file-blank text-success';
    if(in_array($ext, ['zip','rar'])) return 'bx-archive text-warning';
    return 'bx-file text-secondary';
}

function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) { $bytes = number_format($bytes / 1073741824, 2) . ' GB'; }
    elseif ($bytes >= 1048576) { $bytes = number_format($bytes / 1048576, 2) . ' MB'; }
    elseif ($bytes >= 1024) { $bytes = number_format($bytes / 1024, 2) . ' KB'; }
    else { $bytes = $bytes . ' bytes'; }
    return $bytes;
}
@endphp