<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ParticipantTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [new ParticipantImportSheet(), new ParticipantImportGuideSheet()];
    }
}

class ParticipantImportSheet implements FromArray, WithTitle, WithEvents, ShouldAutoSize
{
    public function title(): string { return 'Import Peserta'; }
    public function array(): array
    {
        return [
            ['nip_nik', 'nama_lengkap', 'instansi'],
            ["'199503032024011001", 'Contoh Nama Peserta', 'BPSDM Provinsi Jawa Barat'],
        ];
    }
    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $sheet->freezePane('A2');
            $sheet->getStyle('A1:C1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A1:C1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF5065D5');
            $sheet->getStyle('A2:A1000')->getNumberFormat()->setFormatCode('@');
            $sheet->setAutoFilter('A1:C1');
        }];
    }
}

class ParticipantImportGuideSheet implements FromArray, WithTitle, ShouldAutoSize
{
    public function title(): string { return 'Petunjuk'; }
    public function array(): array
    {
        return [
            ['PETUNJUK IMPORT PESERTA'],
            ['1', 'Jangan mengubah nama kolom pada sheet Import Peserta.'],
            ['2', 'NIP/NIK, nama lengkap, dan instansi wajib diisi.'],
            ['3', 'Format kolom NIP/NIK sebagai teks agar angka tidak berubah.'],
            ['4', 'Akun lama akan langsung ditambahkan sebagai peserta tanpa mengubah profilnya.'],
            ['5', 'Akun baru memperoleh password acak pada file hasil import dan wajib melengkapi profil saat login pertama.'],
            ['6', 'Peserta hasil import langsung berstatus disetujui pada pelatihan.'],
        ];
    }
}