<?php

namespace App\Imports;

use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ScheduleImport implements ToModel, WithHeadingRow
{
    protected $trainingId;

    public function __construct($trainingId)
    {
        $this->trainingId = $trainingId;
    }

    public function model(array $row)
    {
        if (empty($row['tanggal']) || empty($row['materi_kegiatan'])) {
            return null;
        }

        // Format Tanggal
        $date = null;
        if (is_numeric($row['tanggal'])) {
            $date = Carbon::instance(ExcelDate::excelToDateTimeObject($row['tanggal']))->format('Y-m-d');
        } else {
            $date = Carbon::parse($row['tanggal'])->format('Y-m-d');
        }

        // Format Waktu
        $startTime = $this->formatTime($row['jam_mulai'] ?? '08:00');
        $endTime   = $this->formatTime($row['jam_selesai'] ?? '10:00');

        // Pencocokan Pengajar
        $pengajarId = null;
        $pengajarKeyword = trim($row['tenaga_pengajar_fasilitator'] ?? '');

        if (!empty($pengajarKeyword)) {
            $pengajar = User::whereNotIn('role', ['superadmin', 'admin_bidang', 'admin_aset'])
                ->where(fn ($query) => $query->whereNull('bidang')->orWhere('bidang', ''))
                ->where(function($q) use ($pengajarKeyword) {
                    $q->where('name', 'LIKE', "%{$pengajarKeyword}%")
                      ->orWhere('nip_nik', $pengajarKeyword);
                })
                ->first();

            if ($pengajar) {
                $pengajarId = $pengajar->id;
            }
        }

        // Tangkap Link Zoom
        $zoomLink = $row['link_zoom_virtual_meeting'] ?? $row['link_zoom'] ?? null;

        return new Schedule([
            'training_id' => $this->trainingId,
            'date'        => $date,
            'start_time'  => $startTime,
            'end_time'    => $endTime,
            'activity'    => $row['materi_kegiatan'],
            'jp'          => isset($row['jp']) && is_numeric($row['jp']) ? (int) $row['jp'] : null,
            'link_zoom'   => !empty($zoomLink) ? trim($zoomLink) : null,
            'pengajar_id' => $pengajarId,
            'pic'         => $row['penanggung_jawab_pic'] ?? 'Panitia',
        ]);
    }

    private function formatTime($val)
    {
        if (is_numeric($val)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($val))->format('H:i');
        }
        return date('H:i', strtotime($val));
    }
}
