<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="referrer" content="no-referrer"><title>Ekspose Evaluasi Pasca - {{ $summary['training'] }}</title><style>

@page{margin:18px}*{box-sizing:border-box}body{font-family:DejaVu Sans,sans-serif;font-size:13px;color:#25344b;background:#e9edf5;margin:0}h1{font-size:30px;color:#303a89}h2{font-size:22px;color:#303a89}h3{font-size:17px}.slide{background:white;padding:32px;max-width:1100px;min-height:560px;margin:25px auto;page-break-after:always;overflow-wrap:anywhere}.slide:last-child{page-break-after:auto}.tag{color:#535cb1;font-weight:bold}.muted{color:#68788f}.stat{display:inline-block;border:1px solid #dce2eb;padding:16px;margin:5px;min-width:160px}.stat b{display:block;font-size:26px}.chart{width:100%;border-collapse:collapse;margin:12px 0}.chart td,.chart th{padding:6px;border-bottom:1px solid #e4e8f1;text-align:left}.bar-track{background:#edf0f8;width:100%;height:12px}.bar{height:12px;background:#5f63ce}.second{background:#d69b33}.foot{color:#748096;font-size:10px;margin-top:18px}.controls{position:sticky;top:0;background:#25344b;color:white;padding:12px;text-align:center;z-index:2}.controls button,.controls a{display:inline-block;background:white;color:#26334b;padding:8px 14px;margin:3px;border:0;border-radius:7px;text-decoration:none;cursor:pointer}.presentation-mode .slide{display:none}.presentation-mode .slide.active{display:block}li{margin:12px 0;line-height:1.6}

@media(max-width:700px){.slide{margin:12px;padding:20px;min-height:400px}h1{font-size:23px}.chart td{font-size:11px}}

@media print{body{background:white}.controls{display:none}.slide,.presentation-mode .slide{display:block!important;margin:0;max-width:none;min-height:0;padding:20px}}

@if($pdf)
.slide{min-height:0;margin:0;padding:18px}.slide h2{font-size:20px}.chart{margin:8px 0}.chart td{padding:4px}
@endif
</style></head><body>

@if(!$pdf)<nav class="controls" aria-label="Kontrol presentasi"><button id="toggleSlides" type="button">Mode Presentasi</button><button id="previousSlide" type="button">&larr; Sebelumnya</button><span id="slidePosition" aria-live="polite"></span><button id="nextSlide" type="button">Berikutnya &rarr;</button><button onclick="window.print()" type="button">Cetak</button><a href="{{ $public ? route('post-evaluation.public.pdf',request()->route('token')) : route('post-evaluation.pdf',request()->route('training')) }}">Unduh PDF</a></nav>
@endif
<section class="slide active"><p class="tag">INTEGRAL - EVALUASI PASCA PELATIHAN</p><h1>{{ $summary['training'] }}</h1><h2>Ekspose Evaluasi Level 3 &amp; 4</h2><p>Program {{ $summary['program'] }}</p><p class="muted">Hasil survei dan wawancara, kesimpulan dan tindak lanjut.</p><p class="foot">{{ $public ? 'Ringkasan publik - salinan hasil yang diterbitkan pengelola' : 'Hasil internal - data terkini yang dapat Anda akses' }}<br>Data per {{ \Carbon\Carbon::parse($summary['generated_at'])->translatedFormat('d M Y H:i') }}</p></section>
<section class="slide"><h2>Cakupan dan kelengkapan hasil</h2><div class="stat">Alumni<b>{{ $summary['total'] }}</b></div><div class="stat">Wawancara selesai<b>{{ $summary['completed'] }}</b></div><div class="stat">Survei parsial / kosong<b>{{ $summary['partial'] }}</b></div><table class="chart"><thead><tr><th>Penilai</th><th>Alumni merespons</th></tr></thead><tbody>
@foreach($summary['roles'] as $role)<tr><td>{{ $role['label'] }}</td><td>{{ $role['filled'] }} / {{ $summary['total'] }}</td></tr>
@endforeach</tbody></table><p>Data kosong tetap kosong. Rerata menggunakan indikator terisi; hasil parsial perlu dilengkapi. Nilai survei tidak menggantikan wawancara.</p></section>

@foreach($summary['slides'] as $slide)<section class="slide"><h2>{{ $slide['title'] }}</h2>

@foreach($slide['series'] as $series)<h3>{{ $series['label'] }} - Rerata {{ $series['data']['average'] === null ? 'belum tersedia' : number_format($series['data']['average'],1) }}</h3><table class="chart"><tbody>
@foreach($summary['categories'] as $i=>$category)<tr><td style="width:210px">{{ $category }}</td><td><div class="bar-track"><div class="bar {{ $loop->parent->first ? '' : 'second' }}" style="width:{{ $summary['total'] ? $series['data']['counts'][$i]/$summary['total']*100 : 0 }}%"></div></div></td><td style="width:45px">{{ $series['data']['counts'][$i] }}</td></tr>
@endforeach</tbody></table><p class="muted">{{ $series['data']['valid'] }} alumni dengan nilai - {{ $series['data']['missing'] }} belum memiliki nilai</p>
@endforeach
<p class="foot">Skala 0-100. Perbandingan deskriptif; belum membuktikan perubahan dampak dari waktu ke waktu.</p></section>
@endforeach
<section class="slide"><h2>Kesimpulan</h2><ul>
@foreach($summary['conclusions'] as $text)<li>{{ $text }}</li>
@endforeach</ul><p class="muted">Rerata keseluruhan memakai tiga indikator terisi: jangka panjang, tujuan, manfaat. Nilai berbobot lima kriteria adalah metrik terpisah.</p></section>
<section class="slide"><h2>Rekomendasi dan tindak lanjut</h2><ol>
@foreach($summary['recommendations'] as $text)<li>{{ $text }}</li>
@endforeach</ol><p class="foot">{{ $public ? 'Identitas alumni, jawaban individu, dokumen dan catatan wawancara tidak disertakan.' : 'Bahas temuan dengan penyelenggara dan unit kerja sebelum menetapkan keputusan.' }}</p></section>

@if(!$pdf)<script>(()=>{const slides=[...document.querySelectorAll('.slide')];let current=0;const render=()=>{slides.forEach((s,i)=>s.classList.toggle('active',i===current));document.getElementById('slidePosition').textContent=`${current+1} / ${slides.length}`;};const move=step=>{current=Math.min(slides.length-1,Math.max(0,current+step));render();if(document.body.classList.contains('presentation-mode'))window.scrollTo(0,0);else slides[current].scrollIntoView({behavior:'smooth'});};document.getElementById('toggleSlides').onclick=()=>{document.body.classList.toggle('presentation-mode');render();window.scrollTo(0,0);};document.getElementById('previousSlide').onclick=()=>move(-1);document.getElementById('nextSlide').onclick=()=>move(1);document.addEventListener('keydown',e=>{if(e.key==='ArrowRight')move(1);if(e.key==='ArrowLeft')move(-1);});render();})();</script>
@endif
</body></html>
