@extends('layouts.master')
@section('title', 'Impor Rekap Lama Level 1')

@section('content')
<div class="container-xxl container-p-y">
    <a href="{{ route('evall1.index', $training) }}" class="small"><i class="bx bx-arrow-back me-1"></i>Kembali ke Evaluasi Level 1</a>
    <h4 class="fw-bold mt-2 mb-1">Impor Rekap Lama Level 1</h4>
    <p class="text-muted">{{ $training->nama_pelatihan }} · Angkatan {{ $training->angkatan }}</p>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif

    <section class="card card-body mb-4">
        <form action="{{ route('evall1.recap-import.preview', $training) }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
            @csrf
            <div class="col-sm">
                <label for="legacyRecapFile" class="form-label fw-semibold">File Rekap Narasumber atau Penyelenggara</label>
                <input id="legacyRecapFile" type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                <div class="form-text">Impor data peserta ke pelatihan terlebih dahulu. Gunakan file Excel dengan susunan kolom rekap lama.</div>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-primary"><i class="bx bx-show me-1"></i>Pratinjau</button>
            </div>
        </form>
    </section>

    @if($preview)
        @php
            $speaker = $preview['kind'] === 'narasumber';
            $feedbackIndicators = collect($preview['indicators'])->where('kind', 'text');
        @endphp
        <section class="card mb-4" aria-labelledby="legacyRecapTitle">
            <div class="card-body border-bottom">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <h5 id="legacyRecapTitle" class="mb-1">Pratinjau Rekap {{ $speaker ? 'Narasumber' : 'Penyelenggara' }}</h5>
                        <div class="text-muted">{{ $preview['filename'] }} · {{ count($preview['entries']) }} baris · {{ count($preview['indicators']) }} indikator</div>
                    </div>
                    <span class="badge bg-label-warning">Belum disimpan</span>
                </div>
                @if($preview['source_training'])
                    <div class="alert alert-secondary mt-3 mb-2"><strong>Pelatihan di Excel</strong> {{ $preview['source_training'] }}<br><strong>Pelatihan tujuan</strong> {{ $training->nama_pelatihan }}</div>
                @endif
                <details class="mt-3">
                    <summary>Lihat indikator dari Excel</summary>
                    <ol class="mb-0">
                        @foreach($preview['indicators'] as $indicator)<li>{{ $indicator['label'] }}</li>@endforeach
                    </ol>
                </details>
                <p class="text-muted small mt-3 mb-0">{{ $speaker
                    ? 'Cocokkan nama dengan akun pengajar jika tersedia. Nama dan materi dari Excel tetap bisa disimpan meskipun belum ada akun atau jadwal.'
                    : 'Cocokkan baris dengan peserta yang sudah terdaftar. Biarkan kosong jika identitasnya tidak diketahui.' }}</p>
            </div>

            <form method="POST" action="{{ route('evall1.recap-import.store', [$training, $token]) }}">
                @csrf
                @if($speaker)<datalist id="legacyRecapIdentities">
                    @foreach($identities as $identity)
                        <option value="{{ $identity->id }} | {{ $identity->name }}">{{ $identity->name }}{{ $identity->nip_nik ? ' · '.$identity->nip_nik : '' }}</option>
                    @endforeach
                </datalist>@endif
                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="min-width: 700px">
                        <thead><tr><th>Baris Excel</th><th>{{ $speaker ? 'Narasumber / Materi' : 'Nama di Excel' }}</th><th>Rerata</th>@unless($speaker)<th>Feedback dari Excel</th>@endunless<th>{{ $speaker ? 'Cocokkan Pengajar' : 'Cocokkan Peserta' }}</th></tr></thead>
                        <tbody>
                            @foreach($preview['entries'] as $entry)
                                @php
                                    $matches = $names->get(\Illuminate\Support\Str::lower($entry['name'] ?? ''), collect());
                                    $match = $matches->count() === 1 ? $matches->first() : null;
                                @endphp
                                <tr>
                                    <td>{{ $entry['row'] }}</td>
                                    <th scope="row">{{ $entry['name'] ?? 'Anonim' }}@if($entry['material'])<small class="text-muted d-block">{{ $entry['material'] }}</small>@endif</th>
                                    <td>{{ $entry['average'] === null ? '—' : number_format($entry['average'], 2, ',', '.') }}</td>
                                    @unless($speaker)
                                        <td style="min-width: 14rem; white-space: normal;">
                                            @foreach($feedbackIndicators as $index => $indicator)
                                                @if($entry['values'][$index] !== null)
                                                    <div class="small mb-1"><span class="text-muted">{{ $indicator['label'] }}</span><br>{{ $entry['values'][$index] }}</div>
                                                @endif
                                            @endforeach
                                        </td>
                                    @endunless
                                    <td>
                                        @if($speaker)
                                        <input type="text" class="form-control form-control-sm @error('mapping.'.$entry['row']) is-invalid @enderror"
                                               name="mapping[{{ $entry['row'] }}]" list="legacyRecapIdentities" maxlength="255"
                                               aria-label="Pencocokan baris {{ $entry['row'] }}"
                                               value="{{ old('mapping.'.$entry['row'], $match ? $match->id.' | '.$match->name : '') }}"
                                               placeholder="Cari nama dari daftar">
                                        @else
                                            <select name="mapping[{{ $entry['row'] }}]" aria-label="Peserta untuk baris {{ $entry['row'] }}"
                                                    class="form-select form-select-sm @error('mapping.'.$entry['row']) is-invalid @enderror">
                                                <option value="">{{ $entry['name'] ? 'Gunakan nama dari Excel' : 'Tetap anonim' }}</option>
                                                @foreach($identities as $identity)
                                                    <option value="{{ $identity->id }}" @selected((string) old('mapping.'.$entry['row'], $match?->id) === (string) $identity->id)>
                                                        {{ $identity->name }}{{ $identity->nip_nik ? ' · '.$identity->nip_nik : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                        @error('mapping.'.$entry['row'])<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex flex-wrap justify-content-end gap-2">
                    <a href="{{ route('evall1.recap-import.index', $training) }}" class="btn btn-outline-secondary">Batal</a>
                    <button class="btn btn-primary" type="submit"><i class="bx bx-save me-1"></i>Simpan Impor</button>
                </div>
            </form>
        </section>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="mb-0">Rekap yang Tersimpan</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('evall12.dashboard', $training) }}" class="btn btn-sm btn-outline-primary">Dashboard Evaluasi</a>
            <a href="{{ route('evall1.organizer-summary', $training) }}" class="btn btn-sm btn-outline-primary">Rangkuman Penyelenggara</a>
        </div>
    </div>
    @forelse($imports as $import)
        @include('evaluasi.partials.imported-l1-recap', ['notesOnly' => false])
    @empty
        <div class="card card-body text-muted">Belum ada rekap lama yang diimpor.</div>
    @endforelse
    {{ $imports->links() }}
</div>
@endsection
