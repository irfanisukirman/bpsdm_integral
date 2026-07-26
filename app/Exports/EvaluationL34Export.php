<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EvaluationL34Export implements WithMultipleSheets
{
    protected $training;

    public function __construct($training)
    {
        $this->training = $training;
    }

    public function sheets(): array
    {
        return [
            new L34InformasiSheet($this->training),
            new L34JawabanSheet($this->training),
            new L34OlahDataSheet($this->training), // Sheet yang berisi Tabel & Grafik
        ];
    }
}