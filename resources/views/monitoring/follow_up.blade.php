@extends('layouts.master')
@section('content')
<h4 class="fw-bold py-3 mb-4">Daftar Tindak Lanjut</h4>

<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Pelatihan & Indikator</th>
                    <th>Temuan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($followUps as $index => $fu)
                <tr>
                    <td>
                        <small class="text-primary fw-bold">{{ $fu->training->nama_pelatihan }}</small><br>
                        <span class="text-wrap">{{ $fu->question->question_text ?? 'Indikator' }}</span>
                    </td>
                    <td class="text-wrap small">"{{ $fu->notes }}"</td>
                    <td>
                        @if($fu->is_resolved)
                            <span class="badge bg-label-success">Selesai</span>
                        @else
                            <span class="badge bg-label-danger">Open</span>
                        @endif
                    </td>
                    <td>
                        @if(!$fu->is_resolved)
                            <!-- Tombol untuk memicu Modal -->
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalResolve-{{ $fu->id }}">
                                <i class="bx bx-check me-1"></i> Selesaikan
                            </button>
                        @else
                            <a href="{{ asset('storage/'.$fu->evidence_file) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="bx bx-file me-1"></i> Lihat Bukti
                            </a>
                        @endif
                    </td>
                </tr>  
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="modalResolve-{{ $fu->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form action="{{ route('followup.resolve', $fu->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
                            @csrf @method('PUT')
                            <div class="modal-header border-bottom">
                                <h5 class="modal-title">Penyelesaian Tindak Lanjut</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-uppercase small">Narasi Penyelesaian</label>
                                    <textarea name="resolution_notes" class="form-control" rows="4" placeholder="Jelaskan tindakan perbaikan yang dilakukan..." required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-uppercase small">Unggah Bukti Fisik (PDF)</label>
                                    <input type="file" name="evidence_file" class="form-control" accept="application/pdf" required>
                                    <div class="form-text text-muted small">
                                        <i class="bx bx-info-circle me-1"></i> Format file wajib PDF (Maks 10MB).
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-top">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary px-4">KIRIM JAWABAN</button>
                            </div>
                        </form>
                    </div>
                </div>
@endsection