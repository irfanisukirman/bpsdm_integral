<section class="card card-body mb-4"><div class="d-flex flex-wrap align-items-start justify-content-between gap-3"><div>
<h5>Narasi pembahasan dan rekomendasi</h5><p class="text-muted mb-2">Siapkan prompt dari data pelatihan, salin ke ChatGPT, lalu tempel dan tinjau hasilnya per slide. Membuka halaman ini tidak menjalankan API AI.</p>
@if($manualNarrative)<span class="badge bg-label-success">Narasi hasil tinjauan tersimpan</span>
@elseif($savedGemini)<span class="badge bg-label-success">Hasil Gemini tersimpan</span>
@else<small class="text-muted">Ekspose memakai ringkasan dasar dari angka terkini.</small>@endif
</div>
<div class="d-flex flex-wrap gap-2"><a class="btn btn-primary" href="{{ route('post-evaluation.narrative.edit', [$training] + $origin) }}">Prompt ChatGPT &amp; Edit Narasi</a>
@if(app(\App\Services\PostEvaluationAiService::class)->configured())
<form id="pasca-narrative-form" method="POST" action="{{ route('post-evaluation.ai.generate',$training) }}" onsubmit="const button=this.querySelector('button');button.disabled=true;button.textContent='Membuat narasi...';">@csrf<button class="btn btn-outline-primary" type="submit">Generate Gemini (opsional)</button></form>
@endif
</div></div></section>
