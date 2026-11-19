<section class="card card-body mb-4"><div class="d-flex flex-wrap align-items-start justify-content-between gap-3"><div>
<h5>Narasi pembahasan dan rekomendasi</h5><p class="text-muted mb-2">Siapkan prompt dari data pelatihan, salin ke ChatGPT, lalu tempel dan tinjau hasilnya per slide. Membuka halaman ini tidak menjalankan API AI.</p>
@if($summary['manual_narrative'] ?? false)<span class="badge bg-label-success">Narasi hasil tinjauan tersimpan</span>
@if($summary['narrative_stale'] ?? false)<p class="text-warning small mt-2 mb-0">Data berubah sejak narasi disimpan. Perbarui prompt dan tinjau kembali narasinya.</p>@endif
@elseif(($summary['ai_status'] ?? 'none')==='current')<span class="badge bg-label-success">Menggunakan hasil Gemini yang tersimpan</span>
@else<small class="text-muted">Ekspose memakai ringkasan dasar dari angka terkini.</small>@endif
<small class="d-block text-muted mt-2">{{ $summary['evaluator_note_count'] ?? 0 }} catatan evaluator yang disamarkan tersedia sebagai bahan prompt.</small></div>
<div class="d-flex flex-wrap gap-2"><a class="btn btn-primary" href="{{ route('post-evaluation.narrative.edit',$training) }}">Prompt ChatGPT &amp; Edit Narasi</a>
@if(app(\App\Services\PostEvaluationAiService::class)->configured())
<form id="pasca-narrative-form" method="POST" action="{{ route('post-evaluation.ai.generate',$training) }}" onsubmit="const button=this.querySelector('button');button.disabled=true;button.textContent='Membuat narasi...';">@csrf<button class="btn btn-outline-primary" type="submit">Generate Gemini (opsional)</button></form>
@endif
</div></div></section>
