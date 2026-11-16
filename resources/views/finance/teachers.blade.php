<div class="tab-pane fade" id="teacherTab">
 <div class="card border-0 shadow-sm mb-3"><div class="card-body"><h5 class="fw-bold mb-1">Pengajar & Kelengkapan Dokumen</h5><p class="text-muted mb-0">Semua pengajar terjadwal pada tahun {{ $year }} dari semua bidang, termasuk jadwal mendatang dan honor yang sudah diproses.</p></div></div>
 @forelse($teacherTrainings as $schedules)
 @php $training=$schedules->first()->training; $teachers=$schedules->groupBy('pengajar_id')->sortBy(fn($items)=>$items->first()->pengajar?->name,SORT_NATURAL|SORT_FLAG_CASE); @endphp
 <section class="card border-0 shadow-sm mb-4">
 <div class="card-header border-bottom bg-light"><h5 class="fw-bold mb-1">{{ $training->nama_pelatihan }}</h5><small class="text-muted">{{ $training->bidang }} &middot; {{ $teachers->count() }} pengajar &middot; {{ $schedules->count() }} jadwal mengajar</small></div>
 @foreach($teachers as $items)
 @php $teacher=$items->first()->pengajar; $completeSchedules=$items->filter(fn($item)=>!in_array(false,$item->finance_checks,true))->count(); @endphp
 <div class="p-3 border-bottom"><div class="d-flex flex-wrap justify-content-between gap-2 mb-3"><div><strong>{{ $teacher?->name ?? 'Akun pengajar tidak tersedia' }}</strong><small class="d-block text-muted">{{ $teacher?->nip_nik ?: 'NIP/NIK belum lengkap' }}</small></div><div><span class="badge bg-label-{{ $completeSchedules===$items->count()?'success':'warning' }}">{{ $completeSchedules===$items->count()?'Lengkap':'Belum lengkap' }}</span><small class="d-block text-muted mt-1">{{ $completeSchedules }}/{{ $items->count() }} jadwal lengkap</small></div></div>
 <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Materi & Waktu</th><th>Durasi</th><th style="min-width:250px">Data & Dokumen</th><th>Status Honor</th><th></th></tr></thead><tbody>
 @foreach($items as $schedule)
 @php $checks=$schedule->finance_checks; $missing=collect($checks)->filter(fn($complete)=>!$complete)->keys(); $payment=$schedule->financePayment; $upcoming=substr((string)$schedule->date,0,10)>today()->toDateString(); @endphp
 <tr><td><strong>{{ $schedule->activity }}</strong><small class="d-block text-muted">{{ \Carbon\Carbon::parse($schedule->date)->translatedFormat('d M Y') }}</small><small class="text-muted">{{ substr($schedule->start_time,0,5) }} - {{ substr($schedule->end_time,0,5) }}</small>@if($upcoming)<small class="d-block text-info">Jadwal mendatang</small>@endif</td><td><span class="badge bg-label-info">{{ $schedule->duration_label }}</span></td>
 <td><span class="badge bg-label-{{ $missing->isEmpty()?'success':'warning' }}">{{ count($checks)-$missing->count() }}/{{ count($checks) }} lengkap</span><details class="mt-2"><summary class="small text-primary" style="cursor:pointer">Lihat rincian kelengkapan</summary><ul class="list-unstyled small mt-2 mb-0">@foreach($checks as $label=>$complete)<li class="{{ $complete?'text-success':'text-danger' }}"><i class="bx {{ $complete?'bx-check-circle':'bx-x-circle' }} me-1"></i>{{ $label }}: {{ $complete?'Tersedia':'Belum lengkap' }}</li>@endforeach</ul></details>@if($missing->isNotEmpty())<small class="d-block text-muted mt-2">Kurang: {{ $missing->implode(', ') }}</small>@endif</td>
 <td>@if($payment)<span class="badge bg-label-{{ $statusColor[$payment->status]??'secondary' }}">{{ $payment->status_label }}</span>@else<span class="badge bg-label-secondary">Belum diajukan</span>@endif</td>
 <td class="text-end">@if(!$payment && $training->bidang!==$bidang)<small class="text-muted">{{ Auth::user()->role==='superadmin'?'Pilih bidang pelatihan pada filter anggaran untuk memproses honor.':'Honor diproses pengelola keuangan bidang terkait.' }}</small>@elseif(!$payment)<button type="button" class="btn btn-sm btn-primary pay-teacher" data-bs-toggle="modal" data-bs-target="#teacherPayModal" data-action="{{ route('finance.teacher.store',$schedule) }}" data-name="{{ $teacher?->name }}" data-activity="{{ $training->nama_pelatihan }} - {{ $schedule->activity }}" data-unit="{{ $schedule->duration_label }}" @disabled($missing->isNotEmpty()||$upcoming) title="{{ $upcoming?'Menunggu jadwal terlaksana':($missing->isNotEmpty()?'Lengkapi data dan dokumen terlebih dahulu':'Proses honor') }}">Proses Honor</button>@else<small class="text-muted">Sudah masuk transaksi</small>@endif</td></tr>
 @endforeach
 </tbody></table></div></div>
 @endforeach
 </section>
 @empty
 <div class="card card-body text-center py-5"><p class="text-muted mb-0">Belum ada pengajar terjadwal pada tahun yang dipilih.</p></div>
 @endforelse
</div>
