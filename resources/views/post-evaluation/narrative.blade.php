@extends('layouts.master')
@section('title','Narasi paparan')
@section('content')
<style>
.narrative-card{background:var(--bs-body-bg,#fff);border:1px solid var(--bs-border-color,#dce8df);border-radius:12px;padding:22px;margin:18px 0}.narrative-card textarea{display:block;width:100%;box-sizing:border-box;margin:10px 0;min-height:160px;line-height:1.6}.narrative-card label{display:block;font-weight:600}.narrative-actions{display:flex;flex-wrap:wrap;gap:12px;align-items:center}.narrative-help{color:var(--bs-secondary-color,#616161);font-size:14px}.narrative-warning{padding:16px;background:var(--bs-warning-bg-subtle,#fff1d6);border-radius:8px}#ai-prompt{min-height:220px;font-size:13px}
</style>
<a class="btn btn-outline-secondary btn-sm mb-3" href="{{route('post-evaluation.show', [$training] + request()->only('from'))}}">&larr; Kembali ke Hasil Evaluasi</a>
<h4 class="fw-bold">Narasi paparan</h4>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<p>{{$training->nama_pelatihan}} · Slide 4–9</p>
<p>Salin prompt ke ChatGPT, tinjau hasilnya, lalu tempel ke bagian yang sesuai. Angka dan kategori mengikuti data aplikasi. Tidak memerlukan API.</p>
@if($stale)<p class="narrative-warning" role="alert">Data laporan berubah sejak narasi disimpan. Salin prompt terbaru dan periksa kembali angka serta pembahasannya sebelum menyimpan.</p>@endif
<form method="post" action="{{route('post-evaluation.narrative.update', [$training] + request()->only('from'))}}">
@csrf @method('put')
<input type="hidden" name="source_hash" value="{{$sourceHash}}">
<section class="narrative-card card">
    <h2>1. Siapkan bahan dan salin prompt</h2>
    <label for="collected-evaluator-notes">Catatan evaluator dari seluruh wawancara</label>
    <p class="narrative-help">{{$noteCount}} catatan terisi dari lima kriteria seluruh peserta kegiatan ini, termasuk wawancara draf. Semuanya otomatis masuk ke prompt sebagai bahan pembahasan dan rekomendasi slide 4–9. Nomor peserta dipakai untuk menghubungkan catatan tanpa menambahkan nama atau NIP.</p>
    <textarea class="form-control" id="collected-evaluator-notes" readonly placeholder="Belum ada catatan evaluator pada wawancara.">{{$evaluatorNotes}}</textarea>
    <p class="narrative-help">Catatan mengikuti isian wawancara terbaru saat halaman dibuka. Untuk memperbaruinya, ubah catatan pada form wawancara lalu muat ulang halaman ini. Hasil rangkuman AI akan tampil di slide setelah ditempel pada kolom pembahasan dan disimpan.</p>
    <label for="evaluator-notes">Catatan tambahan evaluator (opsional)</label>
    <p class="narrative-help">Tambahkan konteks atau fakta lapangan lain untuk melengkapi catatan wawancara di atas.</p>
    <textarea class="form-control" id="evaluator-notes" name="notes" maxlength="10000">{{old('notes',$saved['notes'] ?? '')}}</textarea>
    <label for="ai-prompt">Prompt dan data laporan</label>
    <textarea class="form-control" id="ai-prompt" readonly spellcheck="false">{{$prompt}}</textarea>
    <button type="button" class="btn btn-primary" id="copy-ai-prompt">Salin Prompt + Data</button>
    <a class="btn btn-outline-primary mt-2" href="https://chatgpt.com/" target="_blank" rel="noopener noreferrer">Buka ChatGPT</a>
    <span id="copy-status" role="status" aria-live="polite"></span>
</section>
<section class="narrative-card card">
    <h2>2. Tempel dan tinjau hasil AI</h2>
    <p class="narrative-help">Kolom kosong memakai ringkasan dasar. Isi paragraf pendek, maksimal sekitar 120 kata per kolom agar muat di slide. Pisahkan paragraf atau butir rekomendasi dengan baris baru.</p>
    @foreach($summary['slides'] as $index => $slide)
    @php($page=$index+4)
    <label for="discussion-{{$page}}">Slide {{$page}} — {{$slide['title']}}</label>
    <details><summary>Lihat pembahasan otomatis</summary>@foreach($slide['discussion'] as $text)<p>{{$text}}</p>@endforeach</details>
    <textarea class="form-control" id="discussion-{{$page}}" name="discussion[{{$page}}]" maxlength="1600" data-word-count>{{old('discussion.'.$page,$saved['discussion'][$page] ?? '')}}</textarea>
    <p class="narrative-help" data-count-for="discussion-{{$page}}"></p>
    @endforeach
    <label for="conclusions">Slide 9 — Kesimpulan evaluator</label>
    <textarea class="form-control" id="conclusions" name="conclusions" maxlength="1600" data-word-count>{{old('conclusions',$saved['conclusions'] ?? '')}}</textarea>
    <p class="narrative-help" data-count-for="conclusions"></p>
    <label for="recommendations">Slide 9 — Rekomendasi kepada penyelenggara</label>
    <textarea class="form-control" id="recommendations" name="recommendations" maxlength="1600" data-word-count>{{old('recommendations',$saved['recommendations'] ?? '')}}</textarea>
    <p class="narrative-help" data-count-for="recommendations"></p>
</section>
<div class="narrative-actions">
    <button type="submit" class="btn btn-primary" name="action" value="save">Simpan dan Pratinjau</button>
    <a class="btn btn-outline-primary" href="{{route('post-evaluation.expose', [$training] + request()->only('from'))}}">Kembali ke laporan</a>
    <button type="submit" name="action" value="reset" class="btn btn-outline-secondary" onclick="return confirm('Hapus narasi manual dan catatan evaluator kegiatan ini, lalu gunakan ringkasan dasar?')">Kembali ke Ringkasan Dasar</button>
</div>
</form>
@push('js')
<script>
const promptBase = @json($prompt);
const notes = document.getElementById('evaluator-notes');
const promptField = document.getElementById('ai-prompt');
function updatePrompt() {
    promptField.value = promptBase + (notes.value.trim() || 'Belum ada catatan tambahan.');
}
notes.addEventListener('input', updatePrompt);
updatePrompt();
document.getElementById('copy-ai-prompt').addEventListener('click', async function () {
    const status = document.getElementById('copy-status');
    try {
        await navigator.clipboard.writeText(promptField.value);
        status.textContent = 'Prompt tersalin. Tempel ke AI pilihan Anda.';
    } catch (error) {
        promptField.focus(); promptField.select();
        status.textContent = 'Teks sudah dipilih. Tekan Ctrl+C untuk menyalin.';
    }
});
document.querySelectorAll('[data-word-count]').forEach(function (field) {
    const counter = document.querySelector('[data-count-for="' + field.id + '"]');
    function count() {
        const words = field.value.trim() ? field.value.trim().split(/\s+/).length : 0;
        counter.textContent = words + ' kata' + (words > 120 ? ' — sebaiknya ringkas menjadi maksimal 120 kata agar muat di slide.' : '');
    }
    field.addEventListener('input', count); count();
});
</script>
@endpush
@endsection
