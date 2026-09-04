@extends('layouts.master')
@section('title','Dashboard Penggunaan Aset')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y asset-dashboard">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4 no-print">
        <div><h4 class="fw-bold mb-1">Dashboard Penggunaan Aset</h4><p class="text-muted mb-0">Pantauan pemakaian aset lintas bidang, waktu penggunaan, dan ketersediaan.</p></div>
        <div class="d-flex flex-wrap gap-2 align-items-end">
            <form method="GET" action="{{ route('assets.dashboard') }}" class="d-flex gap-2 align-items-end">
                <div><label class="form-label small fw-bold mb-1">Tanggal pantauan</label><input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()"></div>
                @if($date !== now()->toDateString())<a href="{{ route('assets.dashboard') }}" class="btn btn-outline-primary">Hari Ini</a>@endif
            </form>
            <a href="{{ route('daily-schedule.index',['date'=>$date]) }}" class="btn btn-outline-info"><i class="bx bx-calendar-check me-1"></i>Jadwal Harian</a>
            <a href="{{ route('assets.index') }}" class="btn btn-primary"><i class="bx bx-cog me-1"></i>Kelola Aset</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden mb-4">
        <div class="card-body hero p-4 text-white">
            <div class="row align-items-center g-3">
                <div class="col-lg-8"><small class="text-white-50 text-uppercase fw-bold">Ringkasan Operasional</small><h3 class="text-white fw-bold mt-2 mb-2">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</h3>
                    <p class="mb-0">@if($stats['bookings']) Terdapat {{ $stats['bookings'] }} jadwal pemakaian pada {{ $stats['used'] }} aset dengan total durasi {{ number_format($stats['hours'],1,',','.') }} jam. @else Tidak ada aset yang dijadwalkan pada tanggal ini. @endif</p>
                </div>
                <div class="col-lg-4 text-lg-end"><span class="badge bg-white text-primary px-3 py-2">{{ $isToday ? 'Pantauan hari ini' : 'Pantauan terjadwal' }}</span><small class="d-block text-white-50 mt-2">Data diperbarui {{ now()->format('H:i') }} WIB</small></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['icon'=>'bx-cube','color'=>'primary','label'=>'Total Aset','value'=>$stats['total'],'detail'=>$stats['active'].' aktif'],
            ['icon'=>'bx-calendar-check','color'=>'warning','label'=>'Aset Digunakan','value'=>$stats['used'],'detail'=>$stats['bookings'].' jadwal pemakaian'],
            ['icon'=>'bx-check-shield','color'=>'success','label'=>'Aset Tersedia','value'=>$stats['available'],'detail'=>'aktif dan tidak terjadwal'],
            ['icon'=>'bx-time-five','color'=>'info','label'=>'Total Jam Pakai','value'=>number_format($stats['hours'],1,',','.'),'detail'=>'akumulasi seluruh aset'],
        ] as $stat)
            <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body p-3 p-md-4">
                <span class="avatar-initial rounded bg-label-{{ $stat['color'] }} p-2 d-inline-flex mb-3"><i class="bx {{ $stat['icon'] }} fs-4"></i></span>
                <small class="text-muted d-block">{{ $stat['label'] }}</small><h3 class="fw-bold mb-1">{{ $stat['value'] }}</h3><small class="text-muted">{{ $stat['detail'] }}</small>
            </div></div></div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7"><div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom"><h5 class="fw-bold mb-1">Proyeksi Penggunaan 7 Hari</h5><small class="text-muted">Jumlah reservasi dan aset unik sejak tanggal pantauan.</small></div>
            <div class="card-body"><div class="chart-box"><canvas id="forecastChart"></canvas></div></div>
        </div></div>
        <div class="col-xl-5"><div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom"><h5 class="fw-bold mb-1">Penggunaan per Bidang</h5><small class="text-muted">Distribusi jumlah reservasi pada tanggal terpilih.</small></div>
            <div class="card-body"><div class="chart-box"><canvas id="fieldChart"></canvas></div>@if($byField->isEmpty())<p class="text-center text-muted mt-2">Belum ada data penggunaan.</p>@endif</div>
        </div></div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header border-bottom d-flex flex-wrap justify-content-between gap-2">
            <div><h5 class="fw-bold mb-1">Timeline Pemakaian Aset</h5><small class="text-muted">Rincian aset, bidang pemakai, kegiatan, PIC, serta jam mulai dan selesai.</small></div>
            <span class="badge bg-label-primary align-self-center">{{ $usage->count() }} pemakaian</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Waktu & Status</th><th>Aset</th><th>Bidang Pemakai</th><th>Kegiatan</th><th>PIC / Pengajar</th><th class="text-center">Durasi</th></tr></thead>
                <tbody>
                @forelse($usage as $item)
                    @php $statusColor=match($item['status']){'Sedang Dipakai'=>'danger','Akan Datang'=>'info','Selesai'=>'secondary',default=>'primary'}; @endphp
                    <tr class="{{ $item['status']==='Sedang Dipakai' ? 'table-warning' : '' }}">
                        <td class="text-nowrap"><div class="fw-bold">{{ $item['start']->format('H:i') }}–{{ $item['end']->format('H:i') }}</div><span class="badge bg-label-{{ $statusColor }} mt-1">{{ $item['status'] }}</span></td>
                        <td><div class="d-flex align-items-center gap-2"><span class="avatar-initial rounded bg-label-warning p-2"><i class="bx bx-cube"></i></span><div><strong>{{ $item['asset']?->name ?? 'Aset dihapus' }}</strong><small class="d-block text-muted">{{ ucfirst($item['asset']?->type ?? '-') }} · {{ $item['asset']?->location ?? '-' }}</small></div></div></td>
                        <td><span class="badge bg-label-primary text-wrap text-start">{{ $item['bidang'] }}</span></td>
                        <td><strong>{{ $item['activity'] }}</strong><small class="d-block text-muted">{{ $item['type'] }} · {{ $item['parent'] }}</small></td>
                        <td><i class="bx bx-user me-1 text-primary"></i>{{ $item['pic'] }}</td>
                        <td class="text-center"><strong>{{ number_format($item['duration'],1,',','.') }}</strong><small class="d-block text-muted">jam</small></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-5"><i class="bx bx-calendar-x fs-1 text-muted"></i><h6 class="fw-bold mt-2">Tidak ada pemakaian aset</h6><p class="text-muted mb-0">Pilih tanggal lain untuk melihat jadwal penggunaan.</p></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm"><div class="card-header border-bottom"><h5 class="fw-bold mb-1">Komposisi Jenis Aset</h5><small class="text-muted">Jenis aset yang digunakan pada tanggal pantauan.</small></div>
        <div class="card-body"><div class="row align-items-center"><div class="col-lg-5"><div class="chart-box small-chart"><canvas id="typeChart"></canvas></div></div><div class="col-lg-7">
            @forelse($byType as $type=>$count)<div class="d-flex justify-content-between border-bottom py-3"><span><i class="bx bx-category me-2 text-primary"></i>{{ $type }}</span><strong>{{ $count }} pemakaian</strong></div>@empty<p class="text-muted text-center">Belum ada data.</p>@endforelse
        </div></div></div>
    </div>
