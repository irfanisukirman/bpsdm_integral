<style>.survey-overview-chart{margin:16px 0;padding:12px 0;border-bottom:1px solid #e8eeea}.survey-overview-series{display:grid;grid-template-columns:90px minmax(70px,1fr) 90px;gap:12px;align-items:center;margin:9px 0;font-size:.85rem}.survey-overview-track{height:14px;background:#eef2ef;border-radius:8px;overflow:hidden}.survey-overview-fill{display:block;height:100%;background:#15966b;border-radius:8px}.survey-overview-fill.atasan{background:#c99c2f}.survey-overview-fill.rekan{background:#6885c1}.survey-overview-question{white-space:pre-line;overflow-wrap:anywhere;line-height:1.6}.survey-overview-section{scroll-margin-top:20px}@media(max-width:600px){.survey-overview-series{grid-template-columns:70px minmax(50px,1fr) 65px;gap:8px}}</style>
<div class="row g-3 mb-4">
<section class="col-lg-6"><div class="card card-body h-100"><h5>Perubahan jabatan</h5><p class="small text-muted">Sumber: profil dan jawaban evaluasi peserta - {{ $surveySummary['job_respondents'] }} jawaban lengkap.</p>
@foreach($surveySummary['job_changes'] as $label=>$count)
<div class="survey-overview-chart"><p class="mb-2">{{ $label }} - <strong>{{ $count }}</strong></p><div class="survey-overview-track" role="img" aria-label="{{ $label }}: {{ $count }} peserta"><span class="survey-overview-fill" style="width:{{ $surveySummary['job_respondents'] ? $count/$surveySummary['job_respondents']*100 : 0 }}%"></span></div></div>
@endforeach
<small class="text-muted">{{ $surveySummary['total']-$surveySummary['job_respondents'] }} peserta belum memiliki data jabatan sebelum/sesudah yang lengkap.</small></div></section>
<section class="col-lg-6"><div class="card card-body h-100"><h5>Metode pelatihan</h5><p class="small text-muted">Sumber: jawaban metode pelaksanaan - {{ $surveySummary['method_respondents'] }} peserta merespons, tanpa menghitung ganda alumni dan atasan.</p>
@forelse($surveySummary['methods'] as $label=>$count)
<div class="survey-overview-chart"><p class="mb-2">{{ $label }} - <strong>{{ $count }}</strong></p><div class="survey-overview-track" role="img" aria-label="{{ $label }}: {{ $count }} peserta"><span class="survey-overview-fill" style="width:{{ $surveySummary['method_respondents'] ? $count/$surveySummary['method_respondents']*100 : 0 }}%"></span></div></div>
@empty
<p class="text-muted">Belum ada jawaban metode pelatihan.</p>
@endforelse
@if($surveySummary['multiple_methods'])<small class="text-muted">Jawaban dapat memuat lebih dari satu metode.</small>@endif
</div></section></div>
<section class="card card-body mb-4 survey-overview-section"><h5>Perbandingan hasil alumni dan atasan</h5><p class="text-muted">Rata-rata per indikator pada skala {{ $surveySummary['scale']===4 ? '1-4' : '0-100' }}. Hijau: alumni; emas: atasan.@if(in_array('rekan',$training->evaluationRoles(),true)) Biru: rekan kerja.@endif</p><p class="small text-muted">Dihitung otomatis dari jawaban evaluasi di database Integral, tanpa import atau pemetaan manual. Data kosong tidak dianggap nol. Kelompok indikator mengikuti bagian bank soal program {{ $training->program_evaluasi }}. Pada indikator hambatan, nilai lebih tinggi berarti persetujuan lebih kuat terhadap adanya hambatan.</p>
@forelse($surveySummary['sections'] as $section=>$indicators)
<h6 class="fw-bold mt-4">{{ $section }}</h6>
@foreach($indicators as $indicator)
<div class="survey-overview-chart"><p class="survey-overview-question">{{ $indicator['label'] }}</p>
@foreach($indicator['roles'] as $role=>$result)
<div class="survey-overview-series"><span>{{ $result['label'] }}</span><div class="survey-overview-track" role="img" aria-label="{{ $result['label'] }}: {{ $result['average']===null ? 'Belum tersedia' : number_format($result['average'],2,',','.') }}"><span class="survey-overview-fill {{ $role }}" style="width:{{ $result['average']===null ? 0 : $result['average']/$surveySummary['scale']*100 }}%"></span></div><strong>{{ $result['average']===null ? '-' : number_format($result['average'],2,',','.') }}</strong></div><small class="text-muted d-block">{{ $result['label'] }}: {{ $result['count'] }} jawaban terisi</small>
@endforeach
</div>
@endforeach
@empty
<p class="text-muted">Belum ada indikator berskala yang sesuai untuk pelatihan ini.</p>
@endforelse
</section>
<section class="card card-body mb-4"><h5>Sebaran asal instansi</h5><p class="small text-muted">Jumlah peserta yang disetujui, bersumber dari data peserta Integral. Total {{ $surveySummary['total'] }} peserta.</p>
@forelse($surveySummary['agencies'] as $agency=>$count)
<div class="survey-overview-chart"><p>{{ $agency }} - {{ $count }} peserta</p><div class="survey-overview-track"><span class="survey-overview-fill" style="width:{{ $surveySummary['total'] ? $count/$surveySummary['total']*100 : 0 }}%"></span></div></div>
@empty
<p class="text-muted">Belum ada peserta disetujui.</p>
@endforelse
</section>
