@extends('layouts.master')
@section('title','Kelola Sertifikat Pelatihan')
@section('content')
@php
    $generatedCount = $certificates->filter(fn($certificate) => filled($certificate->generated_file_path))->count();
    $readyCount = $certificates->filter(fn($certificate) => filled($certificate->final_file_path) && blank($certificate->sent_at))->count();
    $sentCount = $certificates->filter(fn($certificate) => filled($certificate->sent_at))->count();
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <a href="{{route('trainings.manage',$training->id)}}" class="small"><i class="bx bx-left-arrow-alt"></i> Kembali ke Kelola Pelatihan</a>
            <h4 class="fw-bold mt-2 mb-1">Kelola Sertifikat</h4>
            <p class="text-muted mb-0">{{$training->nama_pelatihan}}</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-self-lg-center">
            <a href="{{route('electronic-signatures.integral')}}" class="btn btn-outline-primary"><i class="bx bx-pen me-1"></i>Buka TTE Integral</a>
            <a href="{{route('training-certificates.template',$training)}}" class="btn btn-outline-secondary"><i class="bx bx-download me-1"></i>Template + Kode</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success border-0"><i class="bx bx-check-circle me-1"></i>{{session('success')}}</div>@endif
    @if($errors->any())<div class="alert alert-danger border-0"><strong>Data belum dapat diproses.</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center text-center">
                <div class="col-md"><span class="avatar-initial rounded bg-label-primary p-2 d-inline-flex mb-2"><i class="bx bx-file-blank fs-4"></i></span><strong class="d-block">1. Generate</strong><small class="text-muted">{{$generatedCount}} sertifikat dibuat</small></div>
                <div class="col-auto d-none d-md-block text-muted"><i class="bx bx-right-arrow-alt fs-3"></i></div>
                <div class="col-md"><span class="avatar-initial rounded bg-label-warning p-2 d-inline-flex mb-2"><i class="bx bx-pen fs-4"></i></span><strong class="d-block">2. Tanda Tangan</strong><small class="text-muted">Diproses di TTE Integral</small></div>
                <div class="col-auto d-none d-md-block text-muted"><i class="bx bx-right-arrow-alt fs-3"></i></div>
                <div class="col-md"><span class="avatar-initial rounded bg-label-info p-2 d-inline-flex mb-2"><i class="bx bx-search-alt fs-4"></i></span><strong class="d-block">3. Pemeriksaan</strong><small class="text-muted">{{$readyCount}} siap dikirim</small></div>
                <div class="col-auto d-none d-md-block text-muted"><i class="bx bx-right-arrow-alt fs-3"></i></div>
                <div class="col-md"><span class="avatar-initial rounded bg-label-success p-2 d-inline-flex mb-2"><i class="bx bx-send fs-4"></i></span><strong class="d-block">4. Distribusi</strong><small class="text-muted">{{$sentCount}} terkirim</small></div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7"><div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom"><h5 class="mb-1 fw-bold">1. Pengaturan Sertifikat</h5><small class="text-muted">Nomor yang sudah diberikan tidak berubah ketika generate ulang.</small></div>
            <div class="card-body"><form method="POST" enctype="multipart/form-data" action="{{route('training-certificates.setting',$training)}}" class="row g-3">@csrf
                <div class="col-md-6"><label class="form-label">Nama Sertifikat</label><input name="name" class="form-control" required value="{{old('name',$setting?->name?:'Sertifikat Pelatihan')}}"></div>
                <div class="col-md-4"><label class="form-label">Tanggal Penerbitan</label><input type="date" name="issued_at" class="form-control" required value="{{old('issued_at',$setting?->issued_at?->format('Y-m-d')?:now()->format('Y-m-d'))}}"></div>
                <div class="col-md-2"><label class="form-label">Foto</label><select name="photo_size" class="form-select" required><option value="3x4" @selected(old('photo_size',$setting?->photo_size?:'3x4')==='3x4')>3×4</option><option value="2x3" @selected(old('photo_size',$setting?->photo_size)==='2x3')>2×3</option></select></div>
                <div class="col-md-9"><label class="form-label">Format Nomor</label><input name="number_format" class="form-control font-monospace" required value="{{old('number_format',$setting?->number_format?:'222.{X}/KPG.03.01.03/BPSDM/{TAHUN}')}}"><div class="form-text">Wajib memuat <code>{X}</code>; gunakan <code>{X:3}</code> untuk 001, 002, 003.</div></div>
                <div class="col-md-3"><label class="form-label">Urutan Mulai</label><input type="number" min="0" name="start_sequence" class="form-control" required value="{{old('start_sequence',$setting?->start_sequence?:1)}}"></div>
                <div class="col-12"><label class="form-label">Template Word (.docx)</label><input type="file" name="template" accept=".docx" class="form-control" {{$setting?->template_path?'':'required'}}><div class="form-text">{{$setting?->template_path?'Template tersedia; unggah hanya jika ingin mengganti.':'Template wajib diunggah sebelum generate.'}}</div></div>
                <div class="col-12"><button class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan Pengaturan</button></div>
            </form></div>
        </div></div>
        <div class="col-xl-5"><div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom"><h5 class="fw-bold mb-1">Kode Template</h5><small class="text-muted">Tempel kode pada dokumen Word.</small></div>
            <div class="card-body"><div class="d-flex flex-wrap gap-2 mb-3">@foreach(['${nama}','${nip_nik}','${jabatan}','${instansi}','${foto}','${nomor_sertifikat}','${nama_pelatihan}','${tanggal_mulai}','${tanggal_selesai}','${tanggal_sertifikat}'] as $code)<code class="border rounded px-2 py-1 bg-light">{{$code}}</code>@endforeach</div><div class="alert alert-warning py-2 small"><i class="bx bx-image me-1"></i>Foto mengikuti ukuran 3×4 atau 2×3 yang dipilih.</div><h6 class="fw-bold">Pratinjau nomor</h6><ol class="mb-0 ps-3">@foreach($preview as $number)<li class="font-monospace mb-1">{{$number}}</li>@endforeach</ol></div>
        </div></div>
    </div>

    <form id="generateForm" method="POST" action="{{route('training-certificates.generate',$training)}}">@csrf</form>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header border-bottom d-flex flex-column flex-xl-row justify-content-between gap-3">
            <div><h5 class="fw-bold mb-1">2. Generate & Ajukan TTE</h5><small class="text-muted">Pilih peserta dan penandatangan. PDF langsung menjadi satu bundel di TTE Integral.</small></div>
            @if($setting?->template_path && $signers->isNotEmpty())<button form="generateForm" class="btn btn-primary align-self-xl-center" onclick="return confirm('Generate sertifikat dan langsung kirim ke antrean TTE Integral?')"><i class="bx bx-cog me-1"></i>Generate & Ajukan TTE</button>@endif
        </div>
        <div class="card-body border-bottom bg-light">
            @if($signers->isEmpty())
                <div class="alert alert-warning mb-0">Belum ada akun penandatangan. Buat akun penandatangan terlebih dahulu dari Pusat TTE.</div>
            @else
            <div class="row g-3">
                <div class="col-lg-6"><label class="form-label fw-semibold">Penandatangan akhir <span class="text-danger">*</span></label><select form="generateForm" name="signer_id" class="form-select" required><option value="">Pilih penandatangan akhir</option>@foreach($signers as $signer)<option value="{{$signer->id}}" @selected(old('signer_id')==$signer->id)>{{$signer->name}} · {{$signer->jabatan?:'Jabatan belum diisi'}}</option>@endforeach</select></div>
                <div class="col-lg-6"><label class="form-label fw-semibold">Paraf berjenjang <span class="text-muted fw-normal">(opsional)</span></label><select form="generateForm" name="reviewer_ids[]" class="form-select" multiple size="3">@foreach($signers as $signer)<option value="{{$signer->id}}" @selected(in_array($signer->id,old('reviewer_ids',[])))>{{$signer->name}} · {{$signer->jabatan?:'Jabatan belum diisi'}}</option>@endforeach</select><div class="form-text">Gunakan Ctrl/Command untuk memilih lebih dari satu. Urutan mengikuti pilihan daftar.</div></div>
            </div>
            @endif
        </div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th><input type="checkbox" class="form-check-input" id="checkAll"></th><th>Peserta</th><th>Data Template</th><th>Nomor</th><th>Status Proses</th><th class="text-end">Aksi</th></tr></thead><tbody>
        @forelse($participants as $participant)
            @php
                $certificate=$certificates->get($participant->id);
                $tteDocument=$certificate?->electronicSignatureDocuments?->sortByDesc('id')->first();
                $isSent=filled($certificate?->sent_at);
                $isReady=filled($certificate?->final_file_path)&&!$isSent;
                $isTte=in_array($tteDocument?->status,['waiting','processing'],true);
            @endphp
            <tr>
                <td><input type="checkbox" form="generateForm" class="form-check-input participant-check" name="participant_ids[]" value="{{$participant->id}}" @disabled($isTte)></td>
                <td><strong>{{$participant->name}}</strong><small class="d-block text-muted">{{$participant->nip_nik?:'NIP/NIK belum tersedia'}}</small></td>
                <td><small>{{$participant->jabatan?:$participant->user?->jabatan?:'-'}}<br>{{$participant->instansi?:$participant->user?->instansi?:'-'}}</small><br><span class="badge bg-label-{{$participant->pasFotoFile?'success':'warning'}}">Foto {{$participant->pasFotoFile?'tersedia':'belum ada'}}</span></td>
                <td class="font-monospace small">{{$certificate?->certificate_number?:'Belum ditetapkan'}}</td>
                <td>
                    @if($isSent)<span class="badge bg-success"><i class="bx bx-check me-1"></i>Sudah dikirim</span><small class="d-block text-muted mt-1">{{$certificate->sent_at->translatedFormat('d M Y H:i')}}</small>
                    @elseif($isReady)<span class="badge bg-label-info"><i class="bx bx-check-shield me-1"></i>Siap dikirim</span>
                    @elseif($isTte)<span class="badge bg-label-warning"><i class="bx bx-time me-1"></i>Proses TTE</span>
                    @elseif($certificate?->generated_at)<span class="badge bg-label-primary">Menunggu TTE</span>
                    @else<span class="badge bg-label-secondary">Belum generate</span>@endif
                </td>
                <td class="text-end"><div class="d-inline-flex gap-1">
                    @if($certificate?->final_file_path)<a href="{{route('participant-certificates.download',$certificate)}}" class="btn btn-sm btn-outline-primary" title="Periksa PDF"><i class="bx bx-show"></i></a>@endif
                    @if($isReady)<form method="POST" action="{{route('training-certificates.send',$certificate)}}" onsubmit="return confirm('Kirim sertifikat ini ke akun {{$participant->name}}?')">@csrf<button class="btn btn-sm btn-success"><i class="bx bx-send me-1"></i>Kirim</button></form>@endif
                </div></td>
            </tr>
        @empty<tr><td colspan="6" class="text-center text-muted py-5">Belum ada peserta approved.</td></tr>@endforelse
        </tbody></table></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div><h5 class="fw-bold mb-1">3. Kirim ke Peserta</h5><p class="text-muted mb-0">Periksa sampel PDF terlebih dahulu. Hanya sertifikat selesai TTE yang dapat dikirim.</p></div>
            <form method="POST" action="{{route('training-certificates.send-ready',$training)}}" onsubmit="return confirm('Kirim semua {{$readyCount}} sertifikat yang sudah selesai TTE ke akun peserta?')">@csrf<button class="btn btn-success" @disabled($readyCount===0)><i class="bx bx-send me-1"></i>Kirim Semua yang Siap ({{$readyCount}})</button></form>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>document.getElementById('checkAll')?.addEventListener('change',event=>document.querySelectorAll('.participant-check:not(:disabled)').forEach(item=>item.checked=event.target.checked));</script>
@endpush