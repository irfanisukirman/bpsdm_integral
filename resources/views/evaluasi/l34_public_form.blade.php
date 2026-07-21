@extends('layouts.auth') {{-- Menggunakan layout bersih --}}
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-dark text-white py-4">
                    <h5 class="mb-0 text-white">Form Penilaian: <span class="text-uppercase">{{ $role }}</span></h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('public.l34.store', [$training->id, $role]) }}" method="POST">
                        @csrf
                        <div class="mb-4 bg-label-secondary p-3 rounded">
                            <label class="form-label fw-bold">Siapa Alumni yang Anda nilai?</label>
                            <select name="participant_id" class="form-select select2" required>
                                <option value="">-- Pilih Alumni --</option>
                                @foreach($participants as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->instansi }})</option>
                                @endforeach
                            </select>
                        </div>

                        @if($role != 'mandiri')
                        <div class="mb-5">
                            <label class="form-label fw-bold">Nama Lengkap Anda (Penilai)</label>
                            <input type="text" name="evaluator_name" class="form-control" placeholder="Tulis nama anda..." required>
                        </div>
                        @endif

                        <h6 class="text-primary mb-3 border-bottom pb-2">Instrumen Penilaian (Skala 10 - 100)</h6>
                        @foreach($questions as $q)
                        <div class="mb-5 bg-white p-3 border rounded shadow-sm">
                            <label class="form-label d-block mb-3 small">{{ $q->question_text }}</label>
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-label-danger">10</span>
                                <input type="range" name="scores[{{ $q->id }}]" class="form-range" min="10" max="100" step="5" value="80" oninput="this.nextElementSibling.value = this.value">
                                <output class="fw-bold text-primary h5 mb-0">80</output>
                            </div>
                            <div class="d-flex justify-content-between mt-2 small text-muted">
                                <span>Sangat Kurang</span>
                                <span>Sangat Baik</span>
                            </div>
                        </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary btn-lg w-100">Simpan Hasil Penilaian</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection