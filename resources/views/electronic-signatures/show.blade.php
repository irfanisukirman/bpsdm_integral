@extends('layouts.master')
@section('title',$signatureRequest->title)
@section('content')
@php
 $myActions=$signatureRequest->documents->flatMap->actions->filter(fn($action)=>$action->actor?->user_id===Auth::id());
 $pendingActions=$myActions->where('status','pending')->values();
 $batchActionPayload=$pendingActions->map(fn($action)=>['id'=>$action->id,'name'=>$action->document->original_name,'url'=>route('electronic-signatures.sign',$action)])->values();
 $completedDocuments=$signatureRequest->documents->where('status','completed')->count();
 $totalDocuments=$signatureRequest->documents->count();
 $percent=$totalDocuments?round($completedDocuments/$totalDocuments*100):0;
@endphp
<div class="container-xxl container-p-y">
 <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4"><div><a href="{{route('electronic-signatures.index')}}" class="small"><i class="bx bx-arrow-back"></i> Daftar TTE</a><h4 class="fw-bold mt-2 mb-1">{{$signatureRequest->title}}</h4><p class="text-muted mb-0">{{$signatureRequest->description?:'Bundel dokumen tanda tangan elektronik'}} &middot; {{$signatureRequest->bidang}}</p></div><div class="d-flex flex-wrap align-items-start gap-2">@if($completedDocuments>0)<a href="{{route('electronic-signatures.download-zip',$signatureRequest)}}" class="btn btn-outline-success"><i class="bx bx-archive-in me-1"></i>Unduh ZIP ({{$completedDocuments}})</a>@endif<span class="badge bg-label-{{$signatureRequest->status==='completed'?'success':'warning'}} fs-6">{{$signatureRequest->status==='completed'?'Selesai':'Dalam proses'}}</span>@if($pendingActions->isNotEmpty())<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#batchSign"><i class="bx bx-pen me-1"></i>Tandatangani Semua ({{$pendingActions->count()}})</button>@endif</div></div>
 @if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
 @if($errors->any())<div class="alert alert-danger">{{$errors->first()}}</div>@endif
 <div class="row g-3 mb-4"><div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Total dokumen</small><h2 class="mb-0">{{$totalDocuments}}</h2></div></div></div><div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Sudah selesai</small><h2 class="text-success mb-0">{{$completedDocuments}}</h2></div></div></div><div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Menunggu giliran saya</small><h2 class="text-warning mb-0">{{$pendingActions->count()}}</h2></div></div></div></div>
 <div class="card border-0 shadow-sm mb-4"><div class="card-body"><div class="d-flex justify-content-between mb-2"><strong>Progres Bundel</strong><strong>{{$completedDocuments}}/{{$totalDocuments}} &middot; {{$percent}}%</strong></div><div class="progress" style="height:12px"><div class="progress-bar bg-success" style="width:{{$percent}}%"></div></div></div></div>
 <div class="card border-0 shadow-sm mb-4"><div class="card-header"><h6 class="fw-bold mb-1">Alur Persetujuan</h6><small class="text-muted">Tombol tanda tangan muncul otomatis ketika seluruh tahapan sebelumnya selesai.</small></div><div class="card-body"><div class="row g-3">
 @foreach($signatureRequest->actors as $actor)
  @php($done=$actor->actions->isNotEmpty()&&$actor->actions->every(fn($action)=>$action->status==='completed'))
  <div class="col-md-6 col-xl-4"><div class="border rounded p-3 h-100 d-flex gap-3 align-items-center">
   <div class="position-relative flex-shrink-0" style="width:64px;height:64px">
    @if($actor->user?->profile_photo)<img src="{{asset('storage/'.$actor->user->profile_photo)}}" alt="Foto {{$actor->user->name}}" class="rounded-circle w-100 h-100 shadow-sm" style="object-fit:cover">
    @elseif($actor->user?->avatar)<img src="{{$actor->user->avatar}}" alt="Foto {{$actor->user->name}}" class="rounded-circle w-100 h-100 shadow-sm" style="object-fit:cover">
    @else<div class="rounded-circle w-100 h-100 d-flex align-items-center justify-content-center bg-label-{{$done?'success':'secondary'}}"><strong class="fs-4">{{mb_strtoupper(mb_substr($actor->user?->name?:'?',0,1))}}</strong></div>@endif
    <span class="badge rounded-pill bg-{{$done?'success':'secondary'}} position-absolute bottom-0 end-0">{{$actor->sequence}}</span>
   </div>
   <div class="min-w-0"><strong class="d-block text-truncate">{{$actor->user?->name}}</strong><small class="d-block text-muted">{{$actor->role==='signer'?'Penandatangan akhir':'Pemaraf'}}</small><small class="d-block text-muted">{{$actor->user?->jabatan?:'-'}}</small><span class="badge mt-2 bg-label-{{$done?'success':'secondary'}}">{{$done?'Selesai':'Menunggu'}}</span></div>
  </div></div>
 @endforeach
