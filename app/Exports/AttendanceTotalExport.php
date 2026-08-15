<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceTotalExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $training, $dates, $participants;

    public function __construct($training, $dates, $participants)
    {
        $this->training = $training;
        $this->dates = $dates;
        $this->participants = $participants;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            // Hapus baris 5 yang sebelumnya dipaksa jadi hitam (000000)
        ];
    }

    public function view(): View
    {
        return view('attendance.excel_all_days', [
            'training' => $this->training,
            'dates' => $this->dates,
            'participants' => $this->participants
        ]);
    }
    
}