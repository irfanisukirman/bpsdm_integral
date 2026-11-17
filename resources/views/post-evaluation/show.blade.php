
@extends('layouts.master')

@section('title','Hasil Evaluasi Pasca Level 3 & 4')

@section('content')
<style>.pasca-table{min-width:820px}.pasca-table td{vertical-align:top;white-space:normal;overflow-wrap:anywhere}.pasca-question{white-space:pre-line;max-width:600px}.pasca-mapping{min-width:800px;width:100%;table-layout:fixed}.pasca-mapping th:nth-child(1){width:17%}.pasca-mapping th:nth-child(2){width:13%}.pasca-mapping th:nth-child(3){width:50%}.pasca-mapping th:nth-child(4){width:20%}.pasca-mapping select{min-width:110px}.pasca-settings .form-label{font-weight:600}.pasca-tabs{overflow-x:auto;flex-wrap:nowrap}.pasca-tabs .nav-link{white-space:nowrap}</style>
@if($manager)
<a href="{{ route('trainings.manage', $training->id) }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-arrow-back me-1"></i> Kembali ke Kelola Pelatihan</a>
@else
<a href="{{ route('post-evaluation.index') }}" class="small">&larr; Semua hasil pelatihan</a>
@endif
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 my-3"><div><span class="badge bg-label-primary mb-2">{{ $training->program_evaluasi }}</span><h4 class="fw-bold">{{ $training->nama_pelatihan }}</h4><p class="text-muted">Hasil Evaluasi Pasca Level 3 &amp; 4 - Angkatan {{ $training->angkatan }}</p></div>@if($manager)<div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-primary" href="{{ route('post-evaluation.expose',$training) }}">Ekspose</a><a class="btn btn-primary" href="{{ route('post-evaluation.pdf',$training) }}">Unduh PDF</a>
@if($manager)<a class="btn btn-outline-success" href="{{ route('evall34.export',$training) }}">Excel Survei</a>
@endif<a class="btn btn-outline-secondary" href="{{ route('post-evaluation.proper.public',$training->proper_uid) }}" target="_blank" rel="noopener">Link Pengisian Proper</a></div>@endif</div>

@foreach(['success'=>'success','error'=>'danger'] as $key=>$tone)
@if(session($key))<div class="alert alert-{{ $tone }}">{{ session($key) }}</div>
@endif
@endforeach

@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">
@foreach($errors->all() as $error)<li>{{ $error }}</li>
@endforeach</ul></div>
@endif
@if($manager)
<div class="card card-body mb-3"><label class="form-label fw-bold">Link publik pengisian inovasi / Proper</label><input class="form-control" readonly onclick="this.select()" value="{{ route('post-evaluation.proper.public',$training->proper_uid) }}"><small class="text-muted mt-2">Bagikan kepada alumni pelatihan ini. Alumni memasukkan NIP/NIK, melengkapi Proper, milestone, kondisi terkini dan dokumen pendukung.</small></div>
@endif
<ul class="nav nav-pills pasca-tabs gap-2 mb-4">
@foreach(['ringkasan'=>'Ringkasan','jawaban'=>'Jawaban','wawancara'=>'Peserta & Wawancara','pengaturan'=>'Pengaturan','import'=>'Import Data'] as $key=>$label)
@if($manager || in_array($key,['ringkasan','wawancara']))<li class="nav-item"><a class="nav-link {{ $tab === $key ? 'active' : '' }}" href="{{ route('post-evaluation.show',[$training,'tab'=>$key]) }}">{{ $label }}</a></li>
@endif
@endforeach</ul>

@if(!$manager)<div class="alert alert-info">Ruang tim: daftar dan ringkasan hanya mencakup alumni yang ditugaskan kepada tim Anda.</div>
@endif

@if($tab === 'ringkasan')

@if($manager)
@include('post-evaluation.ai-expose')
@endif
@include('post-evaluation.summary')

@elseif($tab === 'jawaban')
<form method="GET" class="input-group mb-3"><input type="hidden" name="tab" value="jawaban"><input name="q" value="{{ $search }}" class="form-control" maxlength="100" placeholder="Cari alumni, pertanyaan atau jawaban"><button class="btn btn-primary">Cari</button></form>
<div class="card table-responsive"><table class="table pasca-table"><thead><tr><th>Alumni</th><th>Penilai / Bagian</th><th>Pertanyaan</th><th>Jawaban</th></tr></thead><tbody>

