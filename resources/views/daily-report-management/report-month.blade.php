@extends('layouts.master')
@section('title', 'Laporan '.$period->translatedFormat('F Y'))
@section('content')
<div class="container-xxl container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><a href="{{ route('daily-report-management.reports', ['year' => $period->year]) }}" class="btn btn-sm btn-outline-secondary mb-3"><i class="bx bx-left-arrow-alt"></i> Daftar Bulan</a><h3 class="fw-bold mb-1">Laporan {{ $period->translatedFormat('F Y') }}</h3><p class="text-muted mb-0">{{ $employees->count() }} pegawai · {{ $reports->count() }} hari laporan</p></div><a href="{{ route('daily-report-management.monthly-zip', $period->format('Y-m')) }}" class="btn btn-danger"><i class="bx bx-download me-1"></i>Unduh ZIP Semua Pegawai</a>
    </div>
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="list-group list-group-flush">
            @forelse($employees as $assignment)
                @php $employee=$assignment->user;$employeeReports=$assignment->reports;$detailId='employee-'.$assignment->id;$activityCount=$employeeReports->sum(fn($report)=>$report->items->count()); @endphp
                <div class="list-group-item p-0">
                    <div class="employee-row d-flex flex-wrap align-items-center gap-3 p-3">
                        <div class="employee-avatar"><i class="bx bx-user"></i></div><div class="flex-grow-1"><strong class="d-block">{{$employee?->name?:$assignment->employee_email}}</strong><small class="text-muted">{{$employee?->nip_nik?:$assignment->employee_email}}</small></div>
                        @if($employeeReports->isEmpty())
                            <span class="badge bg-label-danger"><i class="bx bx-error-circle me-1"></i>Belum membuat laporan</span>
                        @else
                            <div class="employee-stat"><strong>{{$employeeReports->count()}}</strong><small>Hari laporan</small></div><div class="employee-stat"><strong>{{$activityCount}}</strong><small>Kegiatan</small></div>
                            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#{{$detailId}}"><i class="bx bx-show me-1"></i>Lihat Kegiatan</button>
                        @endif
                    </div>
                    @if($employeeReports->isNotEmpty())<div class="collapse employee-detail" id="{{$detailId}}"><div class="p-3 pt-0"><div class="timeline-report">
                        @foreach($employeeReports->sortBy('report_date') as $report)
                            <div class="report-day"><div class="report-date"><span>{{$report->report_date->format('d')}}</span><small>{{$report->report_date->translatedFormat('M')}}</small></div><div class="report-content"><h6 class="fw-bold mb-2">{{$report->report_date->translatedFormat('l, d F Y')}}</h6><div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th style="width:130px">Waktu</th><th>Kegiatan</th><th>Hasil / Output / Kesimpulan</th></tr></thead><tbody>@foreach($report->items as $item)<tr><td class="text-nowrap">{{substr($item->start_time,0,5)}}–{{substr($item->end_time,0,5)}}</td><td>{{$item->activity}}</td><td>{{$item->output}}</td></tr>@endforeach</tbody></table></div></div></div>
                        @endforeach
                    </div></div></div>@endif
                </div>
            @empty
                <div class="text-center py-5 text-muted">Belum ada laporan pada bulan ini.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
@push('css')<style>.employee-avatar{width:44px;height:44px;display:grid;place-items:center;border-radius:50%;background:#eef3ff;color:#4169d8;font-size:22px}.employee-stat{text-align:center;min-width:80px}.employee-stat strong,.employee-stat small{display:block}.employee-stat small{font-size:11px;color:#7d8797}.employee-detail{background:#f7f9fc}.report-day{display:grid;grid-template-columns:58px 1fr;gap:14px;padding:14px 0;border-bottom:1px solid #e3e8f0}.report-day:last-child{border:0}.report-date{width:52px;height:56px;border-radius:12px;background:#4169d8;color:#fff;text-align:center;padding-top:5px}.report-date span{display:block;font-size:20px;font-weight:700;line-height:25px}.report-date small{text-transform:uppercase;font-size:10px}.report-content{min-width:0;background:#fff;border:1px solid #e4e9f2;border-radius:12px;padding:14px}@media(max-width:600px){.employee-stat{display:none}.report-day{grid-template-columns:1fr}.report-date{width:100%;height:auto;padding:7px 10px;text-align:left}.report-date span,.report-date small{display:inline;font-size:13px}}</style>@endpush
