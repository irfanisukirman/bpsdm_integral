<?php

namespace App\Exports;

use App\Models\LevelOneRecapImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class LevelOneImportedRecapExport extends DefaultValueBinder implements FromArray, WithCustomValueBinder, WithStrictNullComparison
{
    public function __construct(private LevelOneRecapImport $import) {}

    public function array(): array
    {
        $speaker = $this->import->kind === 'narasumber';
        $rows = [[
            $speaker ? 'Narasumber' : 'Peserta', ...($speaker ? ['Materi'] : []),
            'Rerata', ...array_column($this->import->indicators, 'label'),
        ]];
        foreach ($this->import->entries as $entry) {
            $rows[] = [
                $entry['linked_name'] ?? $entry['name'] ?? 'Anonim',
                ...($speaker ? [$entry['material']] : []),
                $entry['average'], ...$entry['values'],
            ];
        }

        return $rows;
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
