<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LevelOneRecapReader
{
    public function read(string $path): array
    {
        try {
            $reader = IOFactory::createReaderForFile($path, [IOFactory::READER_XLSX, IOFactory::READER_XLS]);
            $sheet = $reader->setReadDataOnly(true)->load($path)->getSheet(0);
        } catch (\Exception) {
            throw ValidationException::withMessages(['file' => 'File Excel tidak dapat dibaca.']);
        }

        $organizerTitle = str_contains(Str::lower((string) $this->value($sheet, 1, 1)), 'penyelenggara');
        foreach (range(1, 24) as $row) {
            if (rtrim(Str::lower((string) $this->value($sheet, 1, $row)), '.') !== 'no') continue;
            $label = Str::lower((string) $this->value($sheet, 2, $row));
            if (str_contains($label, 'narasumber') || str_contains($label, 'pengajar')) {
                return $this->parse($sheet, 'narasumber', $row + 1, $row + 2);
            }
            if (in_array($label, ['nama lengkap', 'nama peserta', 'nama'], true)) {
                return $this->parse($sheet, 'penyelenggara', $row, $row + 1);
            }
            if ($organizerTitle) {
                return $this->parse($sheet, 'penyelenggara', $row, $row + 1, $label === '' ? 3 : 2, false);
            }
        }

        throw ValidationException::withMessages(['file' => 'Header rekap tidak dikenali. Gunakan format rekap narasumber atau penyelenggara Level 1.']);
    }

    private function parse(Worksheet $sheet, string $kind, int $header, int $start, int $firstIndicator = 3, bool $hasNameColumn = true): array
    {
        $speaker = $kind === 'narasumber';
        $indicators = [];
        $lastRow = $sheet->getHighestDataRow('A');
        if ($lastRow - $start + 1 > 500) {
            throw ValidationException::withMessages(['file' => 'Maksimal 500 baris per file.']);
        }

        for ($column = $firstIndicator; $column < $firstIndicator + 50; $column++) {
            $label = trim((string) $this->value($sheet, $column, $header));
            if ($label === '' || ($speaker && Str::lower($label) === 'jumlah')) break;
            $indicators[] = [
                'label' => $label,
                'kind' => !$speaker && preg_match('/^(?:\(materi yang dibahas\)\s*)?(?:materi\s*\/\s*paparan|mohon saudara|saran|catatan|sebutkan materi|menurut pendapat anda)\b/iu', $label)
                    ? 'text' : 'score',
            ];
        }
        if (!$indicators) {
            throw ValidationException::withMessages(['file' => 'Kolom indikator belum ditemukan.']);
        }

        $entries = [];
        for ($row = $start; $row <= $lastRow; $row++) {
            $number = $this->value($sheet, 1, $row);
            if (!is_numeric($number) || (int) $number < 1) continue;
            $identity = $hasNameColumn ? trim((string) $this->value($sheet, 2, $row)) : '';
            if ($speaker && in_array($identity, ['', '-'], true)) {
                throw ValidationException::withMessages(['file' => "Nama narasumber pada baris {$row} belum diisi."]);
            }
            [$name, $material] = $speaker ? array_pad(explode('/', $identity, 2), 2, '') : [$identity, ''];
            $values = [];
            foreach ($indicators as $index => $indicator) {
                $value = $this->value($sheet, $index + $firstIndicator, $row);
                if ($indicator['kind'] === 'score' && $value !== null && (!is_numeric($value) || $value < 0 || $value > 100)) {
                    throw ValidationException::withMessages(['file' => "Nilai pada baris {$row}, kolom {$indicator['label']} harus antara 0 dan 100."]);
                }
                $values[] = $value === null ? null : ($indicator['kind'] === 'score' ? (float) $value : (string) $value);
            }
            if (!array_filter($values, fn ($value) => $value !== null)) continue;
            $scores = array_filter($values, fn ($value, $index) => $value !== null && $indicators[$index]['kind'] === 'score', ARRAY_FILTER_USE_BOTH);
            $entries[] = [
                'row' => $row,
                'name' => in_array(trim($name), ['', '-'], true) ? null : trim($name),
                'material' => trim($material),
                'values' => $values,
                'average' => $scores ? round(array_sum($scores) / count($scores), 2) : null,
            ];
        }
        if (!$entries) {
            throw ValidationException::withMessages(['file' => 'File tidak memuat baris nilai atau masukan yang bisa diimpor.']);
        }

        $source = Str::limit((string) $this->value($sheet, 1, 2), 255, '');
        $fingerprint = hash('sha256', json_encode([$kind, $indicators, array_map(fn ($entry) => array_diff_key($entry, ['row' => true]), $entries)], JSON_THROW_ON_ERROR));

        return ['kind' => $kind, 'source_training' => $source ?: null, 'indicators' => $indicators, 'entries' => $entries, 'fingerprint' => $fingerprint];
    }

    private function value(Worksheet $sheet, int $column, int $row): mixed
    {
        $cell = $sheet->getCell([$column, $row]);
        $value = $cell->isFormula() ? $cell->getOldCalculatedValue() : $cell->getValue();
        if (is_string($value)) {
            $value = trim($value);
        }

        return $value === '' ? null : $value;
    }
}
