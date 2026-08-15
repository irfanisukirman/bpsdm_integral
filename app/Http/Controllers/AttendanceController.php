<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Schedule;
use App\Models\Participant;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon; 


class AttendanceController extends Controller
{
    // --- SISI ADMIN ---
    public function index($training_id)
    {
        $training = Training::findOrFail($training_id);

        // Ambil tanggal unik dari jadwal pelatihan ini
        $dates = Schedule::where('training_id', $training_id)
            ->select('date', 'attendance_open', 'attendance_close')
            ->groupBy('date', 'attendance_open', 'attendance_close')
            ->orderBy('date', 'asc')
            ->get();

        // Hitung total peserta untuk persentase
        $totalParticipants = Participant::where('training_id', $training_id)->count();

        return view('attendance.index', compact('training', 'dates', 'totalParticipants'));
    }

    public function setTimeByDate(Request $request, $training_id)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance_open' => 'required',
            'attendance_close' => 'required',
        ]);

        Schedule::where('training_id', $training_id)
            ->where('date', $request->date)
            ->update([
                'attendance_open' => $request->attendance_open,
                'attendance_close' => $request->attendance_close,
            ]);

        return redirect()->back()->with('success', 'Waktu absensi harian berhasil diperbarui.');
    }

    public function showDetailDaily($id, $date)
    {
        $training = Training::findOrFail($id);
        $participants = Participant::where('training_id', $id)->orderBy('name')->get();
        
        // Ambil ID semua sesi di tanggal tersebut
        $scheduleIds = Schedule::where('training_id', $id)->where('date', $date)->pluck('id');

        return view('attendance.show_daily', compact('training', 'participants', 'date', 'scheduleIds'));
    }

    /**
     * SISI PUBLIK: Form Absen Harian
     */
    public function publicShowDaily($training_id, $date)
    {
        $training = Training::findOrFail($training_id);
        $setup = Schedule::where('training_id', $training_id)->where('date', $date)->first();

        if (!$setup || !$setup->attendance_open) {
            $status = 'not_set';
            return view('attendance.public_form_daily', compact('training', 'date', 'status'));
        }

        $now = \Carbon\Carbon::now('Asia/Jakarta');
        $currentTime = $now->format('H:i:s');
        
        if ($currentTime < $setup->attendance_open || $currentTime > $setup->attendance_close) {
            $status = 'closed';
            $open = $setup->attendance_open;
            $close = $setup->attendance_close;
            return view('attendance.public_form_daily', compact('training', 'date', 'status', 'open', 'close'));
        }

        // LOGIKA FILTER PESERTA
        $scheduleIds = Schedule::where('training_id', $training_id)->where('date', $date)->pluck('id');
        
        // Ambil ID peserta yang sudah absen hari ini
        $attendedIds = Attendance::whereIn('schedule_id', $scheduleIds)->pluck('participant_id');

        // Peserta yang BELUM absen (untuk Dropdown)
        $notAttended = Participant::where('training_id', $training_id)
            ->whereNotIn('id', $attendedIds)
            ->orderBy('name', 'asc')
            ->get();

        // Peserta yang SUDAH absen (untuk Daftar Progres)
        $attended = Participant::where('training_id', $training_id)
            ->whereIn('id', $attendedIds)
            ->orderBy('name', 'asc')
            ->get();

        $status = 'open';
        return view('attendance.public_form_daily', compact('training', 'date', 'notAttended', 'attended', 'status', 'setup'));
    }

    /**
     * SISI PUBLIK: Simpan Absen Harian
     */
    public function publicStoreDaily(Request $request, $training_id, $date)
    {
        $request->validate([
            'participant_id' => 'required',
            'status' => 'required',
            'local_checkin_time' => 'required',
            'timezone_label' => 'required'
        ]);

        // Sesuai probis harian: absen dicatat ke sesi pertama di hari tersebut
        $firstSchedule = Schedule::where('training_id', $training_id)->where('date', $date)->first();

        Attendance::updateOrCreate(
            [
                'schedule_id' => $firstSchedule->id, 
                'participant_id' => $request->participant_id
            ],
            [
                'status' => $request->status,
                'check_in_at' => $request->local_checkin_time, // Gunakan waktu dari perangkat peserta
                'timezone_label' => $request->timezone_label, // Simpan WIB/WITA/WIT
            ]
        );

        return redirect()->back()->with('success', 'Presensi berhasil dicatat pada pukul ' . date('H:i', strtotime($request->local_checkin_time)) . ' ' . $request->timezone_label);
    }

    // --- SISI PUBLIK (PESERTA) ---
    public function publicShow($schedule_id)
    {
        $schedule = Schedule::with('training.participants')->findOrFail($schedule_id);
        
        // Ambil daftar peserta yang belum absen di sesi ini
        $already_attended = Attendance::where('schedule_id', $schedule_id)->pluck('participant_id');
        $participants = Participant::where('training_id', $schedule->training_id)
                                    ->whereNotIn('id', $already_attended)
                                    ->orderBy('name')
                                    ->get();

        return view('attendance.public_form', compact('schedule', 'participants'));
    }

    public function showDetail($schedule_id) {
        $schedule = Schedule::with('attendances')->findOrFail($schedule_id);
        $participants = Participant::where('training_id', $schedule->training_id)->orderBy('name')->get();
        return view('attendance.show', compact('schedule', 'participants'));
    }

    public function publicStore(Request $request, $schedule_id)
    {
        $request->validate([
            'participant_id' => 'required',
            'status' => 'required'
        ]);

        Attendance::create([
            'schedule_id' => $schedule_id,
            'participant_id' => $request->participant_id,
            'status' => $request->status,
            'check_in_at' => now(),
            'keterangan' => $request->keterangan
        ]);

        return redirect()->back()->with('success', 'Absensi berhasil dikirim!');
    }

    public function downloadPdf($schedule_id) {
        $schedule = Schedule::with(['attendances.participant', 'training'])->findOrFail($schedule_id);
        $data = [
            'schedule' => $schedule,
            'attendances' => $schedule->attendances
        ];
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('attendance.pdf_template', $data);
        return $pdf->download('Daftar-Hadir-'.$schedule->date.'.pdf');
    }

    public function indexAll()
    {
        $query = Training::query();

        // Probis: Admin Bidang hanya melihat pelatihan bidangnya sendiri
        if (Auth::user()->role !== 'superadmin') {
            $query->where('bidang', Auth::user()->bidang);
        }

        $trainings = $query->latest()->get();

        return view('attendance.all', compact('trainings'));
    }

    public function setTime(Request $request, $schedule_id)
    {
        $request->validate([
            'attendance_open' => 'required',
            'attendance_close' => 'required',
        ]);

        $schedule = Schedule::findOrFail($schedule_id);
        $schedule->update([
            'attendance_open' => $request->attendance_open,
            'attendance_close' => $request->attendance_close,
        ]);

        return redirect()->back()->with('success', 'Waktu absensi untuk sesi ' . $schedule->activity . ' berhasil diperbarui.');
    }
    
    public function downloadPdfDaily($id, $date)
    {
        $training = Training::findOrFail($id);
        $participants = Participant::where('training_id', $id)->orderBy('name')->get();
        
        // Ambil semua ID sesi pada tanggal tersebut
        $scheduleIds = Schedule::where('training_id', $id)->where('date', $date)->pluck('id');

        // Ambil data kehadiran untuk dikirim ke view PDF
        $attendances = Attendance::whereIn('schedule_id', $scheduleIds)->get();

        $data = [
            'training' => $training,
            'participants' => $participants,
            'date' => $date,
            'attendances' => $attendances
        ];

        $pdf = Pdf::loadView('attendance.pdf_daily', $data);
        
        // Set kertas ke portrait A4
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('Presensi-'.$training->id.'-'.$date.'.pdf');
    }

    public function downloadPdfAll($id)
    {
        $training = Training::with(['participants', 'schedules.attendances'])->findOrFail($id);
        
        // Ambil daftar tanggal unik dari jadwal
        $dates = $training->schedules->pluck('date')->unique()->sort();
        
        // Ambil data peserta diurutkan berdasarkan nama
        $participants = $training->participants()->orderBy('name')->get();

        $data = [
            'training'     => $training,
            'dates'        => $dates,
            'participants' => $participants
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('attendance.pdf_all_days', $data);
        
        // Gunakan Landscape jika jumlah hari cukup banyak (lebih dari 5 hari)
        if($dates->count() > 5) {
            $pdf->setPaper('a4', 'landscape');
        } else {
            $pdf->setPaper('a4', 'portrait');
        }

        return $pdf->download('Rekap-Kehadiran-Total-'.$training->id.'.pdf');
    }

    public function downloadExcelAll($id)
    {
        $training = Training::with(['participants', 'schedules.attendances'])->findOrFail($id);
        $dates = $training->schedules->pluck('date')->unique()->sort();
        $participants = $training->participants()->orderBy('name')->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AttendanceTotalExport($training, $dates, $participants), 
            'Rekap-Absensi-'.$training->id.'.xlsx'
        );
    }
}
