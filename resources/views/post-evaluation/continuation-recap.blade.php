<section class="card card-body mb-4"><h5>Rekap keberlanjutan hasil wawancara</h5><p class="text-muted small">Konfirmasi YA/TIDAK pada lima kriteria. Rerata persentase hanya dari jawaban YA yang memiliki nilai. Data kosong bukan nilai nol; wawancara draf tetap ditampilkan.</p>
<div class="row g-3">
@foreach($continuationRecap ?? $summary['continuation_recap'] ?? [] as $row)
<div class="col-lg-6"><div class="border rounded-3 p-3 h-100"><h6>{{ $row['label'] }}</h6>
@foreach([['YA',$row['yes'],'bg-success'],['TIDAK',$row['no'],'bg-danger'],['Belum dikonfirmasi',$row['unknown'],'bg-secondary']] as [$label,$count,$color])
<div class="small mb-2">{{ $label }}: <strong>{{ $count }} orang</strong><div class="progress mt-1" style="height:8px" role="img" aria-label="{{ $label }}: {{ $count }} dari {{ $row['total'] }} peserta"><div class="progress-bar {{ $color }}" style="width:{{ $row['total'] ? $count/$row['total']*100 : 0 }}%"></div></div></div>
@endforeach
<p class="small mb-1">Rerata persentase jawaban YA: <strong>{{ $row['average_yes']===null ? 'Belum tersedia' : number_format($row['average_yes'],2,',','.').'%' }}</strong></p><small class="text-muted">{{ $row['percentage_filled'] }} dari {{ $row['yes'] }} jawaban YA memiliki nilai; {{ $row['draft'] }} konfirmasi masih draf.</small>
</div></div>
@endforeach
</div></section>