</div>
@endsection

@push('styles')
<style>.hero{background:linear-gradient(135deg,#233a7a,#5668d8 55%,#20a6b7)}.chart-box{height:300px;position:relative}.small-chart{height:240px}@media print{.no-print,.layout-navbar,.layout-menu{display:none!important}.card{box-shadow:none!important;break-inside:avoid}}</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded',function(){
 const common={responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}};
 new Chart(document.getElementById('forecastChart'),{type:'bar',data:{labels:@json($forecast->pluck('label')),datasets:[{label:'Reservasi',data:@json($forecast->pluck('count')),backgroundColor:'#5668d8',borderRadius:6},{label:'Aset Unik',data:@json($forecast->pluck('assets')),backgroundColor:'#20a6b7',borderRadius:6}]},options:{...common,scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});
 const field=document.getElementById('fieldChart');if(@json($byField->isNotEmpty()))new Chart(field,{type:'doughnut',data:{labels:@json($byField->keys()),datasets:[{data:@json($byField->values()),backgroundColor:['#5668d8','#20a6b7','#71c68b','#ffbc5b','#e65b65','#8d70c9']}]},options:{...common,cutout:'58%'}});
 const type=document.getElementById('typeChart');if(@json($byType->isNotEmpty()))new Chart(type,{type:'pie',data:{labels:@json($byType->keys()),datasets:[{data:@json($byType->values()),backgroundColor:['#5668d8','#20a6b7','#ffbc5b','#71c68b']}]},options:common});
});
</script>
@endpush
