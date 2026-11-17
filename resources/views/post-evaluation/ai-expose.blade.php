<section class="card card-body mb-4"><div class="d-flex flex-wrap align-items-start justify-content-between gap-3"><div><h5>Pembahasan dan rekomendasi ekspose</h5><p class="text-muted mb-2">Gemini otomatis menyusun narasi dari angka agregat dan catatan evaluator yang disamarkan. Nama, NIP, email peserta dan berkas dokumen tidak disertakan. Grafik tetap memakai perhitungan aplikasi.</p>
@if(($summary['ai_status'] ?? 'none')==='current')
<span class="badge bg-label-success">Narasi AI sesuai data terkini</span><small class="d-block text-muted mt-2">Dibuat {{ \Carbon\Carbon::parse($summary['ai_generated_at'])->translatedFormat('d M Y H:i') }}. Tinjau draf sebelum memperbarui publikasi.</small>
@elseif(($summary['ai_status'] ?? 'none')==='stale')
<span class="badge bg-label-warning">Angka atau catatan evaluator berubah. Narasi akan diperbarui otomatis.</span><small class="d-block text-muted mt-2">Ekspose sementara menggunakan ringkasan berbasis angka terbaru.</small>
@else
<small class="text-muted">Narasi AI akan dibuat otomatis jika layanan tersedia dan sudah ada nilai evaluasi.</small>
@endif
<small class="d-block text-muted mt-2">{{ $summary['evaluator_note_count'] ?? 0 }} catatan evaluator dari lima kriteria wawancara menjadi bahan pembahasan.</small><div id="pasca-narrative-status" class="small mt-2" role="status" aria-live="polite"></div></div><form id="pasca-narrative-form" method="POST" action="{{ route('post-evaluation.ai.generate',$training) }}" onsubmit="const button=this.querySelector('button');button.disabled=true;button.textContent='Sedang membuat narasi...';">
@csrf
<button class="btn btn-primary" type="submit">Buat ulang narasi AI</button></form></div></section>

@if(($summary['ai_status'] ?? 'none') !== 'current' && ($summary['survey']['valid'] > 0 || $summary['interview']['valid'] > 0) && app(\App\Services\PostEvaluationAiService::class)->configured())
@push('js')
<script>
(() => {
    const form = document.getElementById('pasca-narrative-form');
    const status = document.getElementById('pasca-narrative-status');
    if (!form || !status) return;
    const button = form.querySelector('button');
    button.disabled = true;
    status.textContent = 'Gemini sedang menyusun pembahasan dan rekomendasi dari data terbaru...';
    const data = new FormData(form);
    data.set('automatic', '1');
    fetch(form.action, {method: 'POST', body: data, credentials: 'same-origin', headers: {'Accept': 'application/json'}})
        .then(async response => {
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Narasi belum berhasil dibuat. Silakan coba kembali.');
            window.location.reload();
        })
        .catch(error => { status.textContent = error.message; button.disabled = false; });
})();
</script>
@endpush
@endif
