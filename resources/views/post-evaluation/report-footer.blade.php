<div class="footer"><span class="activity">{{ \Illuminate\Support\Str::limit($summary['training'],95) }}</span> - {{ $summary['total'] }} peserta
@if($summary['draft'])<span class="draft"> - DRAF: {{ $summary['draft'] }} wawancara belum selesai</span>@endif
 - {{ ($summary['ai_status'] ?? 'none')==='current' ? 'Narasi AI' : 'Narasi dasar' }}<span class="page">{{ $page }} / {{ $summary['page_count'] }}</span></div>
