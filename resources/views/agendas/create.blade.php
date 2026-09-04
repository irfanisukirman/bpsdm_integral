@extends('layouts.master')
@php $editing=isset($agenda);$currentSchedule=$editing?$agenda->schedules->first():null;$loan=$currentSchedule?->assetLoanRequest;$currentAssetId=$loan?->asset_ids[0]??$currentSchedule?->bookings->first()?->asset_id; @endphp
@section('title',$editing?'Edit Agenda':'Buat Agenda')
@section('content')
<div class="d-flex justify-content-between mb-4"><div><h4 class="fw-bold">{{ $editing?'Edit Agenda':'Buat Agenda' }}</h4><p class="text-muted">Catat kegiatan di luar pelatihan.</p></div><a href="{{route('agendas.index')}}" class="btn btn-outline-secondary">Kembali</a></div>
@if($errors->any())<div class="alert alert-danger"><strong>Agenda belum tersimpan.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif
<form method="POST" enctype="multipart/form-data" action="{{$editing?route('agendas.update',$agenda):route('agendas.store')}}">@csrf @if($editing) @method('PUT') @endif
<div class="card"><div class="card-body"><div class="row g-4">
<div class="col-12"><label class="form-label fw-bold">Nama Kegiatan *</label><input name="name" value="{{old('name',$agenda->name??'')}}" class="form-control" required></div>
<div class="col-md-6"><label class="form-label fw-bold">Tanggal Mulai *</label><input type="date" name="start_date" value="{{old('start_date',$currentSchedule?->starts_at?->format('Y-m-d'))}}" class="form-control" required></div>
<div class="col-md-6"><label class="form-label fw-bold">Tanggal Selesai *</label><input type="date" name="end_date" value="{{old('end_date',$currentSchedule?->ends_at?->format('Y-m-d'))}}" class="form-control" required></div>
<div class="col-md-6"><label class="form-label fw-bold">Jam Mulai *</label><input type="time" name="start_time" value="{{old('start_time',$currentSchedule?->starts_at?->format('H:i'))}}" class="form-control" required></div>
<div class="col-md-6"><label class="form-label fw-bold">Jam Selesai *</label><input type="time" name="end_time" value="{{old('end_time',$currentSchedule?->ends_at?->format('H:i'))}}" class="form-control" required></div>
<div class="col-md-4"><label class="form-label fw-bold">Jenis Agenda</label><select name="agenda_type" class="form-select"><option value="bidang" @selected(old('agenda_type',$agenda->agenda_type??'bidang')==='bidang')>Agenda Bidang</option><option value="pimpinan" @selected(old('agenda_type',$agenda->agenda_type??'bidang')==='pimpinan')>Agenda Pimpinan</option></select></div>
<div class="col-md-4"><label class="form-label fw-bold">Jenis Tempat</label><select name="scope" id="agenda_scope" class="form-select"><option value="internal" @selected(old('scope',$agenda->scope??'internal')==='internal')>Internal BPSDM</option><option value="external" @selected(old('scope',$agenda->scope??'internal')==='external')>Eksternal</option></select></div>
<div class="col-md-8" id="internal_place"><label class="form-label fw-bold">Ruangan BPSDM *</label><select name="asset_id" id="agenda_asset_id" class="form-select"><option value="">-- Pilih Ruangan --</option>@foreach($assets as $asset)<option value="{{$asset->id}}" data-label="{{$asset->name}} - {{$asset->location}} ({{$asset->capacity?:'-'}} orang)" @selected(old('asset_id',$currentAssetId)==$asset->id)>{{$asset->name}} - {{$asset->location}} ({{$asset->capacity?:'-'}} orang)</option>@endforeach</select><div id="availability_status" class="mt-2" aria-live="polite"><small class="text-muted">Isi tanggal dan jam untuk melihat ketersediaan ruangan.</small></div></div>
<div class="col-md-8 d-none" id="external_place"><label class="form-label fw-bold">Tempat Eksternal *</label><input name="external_place" value="{{old('external_place',$currentSchedule?->external_place)}}" class="form-control"></div>
<div class="col-12" id="loan_fields">
 <div class="card bg-light border"><div class="card-body"><h6 class="fw-bold"><i class="bx bx-file me-1"></i>Dokumen Peminjaman Aset</h6>
 @if($loan)<div class="alert alert-{{ ['approved'=>'success','pending'=>'warning','revision'=>'info','rejected'=>'danger'][$loan->status]??'secondary' }} py-2">Status: <strong>{{ ['approved'=>'Disetujui','pending'=>'Menunggu Persetujuan','revision'=>'Perlu Perbaikan','rejected'=>'Ditolak'][$loan->status]??ucfirst($loan->status) }}</strong>@if($loan->review_note)<br>Catatan pengelola: {{ $loan->review_note }}@endif <a href="{{route('asset-loans.document',$loan)}}" target="_blank" class="ms-2">Lihat PDF</a></div>@endif
 <div class="row g-3"><div class="col-md-6"><label class="form-label fw-bold">Surat Peminjaman PDF {{ $loan?'':'*' }}</label><input type="file" name="loan_letter" accept="application/pdf,.pdf" class="form-control"><small class="text-muted">PDF maksimal 5 MB{{ $loan?'; kosongkan jika surat tidak diganti':'' }}.</small></div>
 <div class="col-md-3"><label class="form-label fw-bold">Jumlah Pengguna</label><input type="number" min="1" name="attendee_count" value="{{old('attendee_count',$loan?->attendee_count)}}" class="form-control"></div>
 <div class="col-md-3"><label class="form-label fw-bold">Kontak Pemohon</label><input name="loan_contact" value="{{old('loan_contact',$loan?->contact_person)}}" class="form-control"></div>
 <div class="col-12"><label class="form-label fw-bold">Keperluan Peminjaman</label><textarea name="loan_purpose" rows="2" class="form-control">{{old('loan_purpose',$loan?->purpose)}}</textarea><small class="text-muted">Setelah disimpan, pengajuan akan menunggu persetujuan pengelola aset.</small></div></div>
 </div></div>
</div>
<div class="col-12"><label class="form-label fw-bold">Keterangan</label><textarea name="description" rows="4" class="form-control" placeholder="Informasi kegiatan, link Zoom, passcode, dan lainnya">{{old('description',$agenda->description??'')}}</textarea></div>
<div class="col-12"><label class="form-label fw-bold">Pelaksana</label><textarea name="executor" rows="2" class="form-control">{{old('executor',$currentSchedule?->participants_info)}}</textarea></div>
<div class="col-md-8"><label class="form-label fw-bold">Pembuat Agenda</label><input class="form-control bg-light" value="{{auth()->user()->name}} - {{auth()->user()->bidang}}" readonly></div>
<div class="col-md-4 d-flex align-items-end"><label class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_public" value="1" @checked(old('is_public',$agenda->is_public??false))> Tampilkan di kalender publik</label></div>
</div></div><div class="card-footer text-end"><button class="btn btn-primary"><i class="bx bx-save me-1"></i>{{$editing?'Perbarui Agenda':'Simpan Agenda'}}</button></div></div></form>
@endsection
@push('js')
<script>
$(function () {
    const availabilityUrl = @json(route('agendas.availability'));
    const agendaId = @json($editing ? $agenda->id : null);
    let timer;
    function resetOptions() {
        $('#agenda_asset_id option[value!=""]').each(function () {
            const option = $(this);
            option.prop('disabled', false).text(option.data('label'));
        });
    }
    function togglePlace() {
        const internal = $('#agenda_scope').val() === 'internal';
        $('#internal_place').toggleClass('d-none', !internal);
        $('#external_place').toggleClass('d-none', internal);
        $('#loan_fields').toggleClass('d-none', !internal);
        if (internal) scheduleCheck(); else $('#availability_status').empty();
    }
    function scheduleCheck() { clearTimeout(timer); timer = setTimeout(checkAvailability, 250); }
    function checkAvailability() {
        if ($('#agenda_scope').val() !== 'internal') return;
        const params = {
            start_date: $('[name="start_date"]').val(), end_date: $('[name="end_date"]').val(),
            start_time: $('[name="start_time"]').val(), end_time: $('[name="end_time"]').val(),
            agenda_id: agendaId
        };
        resetOptions();
        if (!params.start_date || !params.end_date || !params.start_time || !params.end_time) {
            $('#availability_status').html('<small class="text-muted">Lengkapi tanggal dan jam untuk melihat ketersediaan ruangan.</small>');
            return;
        }
        $('#availability_status').html('<small class="text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Memeriksa ketersediaan...</small>');
        $.get(availabilityUrl, params).done(function (response) {
            const conflicts = [];
            (response.assets || []).forEach(function (asset) {
                const option = $('#agenda_asset_id option[value="' + asset.id + '"]');
                if (!asset.available) {
                    option.prop('disabled', true).text(option.data('label') + ' - TERPAKAI');
                    conflicts.push(asset.message);
                }
            });
            if ($('#agenda_asset_id option:selected').prop('disabled')) $('#agenda_asset_id').val('');
            $('#availability_status').html(conflicts.length
                ? '<div class="alert alert-warning py-2 px-3 mb-0"><i class="bx bx-error-circle me-1"></i>' + conflicts.join('<br>') + '</div>'
                : '<div class="alert alert-success py-2 px-3 mb-0"><i class="bx bx-check-circle me-1"></i>Semua ruangan tersedia pada waktu tersebut.</div>');
        }).fail(function (xhr) {
            const message = xhr.responseJSON?.message || 'Ketersediaan ruangan belum dapat diperiksa.';
            $('#availability_status').html('<div class="alert alert-danger py-2 px-3 mb-0">' + message + '</div>');
        });
    }
    $('#agenda_scope').on('change', togglePlace);
    $('[name="start_date"], [name="end_date"], [name="start_time"], [name="end_time"]').on('change input', scheduleCheck);
    togglePlace();
});
</script>
@endpush
