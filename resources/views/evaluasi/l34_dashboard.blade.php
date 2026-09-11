@extends('layouts.master')

@section('title', 'Dashboard Evaluasi Level 3 & 4')

@section('content')
@php
    $coverageColor = $overallCoverage >= 90 ? 'success' : ($overallCoverage >= 75 ? 'warning' : 'danger');
    $fmt = fn ($value) => $value === null ? '-' : number_format($value, 1, ',', '.');
@endphp
<div class="container-xxl flex-grow-1 container-p-y l34-dashboard">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4 no-print">
        <div>
            <a href="{{ route('trainings.manage', $training->id) }}" class="small text-muted text-decoration-none"><i class="bx bx-arrow-back me-1"></i>Kembali ke Pengelolaan</a>
            <h4 class="fw-bold mt-2 mb-1">Dashboard Evaluasi Level 3 &amp; 4</h4>
            <p class="text-muted mb-0">Perubahan perilaku, dampak pelatihan, dan kelengkapan penilaian 360&deg;.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button onclick="window.print()" class="btn btn-outline-primary"><i class="bx bx-printer me-1"></i>Cetak Dashboard</button>
            <a href="{{ route('evall34.export_word', $training->id) }}" class="btn btn-primary"><i class="bx bxs-file-doc me-1"></i>Laporan Word</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden mb-4">
        <div class="card-body dashboard-hero p-4 p-md-5 text-white">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <small class="text-white-50 text-uppercase fw-bold">Evaluasi Pascapelatihan {{ $training->program_evaluasi ?: $training->bidang }}</small>
                    <h3 class="text-white fw-bold mt-2">{{ $training->nama_pelatihan }}</h3>
                    <p class="text-white-50">Angkatan {{ $training->angkatan }} &middot; Bidang {{ $training->bidang }} &middot; {{ $activeRoles->count() }} perspektif berlaku</p>
                    <p class="mb-0">{{ $executiveNarrative }}</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <span class="badge bg-white text-primary px-3 py-2">Diperbarui {{ now()->translatedFormat('d M Y, H:i') }}</span>
                    <small class="d-block text-white-50 mt-2">Instrumen otomatis mengikuti bidang dan program pelatihan</small>
                </div>
            </div>
        </div>
    </div>

    @include('evaluasi.partials.ai_dashboard_analysis', [
        'analysis' => $aiAnalysis,
        'aiError' => session('l34_ai_analysis_error'),
        'aiRoute' => route('evall34.dashboard.ai', $training->id),
    ])
    <div class="row g-3 mb-4">
        @foreach([
            ['icon'=>'bx-radar','color'=>$coverageColor,'label'=>'Cakupan Penilaian 360°','value'=>number_format($overallCoverage,1,',','.').' %','detail'=>$fullyAssessed.' dari '.$participantTotal.' alumni lengkap'],
            ['icon'=>'bx-transfer-alt','color'=>'primary','label'=>'Indeks Level 3','value'=>$fmt($l3Average),'detail'=>$scoreCategory($l3Average).' · perubahan perilaku'],
            ['icon'=>'bx-line-chart','color'=>'info','label'=>'Indeks Level 4','value'=>$fmt($l4Average),'detail'=>$scoreCategory($l4Average).' · dampak pelatihan'],
            ['icon'=>'bx-group','color'=>'success','label'=>'Alumni Dinilai Lengkap','value'=>$fullyAssessed,'detail'=>'dari '.$participantTotal.' peserta terdaftar'],
        ] as $stat)
            <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body p-3 p-md-4">
                <span class="avatar-initial rounded bg-label-{{ $stat['color'] }} p-2 d-inline-flex mb-3"><i class="bx {{ $stat['icon'] }} fs-4"></i></span>
                <small class="text-muted d-block">{{ $stat['label'] }}</small><h3 class="fw-bold mb-1">{{ $stat['value'] }}</h3>
                <small class="text-muted">{{ $stat['detail'] }}</small>
            </div></div></div>
        @endforeach
    </div>

    @if($overallCoverage < 75)
        <div class="alert alert-warning border-0 shadow-sm mb-4"><i class="bx bx-error-circle me-2"></i><strong>Data belum representatif.</strong> Cakupan penilaian masih di bawah 75%; kesimpulan perlu dibaca sebagai gambaran sementara.</div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-xl-5"><div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom"><h5 class="fw-bold mb-1">Kelengkapan Perspektif</h5><small class="text-muted">Persentase alumni yang telah dinilai oleh setiap peran.</small></div>
            <div class="card-body"><div class="chart-box"><canvas id="coverageChart"></canvas></div></div>
        </div></div>
        <div class="col-xl-7"><div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom"><h5 class="fw-bold mb-1">Perbandingan Antar-Perspektif</h5><small class="text-muted">Level 3 dan Level 4 dalam skala 0-100.</small></div>
            <div class="card-body"><div class="chart-box"><canvas id="roleChart"></canvas></div></div>
        </div></div>
    </div>

    <div class="row g-4 mb-4">
        @foreach([['id'=>'l3Chart','title'=>'Indikator Level 3: Perubahan Perilaku','items'=>$l3Indicators,'lowest'=>$lowestL3], ['id'=>'l4Chart','title'=>'Indikator Level 4: Dampak Pelatihan','items'=>$l4Indicators,'lowest'=>$lowestL4]] as $chart)
            <div class="col-xl-6"><div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom d-flex justify-content-between align-items-start gap-2">
                    <div><h5 class="fw-bold mb-1">{{ $chart['title'] }}</h5><small class="text-muted">Target acuan minimal 80.</small></div>
                    @if($chart['lowest'])<span class="badge bg-label-warning">Terendah {{ $fmt($chart['lowest']['average']) }}</span>@endif
                </div>
                <div class="card-body"><div class="chart-box chart-tall"><canvas id="{{ $chart['id'] }}"></canvas></div>
                    @if($chart['items']->isEmpty())<p class="text-center text-muted mt-3">Belum tersedia indikator yang berlaku.</p>@endif
                </div>
            </div></div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-5"><div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom"><h5 class="fw-bold mb-1">Sebaran Dampak per Alumni</h5><small class="text-muted">Kategori rata-rata jawaban Level 4 masing-masing alumni.</small></div>
            <div class="card-body"><div class="chart-box"><canvas id="impactChart"></canvas></div></div>
        </div></div>
        <div class="col-xl-7"><div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom"><h5 class="fw-bold mb-1">Rekomendasi Tindak Lanjut</h5><small class="text-muted">Prioritas otomatis berdasarkan kelengkapan dan indikator hasil.</small></div>
            <div class="card-body">
                @foreach($recommendations as $item)
                    <div class="d-flex gap-3 {{ !$loop->last ? 'border-bottom pb-3 mb-3' : '' }}">
                        <span class="avatar-initial rounded bg-label-{{ $item['color'] }} p-2 align-self-start"><i class="bx {{ $item['icon'] }} fs-4"></i></span>
                        <div><h6 class="fw-bold mb-1">{{ $item['title'] }}</h6><p class="text-muted mb-0">{{ $item['detail'] }}</p></div>
                    </div>
                @endforeach
            </div>
        </div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h6 class="fw-bold"><i class="bx bx-info-circle me-2 text-primary"></i>Catatan Interpretasi</h6>
            <p class="text-muted mb-0">Hasil bersifat deskriptif-persepsional berdasarkan jawaban yang tersedia. Indeks Level 4 menggambarkan persepsi dampak terhadap pekerjaan atau unit kerja dan bukan perhitungan dampak finansial/ROTI. Gunakan bersama bukti kinerja dan konteks pelaksanaan bidang terkait.</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.dashboard-hero{background:linear-gradient(135deg,#253b80 0%,#5668d8 55%,#20a6b7 100%)}.chart-box{position:relative;height:310px}.chart-tall{height:370px}
@media print{.no-print,.layout-navbar,.layout-menu,.content-footer{display:none!important}.layout-page{padding:0!important}.card{box-shadow:none!important;break-inside:avoid}.l34-dashboard{padding:0!important}.chart-box{height:250px}}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const roles = @json($activeRoles->values());
    const colors = ['#5668d8','#20a6b7','#71c68b'];
    const base = {responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}};
    new Chart(document.getElementById('coverageChart'), {type:'bar',data:{labels:roles,datasets:[{label:'Cakupan (%)',data:@json($coverageByRole->values()),backgroundColor:colors,borderRadius:8}]},options:{...base,scales:{y:{beginAtZero:true,max:100}}}});
    new Chart(document.getElementById('roleChart'), {type:'bar',data:{labels:roles,datasets:[
        {label:'Level 3',data:@json($activeRoles->keys()->map(fn($r) => $averagesByRole[$r]['Perubahan Perilaku'])),backgroundColor:'#5668d8',borderRadius:6},
        {label:'Level 4',data:@json($activeRoles->keys()->map(fn($r) => $averagesByRole[$r]['Dampak Pelatihan'])),backgroundColor:'#20a6b7',borderRadius:6}
    ]},options:{...base,scales:{y:{beginAtZero:true,max:100}}}});
    function indicatorChart(id, items, color) {
        const el=document.getElementById(id); if(!el || !items.length) return;
        new Chart(el,{type:'bar',data:{labels:items.map(x=>x.label),datasets:[{label:'Skor',data:items.map(x=>x.average),backgroundColor:color,borderRadius:6}]},options:{...base,indexAxis:'y',scales:{x:{beginAtZero:true,max:100}},plugins:{...base.plugins,legend:{display:false}}}});
    }
    indicatorChart('l3Chart', @json($l3Indicators->values()), '#5668d8');
    indicatorChart('l4Chart', @json($l4Indicators->values()), '#20a6b7');
    new Chart(document.getElementById('impactChart'),{type:'doughnut',data:{labels:@json($impactDistribution->keys()),datasets:[{data:@json($impactDistribution->values()),backgroundColor:['#71c68b','#20a6b7','#ffbc5b','#ff8b5b','#e65b65']}]},options:{...base,cutout:'62%'}});
});
</script>
@endpush
