
@extends('layouts.master')

@section('title','Hasil Evaluasi Pasca Level 3 & 4')

@section('content')
<style>.pasca-table{min-width:820px}.pasca-table td{vertical-align:top;white-space:normal;overflow-wrap:anywhere}.pasca-question{white-space:pre-line;max-width:600px}.pasca-mapping{min-width:800px;width:100%;table-layout:fixed}.pasca-mapping th:nth-child(1){width:17%}.pasca-mapping th:nth-child(2){width:13%}.pasca-mapping th:nth-child(3){width:50%}.pasca-mapping th:nth-child(4){width:20%}.pasca-mapping select{min-width:110px}.pasca-settings .form-label{font-weight:600}.pasca-tabs{overflow-x:auto;flex-wrap:nowrap}.pasca-tabs .nav-link{white-space:nowrap}</style>
<a href="{{ route('post-evaluation.index') }}" class="small">&larr; Semua hasil pelatihan</a>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 my-3"><div><span class="badge bg-label-primary mb-2">{{ $training->program_evaluasi }}</span><h4 class="fw-bold">{{ $training->nama_pelatihan }}</h4><p class="text-muted">Hasil Evaluasi Pasca Level 3 &amp; 4 - Angkatan {{ $training->angkatan }}</p></div><div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-primary" href="{{ route('post-evaluation.expose',$training) }}">Ekspose</a><a class="btn btn-primary" href="{{ route('post-evaluation.pdf',$training) }}">Unduh PDF</a>
@if($manager)<a class="btn btn-outline-success" href="{{ route('evall34.export',$training) }}">Excel Survei</a>
@endif<a class="btn btn-outline-secondary" href="{{ route('post-evaluation.proper.public',$training) }}" target="_blank" rel="noopener">Link Pengisian Proper</a></div></div>

@foreach(['success'=>'success','error'=>'danger'] as $key=>$tone)
@if(session($key))<div class="alert alert-{{ $tone }}">{{ session($key) }}</div>
@endif
@endforeach

@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">
@foreach($errors->all() as $error)<li>{{ $error }}</li>
@endforeach</ul></div>
@endif
<div class="card card-body mb-3"><label class="form-label fw-bold">Link publik pengisian inovasi / Proper</label><input class="form-control" readonly onclick="this.select()" value="{{ route('post-evaluation.proper.public',$training) }}"><small class="text-muted mt-2">Bagikan kepada alumni pelatihan ini. Alumni memasukkan NIP/NIK, melengkapi Proper, milestone, kondisi terkini dan dokumen pendukung.</small></div>
<ul class="nav nav-pills pasca-tabs gap-2 mb-4">
@foreach(['ringkasan'=>'Ringkasan','jawaban'=>'Jawaban','peserta'=>'Peserta & Progres','wawancara'=>'Wawancara','pengaturan'=>'Pengaturan'] as $key=>$label)
@if($manager || $key !== 'pengaturan')<li class="nav-item"><a class="nav-link {{ $tab === $key ? 'active' : '' }}" href="{{ route('post-evaluation.show',[$training,'tab'=>$key]) }}">{{ $label }}</a></li>
@endif
@endforeach</ul>

@if(!$manager)<div class="alert alert-info">Ruang tim: daftar dan ringkasan hanya mencakup alumni yang ditugaskan kepada tim Anda.</div>
@endif

@if($tab === 'ringkasan')

@if(empty($setting?->mappings))<div class="alert alert-warning">Indikator survei belum dipetakan. Jawaban tetap tersedia; nilai perbandingan belum dihitung.
@if($manager) <a href="{{ route('post-evaluation.show',[$training,'tab'=>'pengaturan']) }}">Atur pemetaan</a>.
@endif</div>
@endif

@include('post-evaluation.summary')

@elseif($tab === 'jawaban')
<form method="GET" class="input-group mb-3"><input type="hidden" name="tab" value="jawaban"><input name="q" value="{{ $search }}" class="form-control" maxlength="100" placeholder="Cari alumni, pertanyaan atau jawaban"><button class="btn btn-primary">Cari</button></form>
<div class="card table-responsive"><table class="table pasca-table"><thead><tr><th>Alumni</th><th>Penilai / Bagian</th><th>Pertanyaan</th><th>Jawaban</th></tr></thead><tbody>

@foreach($answerRows as $answerRow)
@php $person=$answerRow['person']; $answer=$answerRow['answer']; @endphp
<tr class="pasca-filter-row"><td>{{ $person['participant']->name }}</td><td>{{ $training->evaluationRoleLabels()[$answer->evaluator_role] }}<small class="d-block">{{ $answer->question?->sub_category }}</small></td><td class="pasca-question">{{ $answer->question?->question_text }}</td><td>
@php $decoded=json_decode($answer->note ?? '',true); 
@endphp{{ $answer->score !== null ? $answer->score : (is_array($decoded) ? implode(', ',$decoded) : $answer->note) }}</td></tr>
@endforeach
</tbody></table></div>

@elseif(in_array($tab,['peserta','wawancara']))
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
@php $p=$person['participant']; $iv=$person['interview']; 
@endphp<tr class="pasca-filter-row">

