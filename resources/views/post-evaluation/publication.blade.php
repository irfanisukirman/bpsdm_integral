<div class="card card-body"><h5>Publikasi hasil dan ekspose</h5><p>Link publik menampilkan ringkasan agregat tanpa identitas, jawaban individu, atau catatan wawancara. Hasil publik adalah salinan pada waktu publikasi, bukan data yang berubah otomatis.</p>

@if($setting?->public_token)<label class="form-label">Link hasil publik</label><input class="form-control mb-2" readonly onclick="this.select()" value="{{ route('post-evaluation.public',$setting->public_token) }}"><label class="form-label">Link ekspose publik</label><input class="form-control mb-2" readonly onclick="this.select()" value="{{ route('post-evaluation.public.expose',$setting->public_token) }}"><small class="mb-3">Diterbitkan {{ $setting->published_at?->translatedFormat('d M Y H:i') }}</small>
@else<p class="text-muted">Belum dipublikasikan.</p>
@endif
<form action="{{ route('post-evaluation.publish',$training) }}" method="POST" class="d-flex flex-wrap gap-2" onsubmit="return confirm('Terapkan perubahan publikasi hasil agregat?')">
@csrf<button class="btn btn-primary" name="action" value="publish">{{ $setting?->public_token ? 'Perbarui Hasil Publik' : 'Publikasikan Hasil' }}</button>
@if($setting?->public_token)<button class="btn btn-outline-secondary" name="action" value="rotate">Ganti Link & Perbarui</button><button class="btn btn-outline-danger" name="action" value="revoke">Nonaktifkan Link</button>
@endif</form></div>