<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><style>
@page{margin:18px}body{font-family:DejaVu Sans,sans-serif;font-size:8px;color:#243447}h1{font-size:18px;margin:0 0 4px}.meta{color:#667085;margin-bottom:14px}.summary{margin-bottom:12px;padding:8px;background:#eef4ff}table{width:100%;border-collapse:collapse;table-layout:fixed}th,td{border:1px solid #9aa4b2;padding:5px;vertical-align:top;word-wrap:break-word}th{background:#e9eef8;font-weight:bold}.num{width:24px}.date{width:70px}.small{font-size:7px;color:#667085}</style></head><body>
<h1>Rekap Presensi Kegiatan</h1><div class="meta"><strong>{{$activityAttendance->title}}</strong>@if($activityAttendance->location) &middot; {{$activityAttendance->location}}@endif<br>Bidang: {{$activityAttendance->bidang?:'-'}} &middot; Dicetak {{now()->translatedFormat('d F Y H:i')}} WIB</div>
<div class="summary">Jumlah respons: <strong>{{$responses->count()}}</strong> &nbsp;|&nbsp; Periode: {{$activityAttendance->opens_at?->translatedFormat('d M Y H:i')?:'-'}} s.d. {{$activityAttendance->closes_at?->translatedFormat('d M Y H:i')?:'-'}}</div>
<table><thead><tr><th class="num">No.</th><th class="date">Waktu</th>@foreach($questions as $question)<th>{{$question->label}}</th>@endforeach</tr></thead><tbody>
@forelse($responses as $index=>$response)@php($answers=$response->answers->keyBy('activity_attendance_question_id'))<tr><td>{{$index+1}}</td><td>{{$response->submitted_at?->format('d-m-Y H:i')}}</td>@foreach($questions as $question)@php($answer=$answers->get($question->id))<td>
@if(!$answer)
-
@elseif($answer->value_json)
{{ collect($answer->value_json)->join(', ') }}
@elseif($question->type === 'signature' && isset($signatureImages[$answer->id]))
<img src="{{ $signatureImages[$answer->id] }}" alt="Tanda tangan" style="display:block;max-width:110px;max-height:55px;margin:2px auto;">
<a href="{{ route('activity-attendance.answers.download', $answer) }}" style="font-size:6px;color:#3158a5;text-decoration:none;">Buka tanda tangan</a>
@elseif($answer->file_path)
<a href="{{ route('activity-attendance.answers.download', $answer) }}" style="color:#3158a5;text-decoration:none;">{{ $question->type === 'photo' ? 'Buka foto' : 'Buka dokumen' }}</a>
<div class="small">{{ $answer->original_name }}</div>
@else
{{ $answer->value_text ?: '-' }}
@endif
</td>@endforeach</tr>
@empty<tr><td colspan="{{2+$questions->count()}}" style="text-align:center">Belum ada respons.</td></tr>@endforelse
</tbody></table></body></html>