<div class="pasca-import">
@php
    $report=$importPreview?->report;
    $counts=$report['counts']??null;
    $imported=$importPreview && $importPreview->status==='imported';
    $expired=$importPreview && !$imported && \Carbon\Carbon::parse($importPreview->created_at)->addDay()->isPast();
    $blocked=$report && (count($report['errors'])>0 || $counts['new']===0 || $expired);
@endphp
<div class="import-steps mb-4" aria-label="Tahapan import">
    <div class="import-step {{ $importPreview?'is-complete':'is-current' }}"><span>1</span><div><strong>Unggah file</strong><small>Excel hasil evaluasi</small></div></div>
    <div class="import-step {{ $importPreview?($imported?'is-complete':'is-current'):'' }}"><span>2</span><div><strong>Periksa pencocokan</strong><small>Peserta dan pertanyaan</small></div></div>
    <div class="import-step {{ $imported?'is-complete':'' }}"><span>3</span><div><strong>Simpan hasil</strong><small>Masuk ke database</small></div></div>
</div>
<details id="importUploadPanel" class="card import-upload mb-4" @if(!$importPreview) open @endif>
    <summary class="p-3 p-md-4"><span><strong class="d-block">{{ $importPreview?'Ganti atau periksa ulang file':'Import Data Evaluasi Historis' }}</strong><small class="text-muted">{{ $importPreview?'Unggah kembali setelah memperbaiki file, peserta, atau bank soal.':'Daftarkan dan setujui peserta di pelatihan ini sebelum mengunggah hasil evaluasi.' }}</small></span><i class="bx bx-chevron-down fs-4" aria-hidden="true"></i></summary>
    <div class="px-3 px-md-4 pb-4">
        <form method="POST" enctype="multipart/form-data" action="{{ route('post-evaluation.import.preview', [$training] + $origin) }}" class="row g-3 align-items-end">@csrf
            <div class="col-md-9"><label class="form-label" for="pascaWorkbook">File Excel hasil evaluasi</label><input id="pascaWorkbook" name="workbook" type="file" class="form-control" accept=".xlsx" required><small class="text-muted d-block mt-2">XLSX maksimal 20 MB. Sheet wajib: Jawaban dan Data Peserta. Nama peserta harus memuat NIP/NIK 16 atau 18 digit. Informasi Umum digunakan untuk memeriksa program.</small></div>
            <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="bx bx-search-alt me-1" aria-hidden="true"></i>Periksa File</button></div>
            @if($training->program_evaluasi === 'Teknis')
                <div class="col-12 small text-muted">Format Teknis mendukung blok Alumni PNS dan Non-ASN, Atasan Alumni, serta Rekan Alumni dalam satu file.</div>
            @endif
        </form>
    </div>
