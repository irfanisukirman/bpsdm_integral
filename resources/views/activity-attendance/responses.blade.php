@extends('layouts.master')
@section('title', 'Statistik Respons Presensi')

@push('css')
<style>
.aa-page{min-width:0}.aa-hero{background:linear-gradient(135deg,#20366f 0%,#4f62d3 58%,#168fa1 100%);border-radius:18px;color:#fff;overflow:hidden;position:relative}.aa-hero:after{content:"";position:absolute;width:240px;height:240px;border-radius:50%;background:rgba(255,255,255,.09);right:-65px;top:-105px}.aa-hero h3,.aa-hero p,.aa-hero small,.aa-hero span{color:#fff!important}.aa-back-link{color:rgba(255,255,255,.82)!important}.aa-back-link:hover{color:#fff!important}.aa-actions .btn-light.text-success{color:#198754!important}.aa-actions .btn-light.text-danger{color:#dc3545!important}.aa-actions .btn-light i{color:inherit!important}.aa-hero__content{position:relative;z-index:1}.aa-hero__meta{display:flex;flex-wrap:wrap;gap:.55rem 1.1rem;margin-top:1rem;color:rgba(255,255,255,.88);font-size:.8rem}.aa-hero__meta span{display:inline-flex;align-items:center;gap:.38rem}.aa-actions{display:flex;flex-wrap:wrap;gap:.6rem;align-items:center}.aa-actions .btn{min-height:40px;font-weight:600;white-space:nowrap}.aa-actions .btn-outline-light{border-color:rgba(255,255,255,.72);color:#fff!important}.aa-actions .btn-outline-light:hover{background:#fff;color:#263b75!important}.aa-metric{border:1px solid #edf0f7;border-radius:16px;box-shadow:0 8px 24px rgba(34,48,92,.07);background:#fff}.aa-metric__icon{flex:0 0 46px;width:46px;height:46px;border-radius:13px;display:grid;place-items:center;font-size:1.35rem}.aa-metric small{color:#697a8d!important}.aa-metric strong{color:#26334d!important}.aa-chart-card{border:1px solid #edf0f7;border-radius:16px;box-shadow:0 8px 24px rgba(34,48,92,.07);background:#fff;overflow:hidden}.aa-chart-card .card-header{background:#fff;color:#26334d}.aa-chart-card h5,.aa-chart-card h6{color:#26334d!important}.aa-chart-wrap{height:285px;position:relative;min-width:0}.aa-toolbar{border:1px solid #edf0f7;border-radius:14px;box-shadow:0 5px 18px rgba(34,48,92,.06);background:#fff}.aa-table-card{border:1px solid #edf0f7!important;border-radius:16px}.aa-response-table{min-width:780px}.aa-response-table thead th{background:#f5f7fb!important;color:#52627a!important;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;border-bottom:1px solid #e5e9f2}.aa-response-table tbody td{color:#38475c;background:#fff}.aa-response-table tbody tr:hover td{background:#f9faff}.aa-answer-preview{min-width:280px;max-width:580px}.aa-answer-preview small{line-height:1.55}.aa-section-label{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1rem}.aa-section-label h5{color:#26334d;margin:0}.aa-section-label small{color:#7b8798}.aa-empty-chart{padding:2rem;border:1px dashed #ccd3e1;border-radius:14px;background:#fafbfe;text-align:center;color:#697a8d}
@media(max-width:767.98px){.aa-hero .card-body{padding:1.35rem!important}.aa-hero h3{font-size:1.35rem}.aa-actions{display:grid;grid-template-columns:1fr 1fr;width:100%}.aa-actions .btn:last-child{grid-column:1/-1}.aa-actions .btn{width:100%}.aa-chart-wrap{height:240px}.aa-metric .card-body{padding:.9rem!important}.aa-metric__icon{width:40px;height:40px;flex-basis:40px}.aa-metric strong{font-size:1.25rem!important}.aa-toolbar form>.col{flex:0 0 100%;width:100%}.aa-toolbar form>.col-auto{flex:1}.aa-toolbar .btn{width:100%}}
</style>
@endpush

@section('content')
<div class="aa-page">
<div class="aa-hero shadow-sm mb-4">
    <div class="card-body p-4 p-lg-5 aa-hero__content">
        <div class="d-flex flex-column flex-xl-row justify-content-between gap-4">
            <div>
                <a href="{{ route('activity-attendance.edit', $activityAttendance) }}" class="aa-back-link text-decoration-none small"><i class="bx bx-left-arrow-alt me-1"></i>Kembali ke Form</a>
                <h3 class="text-white fw-bold mt-3 mb-2">Statistik & Respons Presensi</h3>
                <p class="mb-0 opacity-75">{{ $activityAttendance->title }}</p>
                <div class="aa-hero__meta">
                    <span><i class="bx bx-radio-circle-marked"></i>{{ ucfirst($activityAttendance->status) }}</span>
                    <span><i class="bx bx-map"></i>{{ $activityAttendance->location ?: 'Lokasi belum dicantumkan' }}</span>
                    <span><i class="bx bx-calendar"></i>{{ $activityAttendance->opens_at?->translatedFormat('d M Y H:i') ?: 'Tanpa batas waktu mulai' }}@if($activityAttendance->closes_at) s.d. {{ $activityAttendance->closes_at->translatedFormat('d M Y H:i') }}@endif</span>
                </div>
            </div>
            <div class="aa-actions align-self-xl-center">
                <a href="{{ route('activity-attendance.export.excel', $activityAttendance) }}" class="btn btn-light text-success"><i class="bx bx-spreadsheet me-1"></i>Excel</a>
                <a href="{{ route('activity-attendance.export.pdf', $activityAttendance) }}" class="btn btn-light text-danger"><i class="bx bxs-file-pdf me-1"></i>PDF</a>
                <a href="{{ route('activity-attendance.attachments', $activityAttendance) }}" class="btn btn-outline-light"><i class="bx bx-archive-in me-1"></i>ZIP Lampiran</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['Total Respons', $stats['responses'], 'bx-user-check', 'primary'],
        ['Masuk Hari Ini', $stats['today'], 'bx-calendar-check', 'success'],
        ['Total Lampiran', $stats['attachments'], 'bx-paperclip', 'info'],
        ['Kelengkapan Wajib', $stats['completion'].'%', 'bx-check-shield', 'warning'],
    ] as [$label,$value,$icon,$color])
    <div class="col-6 col-xl-3"><div class="card aa-metric h-100"><div class="card-body d-flex align-items-center gap-3 p-3 p-lg-4"><span class="aa-metric__icon bg-label-{{ $color }} text-{{ $color }}"><i class="bx {{ $icon }}"></i></span><div><small class="text-muted d-block">{{ $label }}</small><strong class="fs-4 text-dark">{{ $value }}</strong></div></div></div></div>
    @endforeach
