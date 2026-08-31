<?php

namespace App\Exports;

use App\Models\CertificationType;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CertificationYearExport implements WithMultipleSheets
{
    public function __construct(private int $year) {}

    public function sheets(): array
    {
        $types = CertificationType::with(['events' => fn ($query) => $query
            ->whereYear('start_date', $this->year)
            ->with(['participants'])
            ->orderBy('start_date')])
            ->orderBy('name')
            ->get();

        $sheets = [new CertificationSummarySheet($types, $this->year)];

        foreach ($types as $type) {
            $sheets[] = new CertificationTypeSheet($type, $this->year);
        }

        return $sheets;
    }
}