</div></div></div>
 <div class="card border-0 shadow-sm"><div class="card-header d-flex justify-content-between"><div><h6 class="fw-bold mb-1">Daftar Dokumen</h6><small class="text-muted">Status setiap berkas dalam bundel.</small></div></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Dokumen/Peserta</th><th>Ukuran</th><th>Posisi Proses</th><th>Status</th><th></th></tr></thead><tbody>@foreach($signatureRequest->documents as $document)@php($current=$document->actions->firstWhere('status','pending'))<tr><td><strong>{{$document->participantCertificate?->participant?->name?:($document->internshipParticipant?->name?:$document->original_name)}}</strong>@if($document->participantCertificate)<small class="d-block text-muted">{{$document->participantCertificate->participant?->nip_nik}} &middot; {{$document->participantCertificate->certificate_number}}</small>@elseif($document->internshipParticipant)<small class="d-block text-muted">{{$document->internshipParticipant->student_number}} &middot; {{$document->internshipParticipant->certificate_number}} &middot; Sertifikat Magang/PKL</small>@endif</td><td>{{number_format($document->file_size/1024,1)}} KB</td><td><small>{{$current?'Menunggu '.$current->actor?->user?->name:'Seluruh tahapan selesai'}}</small></td><td><span class="badge bg-label-{{$document->status==='completed'?'success':'warning'}}">{{$document->status==='completed'?'Ditandatangani':'Diproses'}}</span></td><td class="text-end"><a href="{{route('electronic-signatures.download',$document)}}" class="btn btn-sm btn-outline-primary"><i class="bx bx-download"></i></a></td></tr>@endforeach</tbody></table></div></div>
</div>
@if($pendingActions->isNotEmpty())
<div class="modal fade" id="batchSign" tabindex="-1" data-bs-backdrop="static"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-header bg-primary text-white"><div><h5 class="modal-title text-white">Tanda Tangan Bundel BSrE</h5><small>{{$signatureRequest->title}}</small></div><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body p-4"><div class="row g-4"><div class="col-md-4 text-center"><div class="mx-auto mb-3" style="width:110px;height:110px">@if(Auth::user()->profile_photo)<img src="{{ asset('storage/'.Auth::user()->profile_photo) }}" alt="Foto {{ Auth::user()->name }}" class="rounded-circle shadow-sm w-100 h-100" style="object-fit:cover">@elseif(Auth::user()->avatar)<img src="{{ Auth::user()->avatar }}" alt="Foto {{ Auth::user()->name }}" class="rounded-circle shadow-sm w-100 h-100" style="object-fit:cover">@else<div class="rounded-circle bg-label-primary d-flex align-items-center justify-content-center w-100 h-100"><span class="fw-bold fs-1">{{ mb_strtoupper(mb_substr(Auth::user()->name,0,1)) }}</span></div>@endif</div><h5 class="mb-1">{{Auth::user()->name}}</h5><small class="text-muted">NIP/NIK {{Auth::user()->nip_nik}}</small><div class="mt-3"><span class="badge bg-label-info">{{$pendingActions->first()->actor->role==='signer'?'Penandatangan akhir':'Pemaraf'}}</span></div></div><div class="col-md-8"><label class="form-label fw-semibold">Passphrase BSrE</label><input type="password" id="batchPassphrase" class="form-control form-control-lg" autocomplete="new-password" placeholder="Masukkan passphrase" required><div class="alert alert-warning small mt-3"><i class="bx bx-lock me-1"></i>Passphrase digunakan satu kali untuk seluruh bundel, dikirim langsung ke BSrE, dan tidak disimpan oleh INTEGRAL.</div><div class="d-flex justify-content-between mt-3"><span>Progres proses</span><strong id="batchCounter">0/{{$pendingActions->count()}}</strong></div><div class="progress mt-2" style="height:12px"><div id="batchProgress" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%"></div></div><div id="batchLog" class="border rounded bg-light p-3 mt-3 small" style="height:180px;overflow:auto"><span class="text-muted">Log proses akan tampil di sini.</span></div></div></div></div><div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal" id="batchClose">Batal</button><button class="btn btn-primary" id="batchStart"><i class="bx bx-send me-1"></i>Proses Semua Dokumen</button></div></div></div></div>
@endif
@endsection
@push('js')
@if($pendingActions->isNotEmpty())
<script>
let batchActions = @json($batchActionPayload);

