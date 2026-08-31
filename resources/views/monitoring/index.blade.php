@extends('layouts.master')

@section('title', 'Monitoring Pelatihan')

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Monev /</span> Monitoring Penyelenggaraan
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Pelatihan terpantau</h5>
        <small class="text-muted">Kelola instrumen monitoring per pelatihan</small>
        <a href="{{ route('trainings.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali ke Pelatihan
        </a>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Pelatihan</th>
                    <th>Penyelenggara</th>
                    <th>Waktu</th>
                    <th>Status Monitoring</th>
                    <th>Aksi Monitoring</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($trainings as $t)
                <tr>
                    <td>
                        <span class="fw-bold text-dark">{{ $t->nama_pelatihan }}</span><br>
                        <small>Angkatan {{ $t->angkatan }}</small>
                    </td>
                    <td>
                        <small class="text-wrap" style="width: 200px; display: block; line-height: 1.2">
                            {{ $t->bidang }}
                        </small>
                    </td>
                    <td>
                        <small>{{ \Carbon\Carbon::parse($t->tgl_mulai)->format('d/m/Y') }}</small>
                    </td>
                    <td>
                        {{-- Logika cek apakah sudah diisi --}}
                        @if($t->monitoringResults()->exists())
                            <span class="badge bg-label-success">Sudah Diisi</span>
                        @else
                            <span class="badge bg-label-secondary">Belum Diisi</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <!-- TOMBOL ISI INSTRUMEN -->
                            <a href="{{ route('monitoring.fill', $t->id) }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-edit-alt me-1"></i> Isi Instrumen
                            </a>
                            
                            <!-- DROPDOWN DOWNLOAD -->
                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('followup.index', ['training_id' => $t->id]) }}">
                                        <i class="bx bx-task me-2 text-warning"></i> Rekomendasi Monitoring
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('monitoring.export.laporan', $t->id) }}">
                                        <i class="bx bxs-file-doc me-2 text-primary"></i> Laporan Monitoring
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('monitoring.export.tindaklanjut', $t->id) }}">
                                        <i class="bx bxs-file-doc me-2 text-warning"></i> Laporan Tindak Lanjut
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('monitoring.export.rekap', $t->id) }}">
                                        <i class="bx bxs-spreadsheet me-2 text-success"></i> Checklist Monitoring
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data pelatihan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
