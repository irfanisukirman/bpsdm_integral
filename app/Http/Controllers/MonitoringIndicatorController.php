<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel; 
use App\Imports\MonitoringIndicatorImport;
use Maatwebsite\Excel\Concerns\FromArray;

class MonitoringIndicatorController extends Controller
{
    public function index()
    {
        // Ambil data yang kategorinya mengandung kata 'Monitoring'
        $indicators = Question::where('category', 'LIKE', 'Monitoring%')
            ->orderBy('category')
            ->get();

        return view('monitoring.indicators', compact('indicators'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required',
            'metode' => 'required',
            'question_text' => 'required',
        ]);

        // Secara sistem, monitoring selalu bertipe 'ya_tidak' sesuai probis sebelumnya
        $data['type'] = 'ya_tidak'; 

        Question::create($data);

        return redirect()->back()->with('success', 'Indikator monitoring berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $indicator = Question::findOrFail($id);
        $data = $request->validate([
            'category' => 'required',
            'metode' => 'required',
            'question_text' => 'required',
        ]);

        $indicator->update($data);

        return redirect()->back()->with('success', 'Indikator monitoring berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Question::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Indikator berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        // Sekarang Excel:: sudah dikenali
        Excel::import(new MonitoringIndicatorImport, $request->file('file'));

        return redirect()->back()->with('success', 'Indikator monitoring berhasil diimport.');
    }

    public function export()
    {
        $rows = [[
            'No.', 'Kategori Monitoring', 'Metode Pelatihan', 'Tipe Jawaban', 'Indikator / Pertanyaan', 'Dibuat', 'Diperbarui'
        ]];

        Question::where('category', 'LIKE', 'Monitoring%')
            ->orderBy('category')
            ->orderBy('metode')
            ->orderBy('id')
            ->each(function (Question $indicator, int $index) use (&$rows) {
                $rows[] = [
                    $index + 1,
                    $indicator->category,
                    ucfirst((string) ($indicator->metode ?: 'semua')),
                    $indicator->type,
                    $indicator->question_text,
                    optional($indicator->created_at)->format('d-m-Y H:i'),
                    optional($indicator->updated_at)->format('d-m-Y H:i'),
                ];
            });

        return Excel::download(new class($rows) implements FromArray, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithStyles {
            public function __construct(private array $rows) {}
            public function array(): array { return $this->rows; }
            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
            {
                $sheet->freezePane('A2');
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
                return [1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF5065D5']]]];
            }
        }, 'seluruh-indikator-monitoring-'.now()->format('Ymd-His').'.xlsx');
    }
    public function downloadTemplate()
    {
        $data = [
            ['kategori', 'metode', 'indikator_pertanyaan'], // Header
            ['Monitoring Penyelenggara', 'klasikal', 'Apakah sarana prasarana lengkap?'], // Contoh 1
            ['Monitoring Peserta', 'blended', 'Apakah peserta hadir tepat waktu?'], // Contoh 2
        ];

        return Excel::download(new class($data) implements FromArray {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        }, 'template_import_indikator.xlsx');
    }
}
