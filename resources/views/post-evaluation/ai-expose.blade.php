<section class="card card-body mb-4"><div class="d-flex flex-wrap align-items-start justify-content-between gap-3"><div><h5>Pembahasan dan rekomendasi ekspose</h5><p class="text-muted mb-2">AI menyusun narasi dari angka agregat. Identitas peserta, catatan wawancara, dan dokumen tidak dikirim. Grafik tetap memakai perhitungan aplikasi.</p>
@if(($summary['ai_status'] ?? 'none')==='current')
<span class="badge bg-label-success">Narasi AI sesuai data terkini</span><small class="d-block text-muted mt-2">Dibuat {{ \Carbon\Carbon::parse($summary['ai_generated_at'])->translatedFormat('d M Y H:i') }}. Tinjau draf sebelum memperbarui publikasi.</small>
@elseif(($summary['ai_status'] ?? 'none')==='stale')
<span class="badge bg-label-warning">Angka hasil berubah. Buat ulang narasi AI.</span><small class="d-block text-muted mt-2">Ekspose sementara menggunakan ringkasan berbasis angka terbaru.</small>
@else
<small class="text-muted">Narasi AI belum dibuat. Ekspose tetap dapat dibuka dengan pembahasan dasar.</small>
@endif
</div><form method="POST" action="{{ route('post-evaluation.ai.generate',$training) }}" onsubmit="const button=this.querySelector('button');button.disabled=true;button.textContent='Sedang membuat narasi...';">
@csrf
<button class="btn btn-primary" type="submit">Generate Pembahasan &amp; Rekomendasi AI</button></form></div></section>
