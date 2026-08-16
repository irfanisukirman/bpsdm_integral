@extends('layouts.master')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold py-3 mb-0">
        <span class="text-muted fw-light">Evaluasi /</span> Level 2: Learning ({{ $training->nama_pelatihan }})
    </h4>
    <div class="d-flex gap-2">
        <a href="{{ route('trainings.manage', $training->id) }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali ke Pengelolaan
        </a>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImportL2">
            <i class="bx bx-upload me-1"></i> Import Nilai
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Peserta</th>
                    <th>Pre-Test</th>
                    <th>Post-Test</th>
                    <th>N-Gain</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($participants as $p)
                <tr>
                    <td>
                        <strong>{{ $p->name }}</strong><br>
                        <small class="text-muted">{{ $p->nip_nik }}</small>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm input-score" 
                               id="pre-{{ $p->id }}" value="{{ $p->evaluationL2->pretest ?? 0 }}">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm input-score" 
                               id="post-{{ $p->id }}" value="{{ $p->evaluationL2->postest ?? 0 }}">
                    </td>
                    <td>
                        @php 
                            $pre = $p->evaluationL2->pretest ?? 0;
                            $post = $p->evaluationL2->postest ?? 0;
                            $gain = $post - $pre;
                        @endphp
                        <span class="badge {{ $gain >= 0 ? 'bg-label-success' : 'bg-label-danger' }}">
                            {{ $gain > 0 ? '+' : '' }}{{ $gain }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm btn-save-score" data-id="{{ $p->id }}">
                            <i class="bx bx-save"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Import L2 -->
<div class="modal fade" id="modalImportL2" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('evall2.import', $training->id) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Import Nilai via Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small">
                    <i class="bx bx-info-circle me-1"></i> Pastikan NIP/NIK sesuai dengan data peserta yang sudah diimport sebelumnya.
                </div>
                <div class="mb-3">
                    <label class="form-label">Pilih File Excel</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx, .xls" required>
                </div>
            </div>
            <div class="modal-footer border-top">
                <a href="{{ route('evall2.template', $training->id) }}" class="btn btn-outline-secondary">
                    <i class="bx bx-download me-1"></i> Download Daftar Peserta (Excel)
                </a>
                <button type="submit" class="btn btn-primary">Mulai Proses Import</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    // Fungsi simpan manual via AJAX
    $('.btn-save-score').click(function() {
        let id = $(this).data('id');
        let pre = $('#pre-' + id).val();
        let post = $('#post-' + id).val();

        $.post("{{ route('evall2.update-single') }}", {
            _token: "{{ csrf_token() }}",
            participant_id: id,
            pretest: pre,
            postest: post
        }, function(res) {
            alert('Nilai berhasil disimpan');
            location.reload();
        });
    });
</script>
@endpush