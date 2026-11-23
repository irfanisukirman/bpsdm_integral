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
