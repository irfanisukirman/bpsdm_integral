<?php

namespace App\Exports;

use App\Models\Participant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AlumniComprehensiveExport implements WithMultipleSheets
{
    public function __construct(
        private ?string $bidang,
        private bool $isSuperadmin,
        private int $year,
        private string $institution = '',
        private int $minimumFrequency = 2
    ) {}

    public function sheets(): array
    {
        [$people, $institutionRows] = $this->analysisData();
        $repeated = $people->where('frequency', '>=', $this->minimumFrequency)->values();
        $allRepeated = $people->where('frequency', '>', 1);
        $scope = $this->isSuperadmin ? 'Semua Bidang' : ($this->bidang ?: '-');

        $summaryRows = [
            ['ANALISIS PEMERATAAN PESERTA PELATIHAN'],
            ['Tahun', $this->year],
            ['Cakupan', $scope],
            ['Filter Instansi', $this->institution ?: 'Semua Instansi'],
            ['Tanggal Cetak', now('Asia/Jakarta')->format('d/m/Y H:i')],
            [],
            ['INDIKATOR', 'NILAI'],
            ['Peserta unik', $people->count()],
            ['Peserta mengikuti lebih dari satu pelatihan', $allRepeated->count()],
            ['Persentase peserta berulang', $people->count() ? round($allRepeated->count() / $people->count() * 100, 1).'%' : '0%'],
            ['Frekuensi tertinggi', (int) ($people->max('frequency') ?? 0).' pelatihan'],
            ['Jumlah instansi', $institutionRows->count()],
            [],
            ['Catatan', 'Indikator ini merupakan bahan analisis pemerataan dan bukan larangan mengikuti pelatihan.'],
        ];

        $repeatRows = [['No', 'Nama Peserta', 'NIP/NIK', 'Instansi', 'Jumlah Pelatihan', 'Bidang yang Diikuti', 'Pelatihan Terakhir', 'Tanggal Terakhir', 'Indikator']];
        foreach ($repeated as $index => $person) {
            $indicator = $person['frequency'] >= 4 ? 'Prioritas Pemeriksaan' : ($person['frequency'] >= 3 ? 'Sering Mengikuti' : 'Perlu Diperhatikan');
            $repeatRows[] = [$index + 1, $person['name'], $this->excelNip($person['nip_nik']), $person['institution'], $person['frequency'], $person['fields']->implode(', '), $person['last_training']?->nama_pelatihan ?: '-', $person['last_training']?->tgl_mulai ? \Carbon\Carbon::parse($person['last_training']->tgl_mulai)->format('d/m/Y') : '-', $indicator];
        }

        $historyRows = [['No', 'Nama Peserta', 'NIP/NIK', 'Instansi', 'Nama Pelatihan', 'Bidang Penyelenggara', 'Tanggal Mulai', 'Tanggal Selesai', 'Status Pendaftaran']];
        $historyNumber = 1;
        foreach ($repeated as $person) {
            foreach ($person['trainings'] as $record) {
                $historyRows[] = [$historyNumber++, $person['name'], $this->excelNip($person['nip_nik']), $person['institution'], $record->training?->nama_pelatihan ?: '-', $record->training?->bidang ?: '-', $record->training?->tgl_mulai ? \Carbon\Carbon::parse($record->training->tgl_mulai)->format('d/m/Y') : '-', $record->training?->tgl_selesai ? \Carbon\Carbon::parse($record->training->tgl_selesai)->format('d/m/Y') : '-', ucfirst($record->registration_status)];
            }
        }

        $institutionExportRows = [['No', 'Instansi', 'Total Keikutsertaan', 'Peserta Unik', 'Peserta Berulang', 'Rasio Pemerataan', 'Status']];
        foreach ($institutionRows as $index => $row) {
            $institutionExportRows[] = [$index + 1, $row['institution'], $row['participations'], $row['unique_people'], $row['repeat_people'], $row['ratio'].'%', $row['label']];
        }

        return [
            new AlumniExport($this->bidang, $this->isSuperadmin),
            new AlumniAnalysisSheet('Ringkasan Pemerataan', $summaryRows, 7),
            new AlumniAnalysisSheet('Peserta Berulang', $repeatRows),
            new AlumniAnalysisSheet('Riwayat Pelatihan', $historyRows),
            new AlumniAnalysisSheet('Pemerataan Instansi', $institutionExportRows),
        ];
    }

    private function analysisData(): array
    {
        $participants = Participant::with(['training', 'user'])
            ->where('registration_status', 'approved')
            ->whereHas('training', function ($query) {
                $query->whereYear('tgl_mulai', $this->year);
                if (!$this->isSuperadmin) {
                    $query->where('bidang', $this->bidang);
                }
            })->get();

        $institutionName = static fn ($participant): string => trim((string) ($participant->instansi ?: $participant->user?->instansi ?: 'Instansi belum diisi'));
        if ($this->institution !== '') {
            $participants = $participants->filter(fn ($participant) => $institutionName($participant) === $this->institution)->values();
        }
        $nipToUser = $participants->filter(fn ($participant) => $participant->user_id && filled($participant->nip_nik))->mapWithKeys(fn ($participant) => [$this->normalizeNip($participant->nip_nik) => $participant->user_id]);
        $identity = function ($participant) use ($nipToUser): string {
            $nip = $this->normalizeNip($participant->nip_nik);
            if ($participant->user_id) return 'user:'.$participant->user_id;
            if ($nip !== '' && $nipToUser->has($nip)) return 'user:'.$nipToUser->get($nip);
            return $nip !== '' ? 'nip:'.$nip : 'participant:'.$participant->id;
        };

        $people = $participants->groupBy($identity)->map(function ($records) use ($institutionName) {
            $trainings = $records->filter(fn ($item) => $item->training)->unique('training_id')->sortByDesc(fn ($item) => $item->training->tgl_mulai)->values();
            $latest = $trainings->first() ?: $records->first();
            return [
                'name' => $latest->name ?: $latest->user?->name ?: 'Nama belum diisi',
                'nip_nik' => $latest->nip_nik ?: $latest->user?->nip_nik ?: '-',
                'institution' => $institutionName($latest),
                'frequency' => $trainings->count(),
                'trainings' => $trainings,
                'fields' => $trainings->pluck('training.bidang')->filter()->unique()->values(),
                'last_training' => $trainings->first()?->training,
            ];
        })->values();

        $institutionRows = $participants->groupBy(fn ($participant) => $institutionName($participant))->map(function ($records, $institution) use ($identity) {
            $participations = $records->unique(fn ($item) => $identity($item).'|'.$item->training_id)->count();
            $groups = $records->groupBy($identity);
            $uniquePeople = $groups->count();
            $repeatPeople = $groups->filter(fn ($items) => $items->pluck('training_id')->unique()->count() > 1)->count();
            $ratio = $participations ? round($uniquePeople / $participations * 100, 1) : 0;
            $label = match (true) { $ratio >= 80 => 'Merata', $ratio >= 60 => 'Cukup Merata', $ratio >= 40 => 'Kurang Merata', default => 'Tidak Merata' };
            return ['institution' => $institution, 'participations' => $participations, 'unique_people' => $uniquePeople, 'repeat_people' => $repeatPeople, 'ratio' => $ratio, 'label' => $label];
        })->sortBy('ratio')->values();

        return [$people, $institutionRows];
    }

    private function normalizeNip($value): string
    {
        return strtoupper(preg_replace('/[^a-z0-9]/i', '', (string) $value));
    }

    private function excelNip($value): string
    {
        return $value && $value !== '-' ? "'".$value : '-';
    }
}

class AlumniAnalysisSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    public function __construct(private string $sheetTitle, private array $rows, private int $headerRow = 1) {}
    public function title(): string { return $this->sheetTitle; }
    public function array(): array { return $this->rows; }
    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $highestColumn = $event->sheet->getDelegate()->getHighestColumn();
            $lastRow = max(1, count($this->rows));
            $range = 'A'.$this->headerRow.':'.$highestColumn.$this->headerRow;
            $event->sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $event->sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF696CFF');
            $event->sheet->getStyle('A'.$this->headerRow.':'.$highestColumn.$lastRow)->getBorders()->getAllBorders()->setBorderStyle('thin');
            $event->sheet->freezePane('A'.($this->headerRow + 1));
            if ($this->headerRow === 1) $event->sheet->setAutoFilter('A1:'.$highestColumn.$lastRow);
        }];
    }
}
