<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Paparan · {{$summary['training']}}</title><style>
@page{margin:0}body{margin:0;font-family:DejaVu Sans,sans-serif;color:#111}.slide{width:960pt;height:540pt;position:relative;page-break-after:always;overflow:hidden;background:#fff}.slide:last-child{page-break-after:auto}.heading{position:absolute;left:46pt;top:12pt;width:725pt;text-align:center;font-size:18pt;line-height:1.24;font-weight:bold;margin:0}.subtitle{font-size:16pt}.brand{position:absolute;top:10pt;right:16pt;width:136pt;height:auto}.chart-panel{position:absolute;left:50pt;top:92pt;width:500pt;height:394pt;padding:9pt}.chart-title{text-align:center;font-size:12pt;color:#555;line-height:1.35;height:44pt;margin:0 15pt}.chart{display:block;width:500pt;height:303.6pt;margin:0 auto}.coverage{text-align:center;font-size:7.3pt;color:#555;line-height:1.45;margin:7pt 2pt 0}.discussion{position:absolute;left:612pt;top:92pt;width:274pt;height:394pt;background:#dbe2f3;padding:10pt 12pt}.discussion h2,.conclusion h2{font-size:15pt;font-weight:normal;text-align:center;margin:0 0 16pt}.discussion ul,.conclusion ul{padding-left:16pt;margin:0}.discussion li{font-size:11.4pt;line-height:1.46;margin:0 0 12pt;padding-left:1pt}.footer{position:absolute;left:50pt;right:50pt;bottom:9pt;color:#687080;font-size:7pt;line-height:1.3}.page{float:right}.conclusion{position:absolute;top:63pt;height:435pt;padding:12pt 14pt}.conclusion.left{left:50pt;width:410pt;background:#fae5d5}.conclusion.right{left:502pt;width:416pt;background:#e1efd8}.conclusion p{font-size:11.6pt;line-height:1.48;margin:0 0 12pt;text-align:justify}.conclusion.right p,.conclusion li{font-size:10.5pt;line-height:1.42;margin-bottom:12pt}.activity{font-weight:bold}.draft{color:#a14b10}.cover-art{position:absolute;left:0;top:0;width:960pt;height:540pt}.cover-title{position:absolute;left:180pt;top:190pt;width:600pt;height:135pt;text-align:center;font-size:24pt;line-height:1.3;font-weight:bold}.cover-location{position:absolute;left:180pt;top:335pt;width:600pt;text-align:center;font-size:16pt}.cover-organizer{position:absolute;left:260pt;top:393pt;width:420pt;padding:7pt 10pt;text-align:center;background:#218d59;color:white;border-radius:18pt;font-weight:bold;font-size:12pt}.objective{position:absolute;left:128pt;width:746pt;height:59pt;padding:10pt 20pt 10pt 52pt;color:#fff;font-size:23pt;line-height:1.3}.objective-number{position:absolute;left:76pt;width:98pt;height:98pt;border-radius:50pt;background:white;text-align:center;line-height:96pt;font-size:34pt;border:1pt solid #eeae00}.agenda-heading{position:absolute;top:76pt;width:380pt;padding:13pt 12pt;border:1.5pt solid #78ae4f;border-radius:17pt;text-align:center;font-size:16pt;line-height:1.4}.agenda-heading.right{border-color:#5b9cdb}.agenda-body{position:absolute;top:166pt;width:374pt;padding:14pt 18pt;border-radius:18pt;font-size:14pt;line-height:1.5}.agenda-body ul{padding-left:18pt;margin:0}.agenda-body li{margin-bottom:14pt}.agenda-heading.left,.agenda-body.left{left:41pt}.agenda-heading.right,.agenda-body.right{left:513pt}.agenda-body.left{background:#c8e0b4}.agenda-body.right{background:#bcd6ee}.agenda-note{position:absolute;left:50pt;top:481pt;width:850pt;font-size:9pt;color:#65736a}
.ai-note{position:absolute;left:50pt;bottom:25pt;font-size:7pt;color:#687080}.discussion li{font-size:10.8pt;line-height:1.42;margin-bottom:10pt}.conclusion p{font-size:10.8pt;line-height:1.45}.conclusion li{font-size:10.3pt;line-height:1.42}</style></head><body>
@if(!$pdf)
@include('post-evaluation.presentation-controls')
@endif
@php
$logo='data:image/png;base64,'.base64_encode(file_get_contents(public_path('assets/post-evaluation/report-jabar.png')));
$cover='data:image/svg+xml;base64,'.base64_encode(file_get_contents(public_path('assets/post-evaluation/report-cover.svg')));
$objectives='data:image/svg+xml;base64,'.base64_encode(file_get_contents(public_path('assets/post-evaluation/report-objectives.svg')));
@endphp
<section class="slide">
    <img class="cover-art" src="{{$cover}}" alt="">
    <div class="cover-title" @if(mb_strlen($summary['meta']['name'])>120) style="font-size:18pt" @endif>{{$summary['meta']['name']}}</div>
    <div class="cover-location">{{$summary['meta']['location']}}, {{$summary['meta']['date']}}</div>
    <div class="cover-organizer">{{$summary['meta']['organizer']}}</div>
    @include('post-evaluation.report-footer',['page'=>1])
</section>
<section class="slide">
    <h1 class="heading" style="top:39pt;font-size:27pt">Tujuan Evaluasi Pasca</h1><img class="brand" src="{{$logo}}" alt="Pemerintah Provinsi Jawa Barat">
    <img class="cover-art" src="{{$objectives}}" alt="Tiga tujuan evaluasi pasca pelatihan">
    @include('post-evaluation.report-footer',['page'=>2])
</section>
<section class="slide">
    <h1 class="heading" style="width:850pt;font-size:24pt">Mekanisme Konfirmasi Jawaban dan Ekspose</h1>
    <div class="agenda-heading left">Konfirmasi Jawaban dan Evidence dari Alumni<br><strong><em>Tahap konfirmasi / wawancara</em></strong></div>
    <div class="agenda-heading right">Ekspose Hasil oleh Tim Evaluator<br><strong><em>Tahap paparan dan tindak lanjut</em></strong></div>
    <div class="agenda-body left"><ul><li>Peserta: {{$summary['total']}} alumni. @if($summary['team_count']) Tim wawancara: {{$summary['team_count']}} tim. @else Pembagian tim belum ditetapkan. @endif</li><li>Alumni memaparkan aksi perubahan, target jangka menengah/panjang, dan bukti capaian.</li><li>Evaluator mengonfirmasi target, tujuan, manfaat, serta komunikasi berdasarkan rubrik penilaian.</li><li>Hasil konfirmasi dan wawancara dihimpun sebagai dasar analisis kegiatan.</li></ul></div>
    <div class="agenda-body right"><ul><li>Audiens: alumni, evaluator, dan penyelenggara kegiatan.</li><li>Paparan menggabungkan hasil {{$summary['total']}} peserta dari seluruh tim.</li><li>Pembahasan perbandingan survei dan wawancara, kesimpulan, serta rekomendasi.</li><li>Penetapan tindak lanjut berdasarkan bukti dan kesepakatan penyelenggara.</li></ul></div>
    <div class="agenda-note">Rincian waktu dan agenda pelaksanaan mengikuti jadwal yang ditetapkan penyelenggara.</div>
    @include('post-evaluation.report-footer',['page'=>3])
</section>
@foreach($summary['slides'] as $slide)
<section class="slide">
    <h1 class="heading">{{$slide['title']}}@if($loop->first)<br><span class="subtitle">(Hasil wawancara)</span>@endif</h1>
    <img class="brand" src="{{$logo}}" alt="Pemerintah Provinsi Jawa Barat">
    <div class="chart-panel">
        <p class="chart-title">{{$slide['title']}}@if($loop->first) (Hasil wawancara)@endif</p>
        <img class="chart" src="{{$presenter->chart($slide,$summary)}}" alt="Grafik distribusi seluruh peserta kegiatan">
        <p class="coverage">@foreach($slide['series'] as $series){{$series['label']}}: {{$series['data']['valid']}}/{{$summary['total']}} terisi; {{$series['data']['missing']}} belum dinilai @if(!$loop->last) · @endif @endforeach<br>@if($loop->last)Hasil survei parsial/kosong: {{$summary['partial']}} alumni. Rerata tiga indikator terisi; bukan nilai berbobot lima kriteria.@else Rentang ekspose: 0, >0-25, >25-50, >50-75, >75-100 · angka pada batang adalah jumlah peserta @endif</p>
    </div>
    <aside class="discussion"><h2>Pembahasan</h2><ul>@foreach($slide['discussion'] as $text)<li>{{$text}}</li>@endforeach</ul></aside>
    @include('post-evaluation.report-footer',['page'=>$loop->iteration+3])
</section>
@endforeach
<section class="slide">
    <h1 class="heading">Kesimpulan dan Rekomendasi</h1><img class="brand" src="{{$logo}}" alt="Pemerintah Provinsi Jawa Barat">
    <div class="conclusion left"><h2>Pembahasan Keseluruhan</h2>@foreach($summary['conclusions'] as $text)<p>{{$text}}</p>@endforeach</div>
    <div class="conclusion right"><h2>Rekomendasi</h2><p>Tindak lanjut berdasarkan angka hasil evaluasi dan kebutuhan konfirmasi evidence.</p><ul>@foreach($summary['recommendations'] as $item)<li>{{$item}}</li>@endforeach</ul></div>
    @include('post-evaluation.report-footer',['page'=>9])
</section>
</body></html>
