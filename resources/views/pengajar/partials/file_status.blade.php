@php
    $fileExists = $path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path);
    $storedFileName = $path ? basename($path) : null;
    $fileName = $path
        ? (($label ?? 'Dokumen') . '.' . strtolower(pathinfo($path, PATHINFO_EXTENSION)))
        : null;
    $fileSize = $fileExists ? \Illuminate\Support\Facades\Storage::disk('public')->size($path) : 0;
    $formattedSize = $fileSize >= 1048576
        ? number_format($fileSize / 1048576, 1) . ' MB'
        : number_format($fileSize / 1024, 0) . ' KB';
@endphp

@if($fileExists)
    <div class="document-file mt-2 p-2 rounded border border-success bg-label-success">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center min-w-0">
                <i class="bx bxs-file me-2 text-success fs-4"></i>
                <div class="min-w-0">
                    <div class="fw-semibold text-dark text-truncate" title="File tersimpan: {{ $storedFileName }}">{{ $fileName }}</div>
                    <small class="text-success"><i class="bx bx-check-circle me-1"></i>Tersedia · {{ $formattedSize }}</small>
                </div>
            </div>
            <div class="d-flex gap-1">
                <a href="{{ asset('storage/' . $path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" title="Lihat file">
                    <i class="bx bx-show"></i><span class="d-none d-sm-inline ms-1">Lihat</span>
                </a>
                <a href="{{ asset('storage/' . $path) }}" download class="btn btn-sm btn-outline-success" title="Unduh file">
                    <i class="bx bx-download"></i><span class="d-none d-sm-inline ms-1">Unduh</span>
                </a>
            </div>
        </div>
    </div>
@elseif($path)
    <div class="alert alert-warning py-2 px-3 mt-2 mb-0 small">
        <i class="bx bx-error-circle me-1"></i>
        Data upload tercatat, tetapi file tidak ditemukan. Silakan upload ulang.
    </div>
@else
    <div class="text-danger small mt-1"><i class="bx bx-x-circle me-1"></i>Belum diunggah</div>
@endif
