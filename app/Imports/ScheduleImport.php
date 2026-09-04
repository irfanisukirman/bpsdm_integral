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
        $typeInput = strtolower(trim($row['jenis_jadwal'] ?? $row['jenis'] ?? 'pembelajaran'));
        $scheduleType = in_array($typeInput, ['istirahat', 'break', 'jeda', 'ishoma', 'coffee break'], true) ? 'break' : 'learning';
        $unit = strtoupper(trim($row['satuan'] ?? 'JP'));
        $unit = in_array($unit, ['JP', 'OJ'], true) ? $unit : 'JP';
        $amount = isset($row['jumlah']) && is_numeric($row['jumlah'])
            ? (int) $row['jumlah']
            : (isset($row['jp']) && is_numeric($row['jp']) ? (int) $row['jp'] : null);
        $endTime = !empty($row['jam_selesai'])
            ? $this->formatTime($row['jam_selesai'])
            : Carbon::parse($startTime)->addMinutes(($amount ?? 0) * ($unit === 'OJ' ? 60 : 45))->format('H:i');
        if ($scheduleType === 'break' && empty($row['jam_selesai'])) {
            throw new \InvalidArgumentException('Jam selesai wajib diisi untuk jadwal istirahat.');
        }

        // Pencocokan Pengajar
        $pengajarId = null;
        $pengajarKeyword = trim($row['tenaga_pengajar_fasilitator'] ?? '');

        if ($scheduleType === 'learning' && !empty($pengajarKeyword)) {
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
            'schedule_type' => $scheduleType,
            'jp'          => $scheduleType === 'learning' ? $amount : null,
            'duration_unit' => $scheduleType === 'learning' ? $unit : null,
            'link_zoom'   => $scheduleType === 'learning' && !empty($zoomLink) ? trim($zoomLink) : null,
            'pengajar_id' => $pengajarId,
            'pic'         => $scheduleType === 'learning' ? ($row['penanggung_jawab_pic'] ?? 'Panitia') : 'Istirahat',
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