</div>

@if($stats['responses'] > 0)
<div class="row g-4 mb-4">
    <div class="col-12"><div class="card aa-chart-card"><div class="card-header border-0 pb-0"><h5 class="fw-bold mb-1">Tren Respons Harian</h5><p class="text-muted small mb-0">Jumlah formulir yang dikirim berdasarkan tanggal.</p></div><div class="card-body"><div class="aa-chart-wrap"><div id="responseTrendChart" class="w-100 h-100"></div></div></div></div></div>
    @foreach($charts as $index => $chart)
    <div class="col-12 col-xl-6"><div class="card aa-chart-card h-100"><div class="card-header border-0 pb-0"><h6 class="fw-bold mb-1">{{ $chart['question']->label }}</h6><small class="text-muted">Distribusi {{ $chart['total'] }} pilihan tercatat</small></div><div class="card-body"><div class="aa-chart-wrap"><div id="questionChart{{ $index }}" class="w-100 h-100"></div></div></div></div></div>
    @endforeach
</div>
@endif

<div class="card aa-toolbar mb-3"><div class="card-body"><form class="row g-2 align-items-center"><div class="col"><div class="input-group"><span class="input-group-text bg-transparent"><i class="bx bx-search"></i></span><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama, identitas, atau isi jawaban"></div></div><div class="col-auto"><button class="btn btn-primary">Cari</button></div>@if(request('q'))<div class="col-auto"><a href="{{ route('activity-attendance.responses', $activityAttendance) }}" class="btn btn-label-secondary">Reset</a></div>@endif</form></div></div>