@if($manager && $tab==='wawancara')<td><input class="form-check-input" type="checkbox" name="participant_ids[]" value="{{ $p->id }}" form="assignParticipants" aria-label="Pilih {{ $p->name }}"></td>
@endif
<td><strong>{{ $p->name }}</strong><small class="d-block text-muted">{{ $p->nip_nik }}<br>{{ $p->instansi }}</small></td><td>
@foreach($person['roles'] as $role=>$filled)<small class="d-block">{{ $training->evaluationRoleLabels()[$role] }}: {{ $filled ? 'Sudah' : 'Belum' }}</small>
@endforeach</td>
<td>{{ $person['survey_average'] === null ? '-' : number_format($person['survey_average'],1) }}
@if($person['partial'])<small class="d-block text-warning">Parsial / belum lengkap</small>
@endif</td><td>Rerata: {{ $person['interview_average'] === null ? '-' : number_format($person['interview_average'],1) }}<small class="d-block">Berbobot: {{ $person['weighted'] === null ? '-' : number_format($person['weighted'],1) }}</small></td><td>{{ $person['team'] }}<small class="d-block text-muted mt-1">Proper: {{ filled($iv?->project) ? 'Sudah diisi' : 'Belum diisi' }}</small><span class="badge {{ $iv?->status === 'completed' ? 'bg-label-success' : 'bg-label-warning' }} d-block mt-2">{{ $iv?->status === 'completed' ? 'Selesai' : 'Draf' }}</span></td>
<td><a class="btn btn-sm btn-outline-primary" href="{{ route('post-evaluation.interview',[$training,$p]) }}">{{ $iv ? 'Buka Wawancara' : 'Mulai Wawancara' }}</a>

@if($manager)<form action="{{ route('post-evaluation.upload-link',[$training,$p]) }}" method="POST" class="mt-2">
@csrf<button class="btn btn-sm btn-outline-secondary" name="action" value="rotate">{{ $iv?->upload_token ? 'Ganti Link Proper' : 'Buat Link Proper Alumni' }}</button>
@if($iv?->upload_token)<button name="action" value="revoke" class="btn btn-sm btn-outline-danger mt-1">Nonaktifkan</button>
@endif</form>
@if($iv?->upload_token)<input aria-label="Link unggah bukti {{ $p->name }}" class="form-control form-control-sm mt-2" readonly value="{{ route('post-evaluation.upload.public',$iv->upload_token) }}" onclick="this.select()"><small>Bagikan hanya kepada alumni ini.</small>
@endif
@endif
</td></tr>
@empty<tr><td colspan="7">Belum ada peserta disetujui / ditugaskan.</td></tr>
@endforelse</tbody></table></div>

@elseif($tab === 'pengaturan')
<div class="pasca-settings">
<div class="card card-body mb-4"><h5>Pemetaan indikator survei dan rubrik</h5><p class="text-muted">Pilih satu pertanyaan per indikator dan peran. Skala empat: Tidak Sesuai=25, Kurang Sesuai=50, Sesuai=75, Sangat Sesuai=100. Slider menggunakan skor aslinya. Jika beberapa peran dipilih, bobotnya sama; rerata memakai nilai yang terisi dan kekurangannya ditandai parsial. Pertanyaan teks/checkbox tidak otomatis diberi skor.</p>
<form action="{{ route('post-evaluation.settings',$training) }}" method="POST">
@csrf
<div class="table-responsive"><table class="table pasca-mapping"><thead><tr><th>Indikator</th><th>Peran</th><th>Pertanyaan</th><th>Skala</th></tr></thead><tbody>
@php $mappingIndex=0; 
@endphp

@foreach(\App\Services\PostEvaluationService::INDICATORS as $indicator=>$label)
@foreach($training->evaluationRoleLabels() as $role=>$roleLabel)

@php $m=collect($setting?->mappings ?? [])->first(fn($m)=>$m['indicator']===$indicator && $m['role']===$role); $i=$mappingIndex++; 
@endphp
<tr><td>{{ $label }}<input type="hidden" name="mappings[{{ $i }}][indicator]" value="{{ $indicator }}"></td><td>{{ $roleLabel }}<input type="hidden" name="mappings[{{ $i }}][role]" value="{{ $role }}"></td><td><select class="form-select" name="mappings[{{ $i }}][question_id]"><option value="">Tidak dipakai</option>
@foreach($questions->where('category','l34_'.$role)->whereIn('type',['slider','dropdown']) as $q)<option value="{{ $q->id }}" 
@selected(old('mappings.'.$i.'.question_id',$m['question_id'] ?? '')==$q->id)>#{{ $q->id }} - {{ $q->sub_category }} - {{ \Illuminate\Support\Str::limit(preg_replace('/\s+/u',' ',$q->question_text),150) }}</option>
@endforeach</select></td><td><select class="form-select" name="mappings[{{ $i }}][scale]"><option value="four" 
@selected(old('mappings.'.$i.'.scale',$m['scale'] ?? 'four')==='four')>Pilihan 1-4</option><option value="hundred" 
@selected(old('mappings.'.$i.'.scale',$m['scale'] ?? '')==='hundred')>Slider 0-100</option></select></td></tr>

