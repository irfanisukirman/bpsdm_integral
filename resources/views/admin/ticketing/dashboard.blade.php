@extends('layouts.master')

@section('title', 'Dashboard Ticketing')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Manajemen Layanan /</span> Dashboard</h4>
        <p class="text-muted small mb-0">Ringkasan tiket Hotline.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-label-{{ $availability->isOnline() ? 'success' : 'secondary' }} fs-6 py-2 px-3 shadow-sm">
            <i class="bx {{ $availability->isOnline() ? 'bxs-circle text-success me-1' : 'bx-circle me-1' }}"></i>
            {{ $availability->statusInfo()['label'] }}
        </span>
        <a href="{{ route('ticketing.index') }}" class="btn btn-outline-primary btn-sm shadow-sm"><i class="bx bx-list-ul me-1"></i>Semua Tiket</a>
        <a href="{{ route('ticketing.export.excel') }}" class="btn btn-outline-success btn-sm shadow-sm"><i class="bx bx-file me-1"></i>Excel</a>
        <a href="{{ route('ticketing.export.pdf') }}" class="btn btn-outline-danger btn-sm shadow-sm"><i class="bx bxs-file-pdf me-1"></i>PDF</a>
    </div>
</div>

@if(Auth::user()->role === 'superadmin')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h6 class="mb-0 fw-bold"><i class="bx bx-wifi me-2 text-primary"></i>Status Ketersediaan Hotline (terlihat oleh peserta)</h6>
        @if($availability->updated_by)
        <span class="text-muted small">Terakhir diubah: {{ $availability->updated_at?->translatedFormat('d M Y H:i') }}</span>
        @endif
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('ticketing.availability') }}"
              onsubmit="if(!confirm('Simpan perubahan status ketersediaan hotline?'))return false;">
            @csrf
            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="form-label small fw-semibold mb-2">Mode Ketersediaan</label>
                    <div class="d-flex flex-column gap-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mode" id="avAuto" value="auto" @checked($availability->mode==='auto')>
                            <label class="form-check-label" for="avAuto">Otomatis (jam kerja)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mode" id="avOnline" value="manual_online" @checked($availability->mode==='manual_online')>
                            <label class="form-check-label" for="avOnline">Paksa Online</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mode" id="avOffline" value="manual_offline" @checked($availability->mode==='manual_offline')>
                            <label class="form-check-label" for="avOffline">Paksa Offline</label>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4" id="avScheduleFields">
                    <label class="form-label small fw-semibold mb-2">Jam Layanan</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1" for="avOpens">Mulai</label>
                            <input type="time" class="form-control form-control-sm" name="opens_at" id="avOpens" value="{{ $availability->opens_at ? substr($availability->opens_at,0,5) : '07:30' }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1" for="avCloses">Selesai</label>
                            <input type="time" class="form-control form-control-sm" name="closes_at" id="avCloses" value="{{ $availability->closes_at ? substr($availability->closes_at,0,5) : '16:00' }}">
                        </div>
                    </div>
                    <label class="form-label small fw-semibold mt-3 mb-1">Hari Kerja</label>
                    <div class="d-flex flex-wrap gap-2" id="avWorkdays">
                        @php $avWorkdays = $availability->workdays ?: \App\Models\HotlineAvailabilitySetting::DEFAULT_WORKDAYS; @endphp
                        @foreach([1,2,3,4,5,0,6] as $avDay)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input av-workday" type="checkbox" name="workdays[]" value="{{ $avDay }}"
                                   id="avDay{{ $avDay }}" @checked(in_array($avDay,$avWorkdays))>
                            <label class="form-check-label" for="avDay{{ $avDay }}">{{ \App\Models\HotlineAvailabilitySetting::DAYS[$avDay] }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-lg-4">
                    <label class="form-label small fw-semibold mb-2" for="avNote">Catatan (opsional, tampil di widget)</label>
                    <textarea class="form-control form-control-sm" name="note" id="avNote" rows="2" maxlength="255" placeholder="Contoh: Admin sedang dinas luar, aduan diproses esok hari.">{{ $availability->note }}</textarea>
                    <div class="form-text small">Catatan akan menggantikan teks status saat mode Paksa Online/Offline dipilih.</div>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm"><i class="bx bx-save me-1"></i>Simpan Status</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-label-primary p-3"><i class="bx bx-collection fs-3"></i></div>
                <div><div class="text-muted small">Total Tiket</div><div class="fs-4 fw-bold">{{ $stats['total'] }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-label-info p-3"><i class="bx bx-loader-circle fs-3"></i></div>
                <div><div class="text-muted small">Dalam Proses</div><div class="fs-4 fw-bold text-info">{{ $stats['open'] }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-label-secondary p-3"><i class="bx bx-x-circle fs-3"></i></div>
                <div><div class="text-muted small">Ditutup</div><div class="fs-4 fw-bold text-secondary">{{ $stats['closed'] }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 border-start border-warning border-2">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-label-warning p-3"><i class="bx bx-time-five fs-3"></i></div>
                <div><div class="text-muted small">Mendekati SLA</div><div class="fs-4 fw-bold text-warning">{{ $stats['sla_approaching'] }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 border-start border-danger border-2">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-label-danger p-3"><i class="bx bx-error-circle fs-3"></i></div>
                <div><div class="text-muted small">Melewati SLA</div><div class="fs-4 fw-bold text-danger">{{ $stats['sla_overdue'] }}</div></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-7 col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bx bx-line-chart me-2 text-primary"></i>Tren Tiket per Bulan ({{ now()->year }})</h6>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-5 col-lg-6">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bx bx-pie-chart-alt-2 me-2 text-primary"></i>Distribusi per Kategori</h6>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="120"></canvas>
            </div>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bx bx-buildings me-2 text-primary"></i>Distribusi per Bidang</h6>
            </div>
            <div class="card-body">
                <canvas id="bidangChart" height="120"></canvas>
            </div>
        </div>
    </div>
</div>

@if(Auth::user()->role === 'superadmin')
<div class="row g-3 mb-4">
    @foreach($bidangData as $nama => $count)
    <div class="col-md-4 col-lg-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small text-truncate">{{ $nama }}</div>
                <div class="fs-4 fw-bold">{{ $count }} tiket</div>
                <a href="{{ route('ticketing.index', ['bidang' => $nama]) }}" class="btn btn-sm btn-outline-primary mt-2">Lihat Tiket</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const labels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: { labels, datasets: [{ label: 'Jumlah Tiket', data: @json($monthlyData), borderColor: '#696cff', backgroundColor: 'rgba(105,108,255,.15)', fill: true, tension: .35 }] }
});
const cats = @json($categoryData);
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: { labels: Object.keys(cats).map(c => c.replace(/-/g,' ')), datasets: [{ data: Object.values(cats), backgroundColor: ['#696cff','#03c3ec','#ffab00','#71dd37','#ff3e1d'] }] }
});
const bidangs = @json($bidangData);
new Chart(document.getElementById('bidangChart'), {
    type: 'bar',
    data: { labels: Object.keys(bidangs).map(b => b.length > 25 ? b.substring(0,24)+'…' : b), datasets: [{ label: 'Tiket', data: Object.values(bidangs), backgroundColor: '#03c3ec' }] },
    options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
(function(){
    var schedule=document.getElementById('avScheduleFields');
    if(!schedule)return;
    var modes=document.querySelectorAll('input[name="mode"]');
    function sync(){
        var auto=document.querySelector('input[name="mode"]:checked');
        var isAuto=auto&&auto.value==='auto';
        schedule.style.pointerEvents=isAuto?'':'none';
        schedule.style.opacity=isAuto?'1':'.45';
    }
    modes.forEach(function(m){m.addEventListener('change',sync);});
    sync();
})();
</script>
@endpush