@foreach($answerRows as $answerRow)
@php
    $person = $answerRow['person'];
    $answer = $answerRow['answer'];
    $decoded = json_decode($answer->note ?? '', true);
    $displayAnswer = $answer->score !== null ? $answer->score : (is_array($decoded) ? implode(', ', $decoded) : $answer->note);
@endphp
<tr class="pasca-filter-row"><td>{{ $person['participant']->name }}</td><td>{{ $training->evaluationRoleLabels()[$answer->evaluator_role] }}<small class="d-block">{{ $answer->question?->sub_category }}</small></td><td class="pasca-question">{{ $answer->question?->question_text }}</td><td>
    {{ $displayAnswer }}
</td></tr>
@endforeach
</tbody></table></div>

@elseif($tab === 'wawancara')
<form method="GET" class="input-group mb-3"><input type="hidden" name="tab" value="{{ $tab }}"><input name="q" value="{{ $search }}" class="form-control" maxlength="100" placeholder="Cari alumni / instansi / tim"><button class="btn btn-primary">Cari</button></form>

@if($manager && $tab === 'wawancara')
<form action="{{ route('post-evaluation.assign',$training) }}" method="POST" id="assignParticipants" class="d-flex flex-wrap gap-2 mb-3">
@csrf<select name="team_id" class="form-select w-auto"><option value="">Lepas penugasan tim</option>
@foreach($teams as $team)<option value="{{ $team->id }}">{{ $team->name }}</option>
@endforeach</select><button class="btn btn-primary">Simpan Pembagian Peserta</button><small class="align-self-center text-muted">Centang peserta di tabel.</small></form>

@endif
<div class="card table-responsive"><table class="table pasca-table"><thead><tr>
@if($manager && $tab==='wawancara')<th>Pilih</th>
@endif<th>Alumni</th><th>Respons penilai</th><th>Survei</th><th>Wawancara</th><th>Tim / Status</th><th>Aksi</th></tr></thead><tbody>

@forelse($people as $person)
@php
    $p = $person['participant'];
    $iv = $person['interview'];
@endphp
<tr class="pasca-filter-row">

@if($manager && $tab==='wawancara')<td><input class="form-check-input" type="checkbox" name="participant_ids[]" value="{{ $p->id }}" form="assignParticipants" aria-label="Pilih {{ $p->name }}"></td>
@endif
<td><strong>{{ $p->name }}</strong><small class="d-block text-muted">{{ $p->nip_nik }}<br>{{ $p->instansi }}</small></td><td>
@foreach($person['roles'] as $role=>$filled)<small class="d-block">{{ $training->evaluationRoleLabels()[$role] }}: {{ $filled ? 'Sudah' : 'Belum' }}</small>
@endforeach</td>
<td>{{ $person['survey_average'] === null ? '-' : number_format($person['survey_average'],1) }}
@if($person['partial'])<small class="d-block text-warning">Parsial / belum lengkap</small>
@endif</td><td>Rerata: {{ $person['interview_average'] === null ? '-' : number_format($person['interview_average'],1) }}<small class="d-block">Berbobot: {{ $person['weighted'] === null ? '-' : number_format($person['weighted'],1) }}</small></td><td>{{ $person['team'] }}<small class="d-block text-muted mt-1">Proper: {{ filled($iv?->project) ? 'Sudah diisi' : 'Belum diisi' }}</small><span class="badge {{ $iv?->status === 'completed' ? 'bg-label-success' : 'bg-label-warning' }} d-block mt-2">{{ $iv?->status === 'completed' ? 'Selesai' : 'Draf' }}</span></td>
<td><a class="btn btn-sm btn-outline-primary" href="{{ route('post-evaluation.interview',[$training,$p]) }}">{{ $iv ? 'Buka Wawancara' : 'Mulai Wawancara' }}</a>

</td></tr>
@empty<tr><td colspan="7">Belum ada peserta disetujui / ditugaskan.</td></tr>
@endforelse</tbody></table></div>

@elseif($tab === 'import')
@include('post-evaluation.import')

@elseif($tab === 'pengaturan')
@include('post-evaluation.team-settings')

@endif

@if($paginator)<div class="mt-4">{{ $paginator->links() }}</div>@endif
@endsection