@endforeach
@endforeach</tbody></table></div>
<h6 class="mt-4">Lima kriteria wawancara ? total bobot 100%</h6><p class="small text-muted">Urutan kriteria 2-4 menjadi pembanding tiga indikator survei. Sesuaikan istilah aksi perubahan, proyek, aktualisasi, atau hasil pelatihan menurut program.</p>

@foreach($rubric as $i=>$criterion)<div class="row g-2 mb-2"><div class="col-md-9"><label class="form-label" for="criterion-{{ $i }}">Kriteria {{ $i+1 }}</label><input id="criterion-{{ $i }}" class="form-control" name="rubric[{{ $i }}][label]" value="{{ old('rubric.'.$i.'.label',$criterion['label']) }}" required maxlength="200"></div><div class="col-md-3"><label class="form-label" for="weight-{{ $i }}">Bobot (%)</label><input id="weight-{{ $i }}" class="form-control" type="number" min="0" max="100" name="rubric[{{ $i }}][weight]" value="{{ old('rubric.'.$i.'.weight',$criterion['weight']) }}" required></div></div>
@endforeach<details class="border rounded p-3 mt-3"><summary class="fw-bold">Sesuaikan pedoman nilai menurut program</summary><p class="small text-muted mt-2">Pedoman ditampilkan pada form wawancara. Sesuaikan istilah dan bukti untuk LATSAR, PKP, PKA, PKN atau Teknis.</p>
@foreach($rubric as $ri=>$criterion)<h6 class="mt-3">Kriteria {{ $ri+1 }}: {{ $criterion['label'] }}</h6>
@foreach(\App\Services\PostEvaluationService::SCORE_GUIDES[$ri] as $gi=>$guide)<label class="form-label small">{{ $guide['range'] }}</label><textarea class="form-control mb-2" name="rubric[{{ $ri }}][guides][{{ $gi }}]" maxlength="1000" rows="2" required>{{ old('rubric.'.$ri.'.guides.'.$gi,$criterion['guides'][$gi] ?? $guide['description']) }}</textarea>
@endforeach
@endforeach</details><button class="btn btn-primary mt-3">Simpan Pemetaan & Rubrik</button></form></div>
<div class="card card-body mb-4"><h5>Tim wawancara</h5>
@foreach($teams as $team)<div class="d-flex justify-content-between gap-2 border-bottom py-2"><div><strong>{{ $team->name }}</strong><small class="d-block">{{ $members->get($team->id,collect())->pluck('name')->implode(', ') }}</small></div><form action="{{ route('post-evaluation.teams.delete',[$training,$team->id]) }}" method="POST" onsubmit="return confirm('Hapus tim ini? Nilai wawancara tetap tersimpan.')">
@csrf 
@method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus Tim</button></form></div>
@endforeach
<form action="{{ route('post-evaluation.team',$training) }}" method="POST" class="mt-3">
@csrf<label class="form-label">Nama tim</label><input name="name" class="form-control mb-3" required maxlength="150"><label class="form-label">Anggota dari akun Integral (dapat memilih beberapa)</label><select name="users[]" class="form-select mb-3" multiple required size="6">
@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }} - {{ $user->role }}</option>
@endforeach</select><button class="btn btn-outline-primary">Tambah Tim</button></form></div>
<div class="card card-body"><h5>Publikasi hasil dan ekspose</h5><p>Link publik menampilkan ringkasan agregat tanpa identitas, jawaban individu, atau catatan wawancara. Hasil publik adalah salinan pada waktu publikasi, bukan data yang berubah otomatis.</p>

@if($setting?->public_token)<label class="form-label">Link hasil publik</label><input class="form-control mb-2" readonly onclick="this.select()" value="{{ route('post-evaluation.public',$setting->public_token) }}"><label class="form-label">Link ekspose publik</label><input class="form-control mb-2" readonly onclick="this.select()" value="{{ route('post-evaluation.public.expose',$setting->public_token) }}"><small class="mb-3">Diterbitkan {{ $setting->published_at?->translatedFormat('d M Y H:i') }}</small>
@else<p class="text-muted">Belum dipublikasikan.</p>
@endif
<form action="{{ route('post-evaluation.publish',$training) }}" method="POST" class="d-flex flex-wrap gap-2" onsubmit="return confirm('Terapkan perubahan publikasi hasil agregat?')">
@csrf<button class="btn btn-primary" name="action" value="publish">{{ $setting?->public_token ? 'Perbarui Hasil Publik' : 'Publikasikan Hasil' }}</button>
@if($setting?->public_token)<button class="btn btn-outline-secondary" name="action" value="rotate">Ganti Link & Perbarui</button><button class="btn btn-outline-danger" name="action" value="revoke">Nonaktifkan Link</button>
@endif</form></div>
</div>

@endif

@if($paginator)<div class="mt-4">{{ $paginator->links() }}</div>@endif
@endsection

