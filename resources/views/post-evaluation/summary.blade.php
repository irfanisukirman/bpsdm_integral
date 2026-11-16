<div class="row g-3 mb-4">

@foreach([['Alumni',$summary['total']],['Wawancara selesai',$summary['completed']],['Rerata survei',$summary['survey']['average'] === null ? '-' : number_format($summary['survey']['average'],1)],['Rerata wawancara',$summary['interview']['average'] === null ? '-' : number_format($summary['interview']['average'],1)]] as [$label,$value])
<div class="col-6 col-lg-3"><div class="card border shadow-none h-100"><div class="card-body"><small class="text-muted">{{ $label }}</small><h3 class="mt-2 mb-0">{{ $value }}</h3></div></div></div>
@endforeach
</div>
<p class="small text-muted">Nilai pada skala 0-100. Rerata keseluruhan memakai tiga indikator terisi (jangka panjang, tujuan, manfaat), bukan nilai berbobot lima kriteria. Hasil parsial: {{ $summary['partial'] }} alumni. Data kosong tidak dianggap nol.</p>
<div class="d-flex flex-wrap gap-2 mb-4">
@foreach($summary['roles'] as $role)<span class="badge bg-label-info">{{ $role['label'] }}: {{ $role['filled'] }}/{{ $summary['total'] }} alumni merespons</span>
@endforeach</div>
<div class="row g-3">
@foreach($summary['slides'] as $slide)<div class="col-xl-6"><section class="card h-100 border shadow-none"><div class="card-body"><h5>{{ $slide['title'] }}</h5>

@foreach($slide['series'] as $series)<div class="mt-3"><strong>{{ $series['label'] }}</strong><small class="d-block text-muted">Rerata {{ $series['data']['average'] === null ? 'belum tersedia' : number_format($series['data']['average'],1) }} - {{ $series['data']['valid'] }} terisi - {{ $series['data']['missing'] }} kosong</small>

@foreach($summary['categories'] as $i=>$category)<div class="d-flex align-items-center gap-2 mt-2"><span class="small" style="width:145px;flex-shrink:0">{{ $category }}</span><div class="progress flex-grow-1" style="height:12px"><div class="progress-bar {{ $loop->parent->first ? 'bg-primary' : 'bg-warning' }}" style="width:{{ $summary['total'] ? $series['data']['counts'][$i]/$summary['total']*100 : 0 }}%"></div></div><strong class="small">{{ $series['data']['counts'][$i] }}</strong></div>
@endforeach
</div>
@endforeach</div></section></div>
@endforeach</div>
<div class="card border shadow-none mt-4"><div class="card-body"><h5>Kesimpulan</h5>
@foreach($summary['conclusions'] as $text)<p>{{ $text }}</p>
@endforeach<h5 class="mt-4">Rekomendasi</h5><ul>
@foreach($summary['recommendations'] as $text)<li class="mb-2">{{ $text }}</li>
@endforeach</ul></div></div>
