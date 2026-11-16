@if(in_array($document->mime,['application/pdf','image/jpeg','image/png','image/webp'],true))
<a class="btn btn-sm btn-outline-primary" href="{{ route('post-evaluation.evidence',[$training,$document->id,'preview'=>1]) }}" target="_blank" rel="noopener">{{ str_starts_with($document->mime,'image/') ? 'Lihat foto evidence' : 'Lihat Proper' }}</a>
@endif
<a class="btn btn-sm btn-outline-secondary" href="{{ route('post-evaluation.evidence',[$training,$document->id]) }}">Unduh</a>