const batchStart = document.getElementById('batchStart');
const batchClose = document.getElementById('batchClose');
const batchPassphrase = document.getElementById('batchPassphrase');
const batchLog = document.getElementById('batchLog');
const batchProgress = document.getElementById('batchProgress');
const batchCounter = document.getElementById('batchCounter');

batchStart.addEventListener('click', async function () {
    if (batchActions.length === 0) {
        window.location.reload();
        return;
    }

    if (!batchPassphrase.value) {
        batchPassphrase.focus();
        return;
    }

    const passphrase = batchPassphrase.value;
    const actionsBeingProcessed = [...batchActions];
    const failedActions = [];
    let success = 0;
    let failed = 0;

    batchStart.disabled = true;
    batchClose.disabled = true;
    batchPassphrase.disabled = true;
    batchLog.innerHTML = '';
    batchProgress.classList.add('progress-bar-animated');

    for (let i = 0; i < actionsBeingProcessed.length; i++) {
        const item = actionsBeingProcessed[i];
        const logId = `batch-log-${item.id}`;
        batchLog.insertAdjacentHTML('beforeend', `<div id="${logId}" class="mb-1"><i class="bx bx-loader bx-spin"></i> ${i + 1}. ${item.name}</div>`);

        try {
            const response = await fetch(item.url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ passphrase }),
            });
            const data = await response.json();
            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'Tanda tangan gagal diproses.');
            }

            success++;
            document.getElementById(logId).innerHTML = `<span class="text-success"><i class="bx bx-check-circle"></i> ${i + 1}. ${item.name} berhasil</span>`;
        } catch (error) {
            failed++;
            failedActions.push(item);
            document.getElementById(logId).innerHTML = `<span class="text-danger"><i class="bx bx-x-circle"></i> ${i + 1}. ${item.name}: ${error.message}</span>`;
        }

        const done = i + 1;
        batchCounter.textContent = `${done}/${actionsBeingProcessed.length} &middot; ${success} berhasil &middot; ${failed} gagal`;
        batchProgress.style.width = `${Math.round(done / actionsBeingProcessed.length * 100)}%`;
        batchLog.scrollTop = batchLog.scrollHeight;
    }

    batchActions = failedActions;
    batchPassphrase.value = '';
    batchPassphrase.disabled = false;
    batchClose.disabled = false;
    batchClose.textContent = 'Tutup';
    batchProgress.classList.remove('progress-bar-animated');

    if (batchActions.length > 0) {
        batchStart.disabled = false;
        batchStart.innerHTML = `<i class="bx bx-refresh me-1"></i>Coba Lagi (${batchActions.length})`;
        batchPassphrase.placeholder = 'Masukkan kembali passphrase BSrE';
        batchPassphrase.focus();
        batchLog.insertAdjacentHTML('beforeend', '<div class="alert alert-danger py-2 px-3 mt-3 mb-0"><i class="bx bx-info-circle me-1"></i>Dokumen yang gagal tetap tersedia. Periksa passphrase lalu klik <strong>Coba Lagi</strong>.</div>');
        return;
    }

    batchStart.disabled = false;
    batchStart.innerHTML = '<i class="bx bx-refresh me-1"></i>Muat Ulang Halaman';
}
);
</script>
@endif
@endpush


