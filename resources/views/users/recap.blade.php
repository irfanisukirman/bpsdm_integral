@extends('layouts.master')
@section('title', 'Rekap Peserta')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem /</span> Kelola Pengguna</h4>
    @include('users.navigation', ['recapActive' => true])

    <section class="card shadow-sm border-0" aria-labelledby="participantRecapTitle">
        <div class="card-body border-bottom">
            <h5 class="fw-bold mb-1" id="participantRecapTitle">Rekap Peserta</h5>
            <p class="text-muted mb-4">Riwayat menampilkan pelatihan dengan presensi hadir, evaluasi peserta, atau kiriman di forum pelatihan.</p>

            <form method="GET" action="{{ route('users.index') }}" class="row g-3 align-items-end">
                <input type="hidden" name="tab" value="rekap-peserta">
                <input type="hidden" name="group" value="{{ $byMunicipality ? 'kabupaten-kota' : 'instansi' }}">
                <div class="col-sm-6 col-xl-3">
                    <div class="form-label fw-semibold">Rekap berdasarkan</div>
                    <div class="d-flex flex-wrap gap-1" aria-label="Rekap berdasarkan wilayah kerja">
                        <a class="btn btn-sm {{ $byMunicipality ? 'btn-outline-primary' : 'btn-primary' }}" href="{{ route('users.index', ['tab' => 'rekap-peserta', 'search' => $search ?: null]) }}" @unless($byMunicipality) aria-current="page" @endunless>Instansi Provinsi</a>
                        <a class="btn btn-sm {{ $byMunicipality ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('users.index', ['tab' => 'rekap-peserta', 'group' => 'kabupaten-kota', 'search' => $search ?: null]) }}" @if($byMunicipality) aria-current="page" @endif>Kabupaten/Kota</a>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <label class="form-label fw-semibold" for="recapGroup">{{ $byMunicipality ? 'Kabupaten/Kota Tempat Kerja' : 'Perangkat Daerah/Instansi' }}</label>
                    <select class="form-select" name="group_id" id="recapGroup">
                        <option value="">Semua {{ $byMunicipality ? 'kabupaten/kota' : 'instansi' }}</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" @selected($groupId === (int) $group->id)>{{ $group->name }}{{ $group->is_active ? '' : ' (nonaktif)' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-8 col-xl-3">
                    <label class="form-label fw-semibold" for="recapSearch">Cari Peserta</label>
                    <input type="search" class="form-control" name="search" id="recapSearch" value="{{ $search }}" placeholder="Nama, NIP, atau NIK" maxlength="100">
                </div>
                <div class="col-sm-4 col-xl-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search me-1" aria-hidden="true"></i>Tampilkan</button>
                </div>
            </form>
        </div>

        @if(session('recap_export_status'))
            <div class="alert alert-info mx-4 mt-3 mb-0" role="status">{{ session('recap_export_status') }}</div>
        @endif
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-4 py-3">
            <span class="fw-semibold">{{ number_format($participants->total(), 0, ',', '.') }} peserta</span>
            <div class="d-flex flex-wrap align-items-center gap-3">
                @if($groupId || $search !== '')
                    <a href="{{ route('users.index', ['tab' => 'rekap-peserta', 'group' => $byMunicipality ? 'kabupaten-kota' : null]) }}">Hapus filter</a>
                @endif
                <form method="POST" action="{{ route('users.recap.export', [
                    'group' => $byMunicipality ? 'kabupaten-kota' : null,
                    'group_id' => $groupId ?: null,
                    'search' => $search !== '' ? $search : null,
                ]) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary" aria-describedby="recapExportHelp"><i class="bx bx-download me-1" aria-hidden="true"></i>Unduh Batch Excel</button>
                </form>
            </div>
        </div>
        <p id="recapExportHelp" class="small text-muted px-4 mb-3">Maksimal {{ \App\Services\ParticipantUserRecap::EXPORT_BATCH_SIZE }} peserta per file. Untuk filter yang sama, unduhan berikutnya melanjutkan dari batch terakhir.</p>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 participant-recap-table">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Nama</th>
                        <th scope="col">NIP</th>
                        <th scope="col">NIK</th>
                        @if($byMunicipality)<th scope="col">Kabupaten/Kota</th>@endif
                        <th scope="col">Nama Instansi</th>
                        <th scope="col">Pelatihan yang Diikuti</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants as $participant)
                        <tr>
                            <th scope="row" class="fw-semibold">{{ $participant['name'] }}</th>
                            <td class="text-nowrap">{{ $participant['nip'] ?: '—' }}</td>
                            <td class="text-nowrap">{{ $participant['nik'] ?: '—' }}</td>
                            @if($byMunicipality)<td>{{ $participant['municipality'] ?: '—' }}</td>@endif
                            <td>{{ $participant['institution'] ?: '—' }}</td>
                            <td class="participant-recap-trainings">
                                @if($participant['trainings']->isNotEmpty())
                                    <ul class="mb-0 ps-3">
                                        @foreach($participant['trainings'] as $training)
                                            <li>{{ $training->nama_pelatihan }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">Belum ada aktivitas tercatat</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $byMunicipality ? 6 : 5 }}" class="text-center text-muted py-5">Tidak ada peserta yang sesuai dengan filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($participants->hasPages())
            <div class="card-footer">{{ $participants->links() }}</div>
        @endif
    </section>
</div>
@endsection

@push('css')
<style>
    .participant-recap-table { min-width: 760px; }
    .participant-recap-table th, .participant-recap-table td { padding: .85rem 1rem; }
    .participant-recap-trainings { min-width: 230px; }
    .participant-recap-trainings li + li { margin-top: .35rem; }
</style>
@endpush
