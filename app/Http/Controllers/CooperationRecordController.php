<?php

namespace App\Http\Controllers;

use App\Models\CooperationRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

class CooperationRecordController extends Controller
{
    private const BIDANG = 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan';

    public function index(Request $request)
    {
        $this->authorizeManager();
        $this->validateFilters($request);
        $base = CooperationRecord::query();
        $years = (clone $base)->select('year')->distinct()->orderByDesc('year')->pluck('year');
        $stats = [
            'total' => (clone $base)->count(),
            'current_year' => (clone $base)->where('year', now()->year)->count(),
            'partners' => (clone $base)->distinct('partner_name')->count('partner_name'),
            'years' => $years->count(),
        ];
        $records = $this->filtered($request)->with('creator')
            ->orderByDesc('year')->latest('id')->paginate(12)->withQueryString();

        return view('cooperations.index', compact('records', 'years', 'stats'));
    }

    public function export(Request $request)
    {
        $this->authorizeManager();
        $this->validateFilters($request);
        $rows = [['No.', 'Bekerja Sama dengan', 'Tentang', 'Tahun', 'Nama Dokumen', 'Tautan Dokumen']];
        $this->filtered($request)->orderByDesc('year')->orderBy('partner_name')->get()
            ->each(function (CooperationRecord $record, int $index) use (&$rows) {
                $rows[] = [$index + 1, $record->partner_name, $record->subject, $record->year, $record->original_name, route('cooperations.download', $record)];
            });
        $yearLabel = $request->filled('year') ? '-'.$request->integer('year') : '-semua-tahun';

        return Excel::download(new class($rows) implements FromArray, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithStyles {
            public function __construct(private array $rows) {}
            public function array(): array { return $this->rows; }
            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
            {
                $sheet->freezePane('A2');
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
                return [1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF284B9B']]]];
            }
        }, 'rekap-kerja-sama'.$yearLabel.'-'.now()->format('Ymd-His').'.xlsx');
    }

    public function store(Request $request)
    {
        $this->authorizeManager();
        $data = $this->validated($request, true);
        $upload = $request->file('document');
        $path = $upload->store('cooperations/'.$data['year'], 'public');
        CooperationRecord::create([
            'partner_name' => $data['partner_name'], 'subject' => $data['subject'], 'year' => $data['year'],
            'file_path' => $path, 'original_name' => $upload->getClientOriginalName(),
            'file_type' => strtolower((string) $upload->getClientOriginalExtension()),
            'file_size' => (int) $upload->getSize(), 'created_by' => Auth::id(),
        ]);
        return back()->with('success', 'Data kerja sama dan dokumennya berhasil disimpan.');
    }

    public function update(Request $request, CooperationRecord $cooperation)
    {
        $this->authorizeManager();
        $data = $this->validated($request, false);
        $oldPath = null;
        $payload = ['partner_name' => $data['partner_name'], 'subject' => $data['subject'], 'year' => $data['year']];
        if ($request->hasFile('document')) {
            $upload = $request->file('document');
            $oldPath = $cooperation->file_path;
            $payload += [
                'file_path' => $upload->store('cooperations/'.$data['year'], 'public'),
                'original_name' => $upload->getClientOriginalName(),
                'file_type' => strtolower((string) $upload->getClientOriginalExtension()),
                'file_size' => (int) $upload->getSize(),
            ];
        }
        $cooperation->update($payload);
        if ($oldPath && $oldPath !== $cooperation->file_path) Storage::disk('public')->delete($oldPath);
        return back()->with('success', 'Data kerja sama berhasil diperbarui.');
    }

    public function download(CooperationRecord $cooperation)
    {
        $this->authorizeManager();
        abort_unless(Storage::disk('public')->exists($cooperation->file_path), 404, 'Dokumen kerja sama tidak ditemukan.');
        return Storage::disk('public')->download($cooperation->file_path, $cooperation->original_name);
    }

    public function destroy(CooperationRecord $cooperation)
    {
        $this->authorizeManager();
        $path = $cooperation->file_path;
        $cooperation->delete();
        if (!CooperationRecord::where('file_path', $path)->exists()) Storage::disk('public')->delete($path);
        return back()->with('success', 'Data kerja sama dan dokumennya berhasil dihapus.');
    }

    private function filtered(Request $request)
    {
        return CooperationRecord::query()
            ->when($request->filled('year'), fn ($query) => $query->where('year', $request->integer('year')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = trim((string) $request->search);
                $query->where(fn ($nested) => $nested->where('partner_name', 'like', "%{$term}%")
                    ->orWhere('subject', 'like', "%{$term}%")
                    ->orWhere('original_name', 'like', "%{$term}%"));
            });
    }

    private function validateFilters(Request $request): void
    {
        $request->validate([
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 10)],
            'search' => ['nullable', 'string', 'max:150'],
        ]);
    }

    private function validated(Request $request, bool $documentRequired): array
    {
        return $request->validate([
            'partner_name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:500'],
            'year' => ['required', 'integer', 'min:1900', 'max:'.(now()->year + 10)],
            'document' => [$documentRequired ? 'required' : 'nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx'],
        ], [
            'document.required' => 'Dokumen kerja sama wajib diunggah.',
            'document.max' => 'Ukuran dokumen maksimal 20 MB.',
            'document.mimes' => 'Dokumen harus berformat PDF, DOC, atau DOCX.',
        ]);
    }

    private function authorizeManager(): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->role === 'superadmin' || ($user->role === 'admin_bidang' && $user->bidang === self::BIDANG)), 403);
    }
}
