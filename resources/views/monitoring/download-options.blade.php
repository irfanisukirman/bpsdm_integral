@extends('layouts.master')

@section('title', 'Download Laporan Monitoring')

@section('content')
@php
    $documents = [
        'monitoring' => ['Laporan Monitoring', 'Ringkasan pelaksanaan monitoring, termasuk temuan dan rekomendasi.', 'Word', 'bx-file'],
        'tindaklanjut' => ['Laporan Tindak Lanjut', 'Rincian temuan dan status tindak lanjutnya.', 'Word', 'bx-task'],
        'ceklis' => ['Ceklis Monitoring', 'Rekap jawaban per indikator monitoring.', 'Excel', 'bx-spreadsheet'],
    ];
@endphp

<section class="training-hero rounded-4 p-4 p-md-5 mb-4">
    <a href="{{ route('monitoring.downloads') }}" class="btn btn-outline-secondary mb-3">
        <i class="bx bx-arrow-back me-1" aria-hidden="true"></i>Kembali ke Daftar Pelatihan
    </a>
    <h1 class="h3 fw-bold mb-2">Download Laporan Monitoring</h1>
    <p class="fs-5 fw-semibold text-break mb-2">{{ $training->nama_pelatihan }}</p>
    <div class="d-flex flex-wrap gap-3">
        @if(filled($training->bidang))
            <span><i class="bx bx-buildings me-1" aria-hidden="true"></i>{{ $training->bidang }}</span>
        @endif
        @if(filled($training->angkatan))
            <span><i class="bx bx-group me-1" aria-hidden="true"></i>Angkatan {{ $training->angkatan }}</span>
        @endif
    </div>
</section>

<section aria-labelledby="monitoring-documents">
    <h2 id="monitoring-documents" class="h5 mb-1">Pilih Dokumen</h2>
    <p class="text-muted mb-3">Periksa penandatangan sebelum mengunduh dokumen.</p>
    <div class="row g-3">
        @foreach($documents as $document => [$label, $description, $format, $icon])
            <div class="col-12 col-md-6 col-xl-4">
                <article class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                            <span class="badge bg-label-primary p-2"><i class="bx {{ $icon }} fs-4" aria-hidden="true"></i></span>
                            <span class="badge bg-label-secondary">{{ $format }}</span>
                        </div>
                        <h3 class="h5 mb-2">{{ $label }}</h3>
                        <p class="text-muted flex-grow-1">{{ $description }}</p>
                        <a href="{{ route('monitoring.report-settings', ['id' => $training->id, 'document' => $document]) }}"
                            class="btn btn-primary align-self-start" aria-label="Siapkan unduhan {{ $label }}">
                            <i class="bx bx-download me-1" aria-hidden="true"></i>Siapkan Unduhan
                        </a>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
</section>
@endsection