</details>
@if($importPreview)
<div class="row g-4 mb-4 align-items-start">
    <div class="col-xl-8">
        <div class="card import-overview mb-3"><div class="card-body p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3"><div class="import-file-heading"><span class="text-muted small">HASIL PEMERIKSAAN FILE</span><h5 class="fw-bold mt-1 mb-1">Pratinjau Import</h5><div class="import-file-name"><i class="bx bx-spreadsheet text-success me-1" aria-hidden="true"></i>{{ $importPreview->filename }}</div></div><span class="badge bg-label-{{ $imported?'success':($blocked?'warning':'success') }}">{{ $imported?'Sudah diimpor':($expired?'Pratinjau kedaluwarsa':(count($report['errors'])?'Perlu diperbaiki':($counts['new']===0?'Tidak ada jawaban baru':'Siap disimpan'))) }}</span></div>
            <div class="import-training-info"><small class="text-muted d-block mb-1">Pelatihan pada file</small><strong>{{ $report['title'] ?: 'Tidak tercantum' }}</strong></div>
            <div class="row g-3 mt-1">
            @foreach([
                ['label'=>'Peserta cocok','value'=>$counts['matched'].' / '.$counts['participants'],'hint'=>'Peserta yang sudah terdaftar','color'=>$counts['matched']===$counts['participants']?'success':'warning'],
                ['label'=>'Jawaban baru','value'=>number_format($counts['new'],0,',','.'),'hint'=>'Isian pertanyaan yang akan disimpan','color'=>'primary'],
                ['label'=>'Jawaban sama','value'=>number_format($counts['same'],0,',','.'),'hint'=>'Sudah ada, tidak digandakan','color'=>'secondary'],
                ['label'=>'Jawaban berbeda','value'=>number_format($counts['conflicts'],0,',','.'),'hint'=>'Dilewati, nilai lama dipertahankan','color'=>$counts['conflicts']?'warning':'secondary'],
            ] as $metric)
                <div class="col-6"><div class="import-metric"><small class="text-muted">{{ $metric['label'] }}</small><strong class="text-{{ $metric['color'] }}">{{ $metric['value'] }}</strong><small class="text-muted">{{ $metric['hint'] }}</small></div></div>
            @endforeach
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3">@forelse($counts['roles'] as $role=>$total)<span class="import-response-pill">{{ $training->evaluationRoleLabels()[$role]??$role }} <strong>{{ $total }}</strong> peserta</span>@empty<small class="text-muted">Belum ada respons evaluasi yang terbaca.</small>@endforelse</div>
            <p class="small text-muted mt-3 mb-0">Jumlah di atas merupakan hasil pemeriksaan sebelum penyimpanan. Satu peserta dapat memiliki banyak isian pertanyaan.</p>
        </div></div>
        @if($report['errors'])
        <div class="card import-issues mb-3"><div class="card-body p-3 p-md-4"><h6 class="fw-bold text-danger"><i class="bx bx-error-circle me-1" aria-hidden="true"></i>{{ count($report['errors']) }} masalah perlu diperbaiki</h6><p class="small text-muted mb-2">Daftarkan/setujui peserta yang belum cocok atau sesuaikan bank soal dengan program pelatihan. Periksa ulang file setelah diperbaiki.</p><ul class="small mb-0 ps-3">@foreach(array_slice($report['errors'],0,4) as $message)<li class="mb-1">{{ $message }}</li>@endforeach</ul>
        @if(count($report['errors'])>4)<details class="mt-2"><summary class="small text-primary">Lihat {{ count($report['errors'])-4 }} masalah lainnya</summary><ul class="small mt-2 import-issue-list">@foreach(array_slice($report['errors'],4) as $message)<li class="mb-1">{{ $message }}</li>@endforeach</ul></details>@endif
        </div></div>
        @endif
        @foreach($report['warnings'] as $message)<div class="alert alert-warning d-flex align-items-start gap-2 mb-3"><i class="bx bx-info-circle mt-1" aria-hidden="true"></i><span>{{ $message }}</span></div>@endforeach
    </div>
    <div class="col-xl-4">
        <aside class="card import-save-panel"><div class="card-body p-3 p-md-4">
            <h5 class="fw-bold mb-3">{{ $imported?'Import selesai':'Konfirmasi penyimpanan' }}</h5>
            <small class="text-muted d-block mb-1">Pelatihan tujuan</small><strong class="d-block import-target-name mb-2">{{ $training->nama_pelatihan }}</strong><div class="d-flex gap-2 flex-wrap mb-3"><span class="badge bg-label-primary">{{ $training->program_evaluasi }}</span><span class="badge bg-label-secondary">Angkatan {{ $training->angkatan }}</span></div>
            <p class="small text-muted">Peserta dicocokkan berdasarkan NIP/NIK. Jawaban terisi tetap dipertahankan; akun, Proper dan penilaian wawancara tidak berubah.</p>
            @if($imported)
                <div class="alert alert-success small mb-3">Jawaban sudah tersimpan. Lihat hasilnya pada tab Jawaban atau Ringkasan.</div><a class="btn btn-outline-primary w-100" href="{{ route('post-evaluation.show', [$training, 'tab' => 'jawaban'] + $origin) }}">Lihat Jawaban</a>
            @else
                @if($expired)<div class="alert alert-warning small">Pratinjau sudah melewati 24 jam. Unggah dan periksa ulang file sebelum menyimpan.</div>@elseif(count($report['errors']))<div class="alert alert-warning small">Tombol simpan aktif setelah semua masalah pencocokan diperbaiki.</div>@elseif($counts['new']===0)<div class="alert alert-info small">Tidak ada jawaban baru untuk disimpan.</div>@endif
                <form method="POST" action="{{ route('post-evaluation.import.store', [$training, $importPreview->id] + $origin) }}">@csrf
                    <div class="form-check mb-3"><input id="confirmImportTraining" type="checkbox" class="form-check-input" name="confirm_training" value="1" required @disabled($blocked)><label class="form-check-label small" for="confirmImportTraining">Saya sudah memeriksa hasil pencocokan dan memastikan data ini untuk pelatihan tujuan di atas.</label></div>
                    <button type="submit" class="btn btn-success w-100" @disabled($blocked)><i class="bx bx-check-circle me-1" aria-hidden="true"></i>Simpan Hasil Import</button>
                </form>
                <button type="button" class="btn btn-outline-secondary w-100 mt-2" data-import-reupload><i class="bx bx-upload me-1" aria-hidden="true"></i>Periksa Ulang File</button>
                <small class="text-muted d-block mt-3">Pratinjau berlaku 24 jam. Jika peserta, soal atau jawaban berubah, periksa file kembali.</small>
            @endif
        </div></aside>
    </div>
</div>
@include('post-evaluation.import-details')
@endif
<details class="card mb-4 import-history" @if(!$importPreview) open @endif><summary class="p-3 p-md-4"><span class="fw-bold">Riwayat Import <span class="badge bg-label-secondary ms-1">{{ $importHistory->count() }}</span></span><i class="bx bx-chevron-down fs-4" aria-hidden="true"></i></summary><div class="table-responsive"><table class="table mb-0"><thead><tr><th>File</th><th>Waktu penyimpanan</th><th>Status</th></tr></thead><tbody>@forelse($importHistory as $batch)<tr><td class="import-file-name">{{ $batch->filename }}</td><td class="text-nowrap">{{ \Carbon\Carbon::parse($batch->imported_at)->format('d/m/Y H:i') }}</td><td><span class="badge bg-label-success">Tersimpan</span></td></tr>@empty<tr><td colspan="3" class="text-muted py-4 text-center">Belum ada import yang disimpan untuk pelatihan ini.</td></tr>@endforelse</tbody></table></div></details>
</div>
@push('css')
<style>
.pasca-import .import-steps{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.pasca-import .import-step{display:flex;align-items:center;gap:12px;padding:14px 16px;border:1px solid #e1e5ed;border-radius:10px;background:#fff}.pasca-import .import-step>span{display:grid;place-items:center;flex-shrink:0;width:32px;height:32px;border-radius:50%;background:#eef0f5;color:#697a8d;font-weight:700}.pasca-import .import-step strong,.pasca-import .import-step small{display:block}.pasca-import .import-step small{font-size:.75rem;color:#8592a3;margin-top:3px}.pasca-import .import-step.is-current{border-color:#a5a6f6;background:#f5f5ff}.pasca-import .import-step.is-current>span{background:#696cff;color:#fff}.pasca-import .import-step.is-complete>span{background:#e3f5ec;color:#23865b}.pasca-import details>summary{display:flex;align-items:center;justify-content:space-between;gap:14px;cursor:pointer;list-style:none}.pasca-import details>summary::-webkit-details-marker{display:none}.pasca-import details[open]>summary>.bx-chevron-down{transform:rotate(180deg)}.pasca-import .import-file-heading{min-width:0;flex:1}.pasca-import .import-file-name,.pasca-import .import-target-name{overflow-wrap:anywhere;white-space:normal;line-height:1.6}.pasca-import .import-training-info{padding:12px 16px;background:#f7f8fc;border-radius:8px;overflow-wrap:anywhere}.pasca-import .import-metric{height:100%;padding:16px;border:1px solid #e8ebf2;border-radius:10px}.pasca-import .import-metric small{display:block;font-size:.75rem}.pasca-import .import-metric strong{display:block;font-size:1.65rem;line-height:1.3;margin:6px 0}.pasca-import .import-response-pill{font-size:.8rem;border-radius:7px;padding:6px 10px;background:#f0f3f8;color:#566579}.pasca-import .import-response-pill strong{margin-left:5px}.pasca-import .import-issues{border-left:3px solid #ff6262}.pasca-import .import-issue-list{max-height:240px;overflow:auto}.pasca-import .import-save-panel{border-top:3px solid #696cff}.pasca-import .import-table-scroll{max-height:480px;overflow:auto}.pasca-import .import-detail-table{min-width:720px;table-layout:fixed;margin:0}.pasca-import .import-detail-table th,.pasca-import .import-detail-table td{padding:14px 16px;vertical-align:top;white-space:normal;overflow-wrap:anywhere;line-height:1.6}.pasca-import .import-detail-table thead th{position:sticky;top:0;background:#f7f8fc;z-index:1;font-size:.75rem}.pasca-import .import-detail-table .badge{white-space:normal;text-align:left;line-height:1.4}.pasca-import .import-detail-toolbar{display:flex;align-items:end;flex-wrap:wrap;gap:12px;padding:16px;border-top:1px solid #eceef3;border-bottom:1px solid #eceef3}.pasca-import .import-detail-toolbar .import-search{flex:1;min-width:200px}.pasca-import .import-detail-toolbar .import-filter{width:180px}.pasca-import .import-pager{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:14px 16px;border-top:1px solid #eceef3}.pasca-import .import-pager .btn{min-width:36px;min-height:36px}.pasca-import .import-question-text{white-space:pre-line;line-height:1.7}.pasca-import .import-match-label{font-size:.7rem;color:#8592a3;display:block;margin-bottom:3px}.pasca-import .import-detail-table tr[hidden]{display:none!important}@media(min-width:1200px){.pasca-import .import-save-panel{position:sticky;top:20px}.pasca-import .col-xl-4{align-self:stretch}}@media(max-width:767px){.pasca-import .import-steps{gap:6px}.pasca-import .import-step{padding:10px 7px;flex-direction:column;align-items:flex-start;gap:6px;font-size:.75rem}.pasca-import .import-step small{display:none}.pasca-import .import-step>span{width:26px;height:26px}.pasca-import .import-detail-toolbar .import-filter{width:100%}.pasca-import .import-metric{padding:12px}.pasca-import .import-metric strong{font-size:1.4rem}}
</style>
@endpush
@push('js')
<script>
document.querySelectorAll('[data-import-reupload]').forEach(button=>button.addEventListener('click',()=>{document.getElementById('importUploadPanel').open=true;document.getElementById('pascaWorkbook').focus();}));
document.querySelectorAll('[data-import-table]').forEach(panel=>{
 const rows=[...panel.querySelectorAll('[data-import-row]')],search=panel.querySelector('[data-import-search]'),filter=panel.querySelector('[data-import-filter]'),info=panel.querySelector('[data-import-info]'),empty=panel.querySelector('[data-import-empty]'),prev=panel.querySelector('[data-import-prev]'),next=panel.querySelector('[data-import-next]');let page=1;const size=10;
 function render(){const term=search.value.trim().toLocaleLowerCase('id-ID'),status=filter.value,matches=rows.filter(row=>(!term||row.textContent.toLocaleLowerCase('id-ID').includes(term))&&(!status||row.dataset.status===status||row.dataset.role===status));const pages=Math.max(1,Math.ceil(matches.length/size));page=Math.min(page,pages);rows.forEach(row=>row.hidden=true);matches.slice((page-1)*size,page*size).forEach(row=>row.hidden=false);empty.hidden=matches.length>0;info.textContent=matches.length?((page-1)*size+1)+'?'+Math.min(page*size,matches.length)+' dari '+matches.length+' hasil ? Halaman '+page+'/'+pages:'Tidak ada hasil yang cocok';prev.disabled=page<=1;next.disabled=page>=pages;}
 search.addEventListener('input',()=>{page=1;render();});filter.addEventListener('change',()=>{page=1;render();});prev.addEventListener('click',()=>{page--;render();});next.addEventListener('click',()=>{page++;render();});render();
});
</script>
@endpush
