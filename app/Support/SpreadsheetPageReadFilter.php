<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class SpreadsheetPageReadFilter implements IReadFilter
{
    public function __construct(
        private string $sheetName,
        private int $startRow,
        private int $endRow
    ) {}

    public function readCell($columnAddress, $row, $worksheetName = ''): bool
    {
        return $worksheetName === $this->sheetName
            && ($row === 1 || ($row >= $this->startRow && $row <= $this->endRow));
    }
}