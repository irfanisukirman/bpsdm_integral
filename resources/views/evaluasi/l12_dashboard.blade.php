@extends('layouts.master')

@section('title', 'Dashboard Evaluasi Level 1 & 2')

@section('content')
@php
    $gainClass = $gainAverage === null ? 'secondary' : ($gainAverage > 0 ? 'success' : ($gainAverage < 0 ? 'danger' : 'warning'));
    $responseClass = $l1ResponseRate >= 90 ? 'success' : ($l1ResponseRate >= 75 ? 'warning' : 'danger');
@endphp
<div class="container-xxl flex-grow-1 container-p-y executive-dashboard">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4 no-print">
        <div>
            <a href="{{ route('trainings.manage', $training->id) }}" class="small text-muted text-decoration-none"><i class="bx bx-arrow-back me-1"></i>Kembali ke Pengelolaan</a>
            <h4 class="fw-bold mt-2 mb-1">Dashboard Evaluasi Level 1 &amp; 2</h4>
            <p class="text-muted mb-0">Ringkasan eksekutif untuk bahan evaluasi dan pelaporan kepada pimpinan.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" onclick="window.print()" class="btn btn-outline-primary"><i class="bx bx-printer me-1"></i>Cetak Dashboard</button>
            <a href="{{ route('evall12.export_word', $training->id) }}" class="btn btn-primary"><i class="bx bxs-file-doc me-1"></i>Laporan Word</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden mb-4">
        <div class="card-body executive-hero p-4 p-md-5 text-white">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <small class="text-white-50 text-uppercase fw-bold">Ringkasan Evaluasi Pelatihan</small>
                    <h3 class="text-white fw-bold mt-2 mb-2">{{ $training->nama_pelatihan }}</h3>
                    <p class="text-white-50 mb-3">Angkatan {{ $training->angkatan }} &middot; {{ $training->bidang }} &middot; {{ \Carbon\Carbon::parse($training->tgl_mulai)->translatedFormat('d M Y') }}–{{ \Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d M Y') }}</p>
                    <p class="mb-0 executive-narrative">{{ $executiveNarrative }}</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <span class="badge bg-white text-primary px-3 py-2 mb-2">Data diperbarui {{ now()->translatedFormat('d M Y, H:i') }}</span>
                    <small class="d-block text-white-50">{{ $participantsTotal }} peserta terdaftar</small>
                </div>
            </div>
        </div>
    </div>

    @include('evaluasi.partials.ai_dashboard_analysis', [
        'analysis' => $aiAnalysis,
        'aiError' => session('l12_ai_analysis_error'),
        'aiRoute' => route('evall12.dashboard.ai', $training->id),
    ])
    <div class="row g-3 mb-4">
        @foreach([
            ['icon'=>'bx-check-circle','color'=>$responseClass,'label'=>'Tingkat Respons L1','value'=>number_format($l1ResponseRate,1,',','.').' %','detail'=>$l1Respondents.' dari '.$participantsTotal.' peserta'],
            ['icon'=>'bx-smile','color'=>'primary','label'=>'Skor Kepuasan L1','value'=>$l1Average === null ? '–' : number_format($l1Average,1,',','.'),'detail'=>$scoreCategory($l1Average)],
            ['icon'=>'bx-trending-up','color'=>$gainClass,'label'=>'Peningkatan Rerata L2','value'=>$gainAverage === null ? '–' : (($gainAverage > 0 ? '+' : '').number_format($gainAverage,1,',','.')),'detail'=>$l2Count.' peserta memiliki nilai'],
            ['icon'=>'bx-user-check','color'=>'success','label'=>'Peserta Meningkat','value'=>number_format($increaseRate,1,',','.').' %','detail'=>$l2Status['increased'].' dari '.$l2Count.' peserta'],
        ] as $stat)
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 executive-stat"><div class="card-body p-3 p-md-4">
                    <span class="avatar-initial rounded bg-label-{{ $stat['color'] }} p-2 d-inline-flex mb-3"><i class="bx {{ $stat['icon'] }} fs-4"></i></span>
                    <small class="text-muted d-block">{{ $stat['label'] }}</small>
                    <h3 class="fw-bold mb-1">{{ $stat['value'] }}</h3>
                    <small class="text-muted">{{ $stat['detail'] }}</small>
                </div></div>
            </div>
        @endforeach
    </div>

    @if($l1ResponseRate < 75 || $l2Count < $participantsTotal)
        <div class="alert alert-warning border-0 shadow-sm mb-4"><i class="bx bx-error-circle me-2"></i><strong>Perhatikan kelengkapan data.</strong> Interpretasi hasil perlu mempertimbangkan bahwa respons L1 atau data L2 belum mencakup seluruh peserta.</div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom"><h5 class="mb-1 fw-bold">Komposisi Skor Level 1</h5><small class="text-muted">Perbandingan reaksi terhadap penyelenggara dan narasumber.</small></div>
                <div class="card-body"><div class="chart-wrap"><canvas id="l1ComparisonChart"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom d-flex justify-content-between align-items-start gap-2">
                    <div><h5 class="mb-1 fw-bold">Skor Indikator Level 1</h5><small class="text-muted">Garis putus-putus menunjukkan target minimal 80.</small></div>
                    @if($lowestIndicator)<span class="badge bg-label-warning">Terendah {{ $lowestIndicator['average'] }}</span>@endif
                </div>
                <div class="card-body"><div class="chart-wrap chart-wide"><canvas id="indicatorChart"></canvas></div></div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom"><h5 class="mb-1 fw-bold">Rerata Hasil Belajar</h5><small class="text-muted">Perbandingan nilai pretest dan posttest Level 2.</small></div>
                <div class="card-body"><div class="chart-wrap"><canvas id="learningChart"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom"><h5 class="mb-1 fw-bold">Perubahan Peserta</h5><small class="text-muted">Distribusi hasil belajar individual.</small></div>
                <div class="card-body"><div class="chart-wrap"><canvas id="learningStatusChart"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom"><h5 class="mb-1 fw-bold">Progres Pengisian L1</h5><small class="text-muted">Cakupan data reaksi peserta.</small></div>
                <div class="card-body"><div class="chart-wrap"><canvas id="responseChart"></canvas></div></div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom d-flex justify-content-between align-items-start gap-2">
                    <div><h5 class="mb-1 fw-bold">Kesimpulan Saran &amp; Masukan</h5><small class="text-muted">Telaah resmi admin terhadap {{ $textFeedback->count() }} masukan tertulis.</small></div>
                    <span class="badge {{ $adminSummary ? 'bg-label-success' : 'bg-label-warning' }}">{{ $adminSummary ? 'Tersedia' : 'Belum ditelaah' }}</span>
                </div>
                <div class="card-body">
                    @if($adminSummary)
                        <h6 class="text-primary fw-bold">Kesimpulan Umum</h6><p class="summary-text">{{ $adminSummary->conclusion }}</p>
                        <hr><h6 class="text-warning fw-bold">Rencana Tindak Lanjut</h6><p class="summary-text mb-2">{{ $adminSummary->follow_up }}</p>
                        <small class="text-muted">Diperbarui {{ $adminSummary->reviewed_at?->translatedFormat('d M Y, H:i') }} oleh {{ $adminSummary->reviewer?->name ?? 'Admin' }}</small>
                    @else
                        <div class="text-center py-4 text-muted"><i class="bx bx-message-square-edit fs-1 d-block mb-2"></i>Kesimpulan admin belum tersedia.<div class="no-print"><a href="{{ route('evall1.organizer-summary', $training->id) }}#adminSummaryCard" class="btn btn-sm btn-outline-warning mt-3">Isi Kesimpulan</a></div></div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom"><h5 class="mb-1 fw-bold">Rekomendasi Prioritas</h5><small class="text-muted">Temuan yang memerlukan keputusan atau tindak lanjut pimpinan.</small></div>
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table align-middle mb-0"><thead class="table-light"><tr><th>Prioritas</th><th>Temuan</th><th>Tindakan</th></tr></thead><tbody>
                    @forelse($recommendations as $recommendation)
                        @php $priorityColor = $recommendation['priority'] === 'tinggi' ? 'danger' : ($recommendation['priority'] === 'sedang' ? 'warning' : 'success'); @endphp
                        <tr><td><span class="badge bg-label-{{ $priorityColor }}">{{ strtoupper($recommendation['priority']) }}</span></td><td>{{ $recommendation['finding'] }}</td><td>{{ $recommendation['action'] }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-5">Belum ada rekomendasi prioritas yang dapat ditetapkan.</td></tr>
                    @endforelse
                    </tbody></table></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 d-flex gap-3 align-items-start">
            <span class="avatar-initial rounded bg-label-primary p-2"><i class="bx bx-bulb fs-4"></i></span>
            <div><h5 class="fw-bold mb-2">Kesimpulan Eksekutif</h5><p class="mb-0 summary-text">{{ $executiveNarrative }} {{ $adminSummary?->conclusion }}</p></div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const common = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } };
    const targetPlugin = { id: 'targetLine', afterDatasetsDraw(chart) { if (chart.canvas.id !== 'indicatorChart') return; const y = chart.scales.y.getPixelForValue(80); const ctx = chart.ctx; ctx.save(); ctx.setLineDash([6, 5]); ctx.strokeStyle = '#ffab00'; ctx.beginPath(); ctx.moveTo(chart.chartArea.left, y); ctx.lineTo(chart.chartArea.right, y); ctx.stroke(); ctx.restore(); } };
    Chart.register(targetPlugin);
    new Chart(document.getElementById('l1ComparisonChart'), { type: 'bar', data: { labels: ['Keseluruhan', 'Penyelenggara', 'Narasumber'], datasets: [{ label: 'Skor rata-rata', data: @json([$l1Average, $organizerAverage, $speakerAverage]), backgroundColor: ['#696cff','#ffab00','#71dd37'], borderRadius: 8 }] }, options: { ...common, scales: { y: { beginAtZero: true, max: 100 } } } });
    new Chart(document.getElementById('indicatorChart'), { type: 'bar', data: { labels: @json($indicatorData->pluck('label')->map(fn($label) => \Illuminate\Support\Str::limit($label, 42))), datasets: [{ label: 'Skor indikator', data: @json($indicatorData->pluck('average')), backgroundColor: @json($indicatorData->map(fn($item) => $item['average'] < 80 ? '#ff3e1d' : ($item['average'] < 90 ? '#ffab00' : '#71dd37'))), borderRadius: 6 }] }, options: { ...common, indexAxis: 'y', scales: { x: { beginAtZero: true, max: 100 } } } });
    new Chart(document.getElementById('learningChart'), { type: 'bar', data: { labels: ['Pretest', 'Posttest'], datasets: [{ label: 'Nilai rata-rata', data: @json([$preAverage, $postAverage]), backgroundColor: ['#8592a3','#03c3ec'], borderRadius: 10 }] }, options: { ...common, scales: { y: { beginAtZero: true, max: 100 } } } });
    new Chart(document.getElementById('learningStatusChart'), { type: 'doughnut', data: { labels: ['Meningkat', 'Tetap', 'Menurun'], datasets: [{ data: @json(array_values($l2Status)), backgroundColor: ['#71dd37','#ffab00','#ff3e1d'], borderWidth: 0 }] }, options: { ...common, cutout: '65%' } });
    new Chart(document.getElementById('responseChart'), { type: 'doughnut', data: { labels: ['Sudah mengisi', 'Belum mengisi'], datasets: [{ data: @json([$l1Respondents, max(0, $participantsTotal-$l1Respondents)]), backgroundColor: ['#696cff','#e7e7ff'], borderWidth: 0 }] }, options: { ...common, cutout: '68%' } });
});
</script>
@endpush

<style>
    .executive-hero { background: linear-gradient(135deg, #696cff 0%, #4447b8 100%); }
    .executive-narrative, .summary-text { white-space: pre-line; line-height: 1.7; }
    .executive-stat { transition: transform .2s ease; }
    .executive-stat:hover { transform: translateY(-2px); }
    .chart-wrap { position: relative; min-height: 300px; }
    .chart-wide { min-height: 360px; }
    @media print {
        .layout-menu, .layout-navbar, .content-footer, .no-print { display: none !important; }
        .layout-page, .content-wrapper, .container-xxl { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: none !important; }
        .card { box-shadow: none !important; break-inside: avoid; border: 1px solid #ddd !important; }
        .executive-dashboard { font-size: 11px; }
        .chart-wrap { min-height: 240px; }
    }
</style>
@endsection
