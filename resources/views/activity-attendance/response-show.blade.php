@extends('layouts.master')
@section('title','Detail Respons Presensi')
@section('content')
<div class="mb-4"><a href="{{route('activity-attendance.responses',$response->form)}}" class="small"><i class="bx bx-left-arrow-alt"></i> Kembali ke Respons</a><h4 class="fw-bold mt-2 mb-1">Detail Respons</h4><p class="text-muted">{{$response->form->title}} &middot; {{$response->submitted_at->translatedFormat('d F Y H:i')}} WIB</p></div>
<div class="card border-0 shadow-sm mb-4"><div class="card-body"><small class="text-muted">Kode Respons</small><div class="font-monospace text-break">{{$response->response_token}}</div></div></div>
<div class="d-flex flex-column gap-3">@foreach($response->answers as $answer)<div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted d-block mb-1">{{$answer->question?->label}}</small>
@if($answer->file_path)@if($answer->mime_type==='image/png'||str_starts_with($answer->mime_type?:'','image/'))<img src="{{route('activity-attendance.answers.download',$answer)}}" class="img-fluid border rounded mb-2" style="max-height:260px" alt="Lampiran">@endif<div><a href="{{route('activity-attendance.answers.download',$answer)}}" class="btn btn-sm btn-outline-primary"><i class="bx bx-download me-1"></i>{{$answer->original_name}}</a></div>
@elseif($answer->value_json)<div class="d-flex flex-wrap gap-2">@foreach($answer->value_json as $value)<span class="badge bg-label-primary">{{$value}}</span>@endforeach</div>
@else<strong style="white-space:pre-line">{{$answer->value_text?:'-'}}</strong>@endif
</div></div>@endforeach</div>
@endsection
