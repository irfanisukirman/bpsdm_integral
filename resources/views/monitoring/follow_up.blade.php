@extends('layouts.master')

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Monitoring /</span> Daftar Tindak Lanjut
</h4>

@if(session('success'))
    <div class="alert alert-success alert-dismissible border-0 shadow-sm mb-4" role="alert">
        <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <h5 class="card-header">Temuan Monitoring Masuk</h5>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Pelatihan & Indikator</th>
                    <th>Catatan Temuan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($followUps as $fu)
                <tr>
                    <td>
                        <small class="text-primary fw-bold">{{ $fu->training->nama_pelatihan }}</small><br>
                        <span class="text-wrap" style="display:block; width: 250px;">
                            {{ $fu->question->question_text ?? 'Indikator tidak ditemukan' }}
                        </span>
                    </td>
                    <td class="text-wrap" style="width: 200px;">
                        <small class="text-muted italic">"{{ $fu->notes }}"</small>
                    </td>
                    <td>
                        @if($fu->is_resolved)
                            <span class="badge bg-label-success">Selesai</span>
                        @else
                            <span class="badge bg-label-danger animate__animated animate__flash">Open</span>
                        @endif
                    </td>
                    <td>
                        @if(!$fu->is_resolved)
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalResolve-{{ $fu->id }}">
                                <i class="bx bx-check me-1"></i> Selesaikan
                            </button>
                        @else
                            <a href="{{ asset('storage/'.$fu->evidence_file) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="bx bx-file me-1"></i> Bukti
                            </a>
                        @endif
                    </td>
                </tr>

                {{-- MODAL HARUS DI DALAM LOOP AGAR VARIABEL $fu TERBACA --}}
                <div class="modal fade" id="modalResolve-{{ $fu->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form action="{{ route('followup.resolve', $fu->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
                            @csrf @method('PUT')
                            <div class="modal-header border-bottom">
                                <h5 class="modal-title">Penyelesaian Temuan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">NARASI PERBAIKAN</label>
                                    <textarea name="resolution_notes" class="form-control" rows="4" placeholder="Jelaskan tindakan perbaikan yang telah dilakukan..." required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">UNGGAH EVIDENCE (PDF)</label>
                                    <input type="file" name="evidence_file" class="form-control" accept="application/pdf" required>
                                </div>
                            </div>
                            <div class="modal-footer border-top">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Jawaban</button>
                            </div>
                        </form>
                    </div>
                </div>

                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">Tidak ada data tindak lanjut.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection