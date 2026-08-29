@extends('layouts.master')
@section('title','Monitoring Aset')
@section('content')
<div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
    <div><h4 class="fw-bold">Monitoring Aset Terpakai</h4><p class="text-muted mb-0">Timeline pemakaian 00.00–24.00</p></div>
    <form><input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()"></form>
</div>
<div class="card"><div class="card-body overflow-auto" style="min-width:850px">
    <div class="d-flex mb-2"><div style="width:180px"></div><div class="d-flex justify-content-between flex-grow-1 small text-muted">@foreach([0,3,6,9,12,15,18,21,24] as $hour)<span>{{ str_pad($hour,2,'0',STR_PAD_LEFT) }}.00</span>@endforeach</div></div>
    @foreach($assets as $assetRow)
        <div class="d-flex align-items-center border-top py-3">
            <div style="width:180px" class="pe-3"><strong>{{ $assetRow->name }}</strong><br><small class="text-muted">{{ $assetRow->location }}</small></div>
            <div class="position-relative bg-light rounded flex-grow-1" style="height:42px">
                @foreach($assetRow->bookings as $booking)
                    @php
                        $startHour=max(0,$booking->starts_at->hour+$booking->starts_at->minute/60);
                        $endHour=$booking->ends_at->isSameDay($booking->starts_at)?min(24,$booking->ends_at->hour+$booking->ends_at->minute/60):24;
                        $left=$startHour/24*100;$width=max(1,($endHour-$startHour)/24*100);
                        $isTraining=class_basename($booking->bookable_type)==='Schedule';
                        $label=$isTraining?($booking->bookable->activity??'Pelatihan'):($booking->bookable->agenda->name??'Agenda');
                        $owner=$isTraining?($booking->bookable->pic??'-'):($booking->bookable->agenda->creator->name??'-');
                        $tooltip='Aset: '.$assetRow->name.' | Kegiatan: '.$label.' | Peminjam/PIC: '.$owner.' | '.$booking->starts_at->format('H:i').'–'.$booking->ends_at->format('H:i');
                    @endphp
                    <div class="position-absolute {{ $isTraining?'bg-primary':'bg-info' }} text-white rounded px-2 text-truncate"
                         style="left:{{ $left }}%;width:{{ $width }}%;height:34px;top:4px;line-height:34px;cursor:help"
                         title="{{ $tooltip }}">{{ $label }}</div>
                @endforeach
            </div>
        </div>
    @endforeach
</div></div>
@endsection
