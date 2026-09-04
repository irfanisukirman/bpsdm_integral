@php
    $canManage = auth()->user()->role === 'superadmin' || auth()->user()->bidang === $schedule->training?->bidang;
    $initial = collect(explode(' ', trim($schedule->pengajar?->name ?: '?')))->filter()->take(2)->map(fn($word)=>mb_substr($word,0,1))->implode('');
@endphp
<article class="schedule-item {{ $schedule->has_conflict ? 'is-conflict' : '' }}">
    <div class="schedule-time"><strong>{{ substr((string)$schedule->start_time,0,5) }}–{{ substr((string)$schedule->end_time,0,5) }}</strong><span>{{ $schedule->duration_label }}</span></div>
    <div class="schedule-teacher"><span class="teacher-avatar">{{ strtoupper($initial) }}</span><div class="min-w-0"><strong>{{ $schedule->pengajar?->name ?: 'Pengajar belum ditentukan' }}</strong><small>{{ $schedule->pengajar?->nip_nik ? 'NIP/NIK '.$schedule->pengajar->nip_nik : 'NIP/NIK tidak tersedia' }}</small></div></div>
    <div class="schedule-main"><strong>{{ $schedule->activity ?: 'Materi belum ditentukan' }}</strong><span class="training-reference"><i class="bx bx-book-open"></i><span>{{ $schedule->training?->nama_pelatihan ?: '-' }} · {{ $schedule->training?->bidang ?: '-' }}</span></span>@if($schedule->has_conflict)<span class="conflict-note"><i class="bx bx-error-circle"></i> Waktu pengajar bertabrakan dengan jadwal lain</span>@endif</div>
    <div class="schedule-place"><strong><i class="bx bx-map me-1 text-danger"></i>{{ $location }}</strong><span>{{ $schedule->link_zoom ? 'Pertemuan daring' : 'Lokasi mengajar' }}</span>@if($schedule->link_zoom)<a href="{{ $schedule->link_zoom }}" target="_blank" rel="noopener">Buka tautan Zoom</a>@endif</div>
    <div class="schedule-actions"><span class="badge bg-label-{{ $meta['tone'] }}"><i class="bx {{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}</span>@if($canManage)<a href="{{ route('trainings.schedules',$schedule->training_id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Buka jadwal pelatihan"><i class="bx bx-right-arrow-alt"></i></a>@endif</div>
</article>