<div class="card aa-table-card shadow-sm overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0 aa-response-table"><thead><tr><th>No.</th><th>Waktu Mengisi</th><th>Ringkasan Jawaban</th><th>Lampiran</th><th class="text-end">Aksi</th></tr></thead><tbody>
@forelse($responses as $response)
<tr><td><span class="badge bg-label-secondary">{{ $responses->firstItem()+$loop->index }}</span></td><td><strong class="text-dark">{{ $response->submitted_at->translatedFormat('d M Y') }}</strong><small class="d-block text-muted"><i class="bx bx-time-five me-1"></i>{{ $response->submitted_at->format('H:i') }} WIB</small></td><td class="aa-answer-preview">@forelse($response->answers->filter(fn($answer)=>filled($answer->value_text))->take(3) as $answer)<small class="d-block text-truncate"><span class="text-muted">{{ $answer->question?->label }}:</span> <span class="text-dark">{{ Str::limit($answer->value_text, 70) }}</span></small>@empty<small class="text-muted">Jawaban berupa pilihan atau lampiran.</small>@endforelse</td><td><span class="badge bg-label-info"><i class="bx bx-paperclip me-1"></i>{{ $response->answers->whereNotNull('file_path')->count() }} file</span></td><td class="text-end"><a href="{{ route('activity-attendance.responses.show', $response) }}" class="btn btn-sm btn-primary"><i class="bx bx-show me-1"></i>Detail</a></td></tr>
@empty
<tr><td colspan="5" class="text-center py-5 text-muted"><i class="bx bx-inbox fs-1 d-block mb-2"></i>{{ request('q') ? 'Tidak ada respons yang sesuai pencarian.' : 'Belum ada respons masuk.' }}</td></tr>
@endforelse
</tbody></table></div>@if($responses->hasPages())<div class="card-footer bg-white border-top py-3">{{ $responses->links() }}</div>@endif</div>
</div>
@endsection

@if($stats['responses'] > 0)
@push('js')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof ApexCharts === 'undefined') {
        document.querySelectorAll('.aa-chart-wrap').forEach(function (element) {
            element.innerHTML = '<div class="aa-empty-chart">Grafik gagal dimuat. Silakan muat ulang halaman.</div>';
        });
        return;
    }

    const colors = ['#5668d8','#20a6b7','#71c68b','#ffb648','#e65b65','#8d70c9','#5b9bd5','#ed7d31'];
    const baseChart = {fontFamily:'Public Sans, sans-serif',foreColor:'#697a8d',toolbar:{show:false},animations:{enabled:true,speed:500}};

    new ApexCharts(document.querySelector('#responseTrendChart'), {
        chart:{...baseChart,type:'area',height:285},
        series:[{name:'Respons',data:@json($trend->pluck('value')->values())}],
        xaxis:{categories:@json($trend->pluck('label')->values()),axisBorder:{show:false},axisTicks:{show:false}},
        yaxis:{min:0,forceNiceScale:true,labels:{formatter:value=>Math.round(value)}},
        colors:['#5668d8'],stroke:{curve:'smooth',width:3},fill:{type:'gradient',gradient:{shadeIntensity:1,opacityFrom:.28,opacityTo:.04,stops:[0,95]}},
        dataLabels:{enabled:false},grid:{borderColor:'#edf0f7',strokeDashArray:4},tooltip:{x:{show:true}}
    }).render();

    @foreach($charts as $index => $chart)
    @if($chart['labels']->count() <= 6)
    new ApexCharts(document.querySelector('#questionChart{{ $index }}'), {
        chart:{...baseChart,type:'donut',height:285},
        series:@json($chart['values']->map(fn($value)=>(int)$value)->values()),
        labels:@json($chart['labels']->values()),colors:colors,
        legend:{position:'bottom',fontSize:'12px'},dataLabels:{enabled:true},
        plotOptions:{pie:{donut:{size:'58%',labels:{show:true,total:{show:true,label:'Total Pilihan',formatter:()=>@json($chart['total'])}}}}},
        noData:{text:'Belum ada jawaban'}
    }).render();
    @else
    new ApexCharts(document.querySelector('#questionChart{{ $index }}'), {
        chart:{...baseChart,type:'bar',height:285},
        series:[{name:'Jawaban',data:@json($chart['values']->map(fn($value)=>(int)$value)->values())}],
        xaxis:{categories:@json($chart['labels']->values()),min:0,labels:{formatter:value=>Math.round(value)}},
        colors:['#5668d8'],plotOptions:{bar:{horizontal:true,borderRadius:6,barHeight:'58%'}},
        dataLabels:{enabled:true},grid:{borderColor:'#edf0f7',strokeDashArray:4},legend:{show:false},noData:{text:'Belum ada jawaban'}
    }).render();
    @endif
    @endforeach
});
</script>
@endpush
@endif