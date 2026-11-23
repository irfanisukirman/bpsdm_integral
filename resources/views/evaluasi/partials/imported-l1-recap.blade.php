@php
    $kind = \App\Models\LevelOneRecapImport::LABELS[$import->kind];
    $indicators = collect($import->indicators)->when($notesOnly, fn ($items) => $items->where('kind', 'text'));
    $mean = collect($import->entries)->pluck('average')->avg();
@endphp
<details class="card mb-3">
    <summary class="card-header fw-semibold">
        Impor Rekap Lama · {{ $kind }} · {{ $import->filename }}
        <small class="d-block text-muted mt-1">{{ count($import->entries) }} {{ $import->kind === 'narasumber' ? 'baris pengajar/materi (agregat)' : 'respons penyelenggara' }}</small>
        @if(!$notesOnly && $mean !== null)<small class="d-block text-muted">Rerata antarbaris {{ number_format($mean, 2, ',', '.') }}</small>@endif
    </summary>
    @unless($notesOnly)
        <div class="px-3 pb-3">
            <a href="{{ route('evall1.recap-import.download', [$training, $import]) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-download me-1"></i>Unduh Rekap Excel</a>
        </div>
    @endunless
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>{{ $import->kind === 'narasumber' ? 'Narasumber / Materi' : 'Peserta' }}</th>
                    @unless($notesOnly)<th>Rerata</th>@endunless
                    @foreach($indicators as $indicator)<th style="min-width: 10rem">{{ $indicator['label'] }}</th>@endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($import->entries as $entry)
                    <tr>
                        <th scope="row" style="min-width: 11rem">
                            {{ $entry['linked_name'] ?? $entry['name'] ?? 'Anonim' }}
                            @if($entry['material'])<small class="d-block text-muted">{{ $entry['material'] }}</small>@endif
                        </th>
                        @unless($notesOnly)<td>{{ $entry['average'] === null ? '—' : number_format($entry['average'], 2, ',', '.') }}</td>@endunless
                        @foreach($indicators as $index => $indicator)
                            <td>{{ $entry['values'][$index] ?? '—' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</details>
