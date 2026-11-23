<section class="card card-body mb-4"><h5>Sebaran asal instansi</h5><p class="small text-muted">Jumlah peserta yang disetujui, bersumber dari data peserta Integral. Total {{ $surveySummary['total'] }} peserta.</p>
@forelse($surveySummary['agencies'] as $agency=>$count)
<div class="survey-overview-chart"><p>{{ $agency }} - {{ $count }} peserta</p><div class="survey-overview-track"><span class="survey-overview-fill" style="width:{{ $surveySummary['total'] ? $count/$surveySummary['total']*100 : 0 }}%"></span></div></div>
@empty
<p class="text-muted">Belum ada peserta disetujui.</p>
@endforelse
</section>
