<?php

namespace App\Exports;

use App\Models\ComplaintTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class ComplaintInboxExport extends StringValueBinder implements FromQuery, WithCustomValueBinder, WithHeadings, WithMapping
{
    public function __construct(private User $staff, private ?string $status) {}

    public function query(): Builder
    {
        return ComplaintTicket::staffInbox($this->staff)
            ->when($this->status, fn (Builder $query) => $query->where('status', $this->status))
            ->with(['user:id,name', 'assignee:id,name', 'training:id,nama_pelatihan', 'firstCustomerMessage', 'latestStaffMessage'])
            ->latest('updated_at')->orderByDesc('id');
    }

    public function headings(): array
    {
        return [
            'Tiket', 'Jenis', 'Kategori', 'Pengirim', 'Instansi', 'Pelatihan', 'Ditangani oleh', 'Status',
            'Pengaduan', 'Tanggapan Terakhir', 'Dibuat (WIB)', 'Diperbarui (WIB)',
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->reference(),
            $ticket->chat_name ? 'Chat dengan Admin' : 'Pengaduan',
            $ticket->chat_name ? null : ComplaintTicket::CATEGORIES[$ticket->category],
            $ticket->chat_name ?? ($ticket->user?->name ?: ($ticket->guest_name ?: 'Tamu')),
            $ticket->chat_institution,
            $ticket->training?->nama_pelatihan,
            $ticket->assignee?->name,
            ComplaintTicket::STATUSES[$ticket->status],
            $ticket->firstCustomerMessage?->body,
            $ticket->latestStaffMessage?->body,
            $ticket->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
            $ticket->updated_at->timezone('Asia/Jakarta')->format('d/m/Y H:i'),
        ];
    }
}
