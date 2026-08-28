<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\AlumniProfile;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

class AlumniController extends Controller
{
    public function index()
    {
       $user = Auth::user();
        
        // --- 1. HITUNG ALUMNI SESUAI HAK AKSES ---
        $query = Participant::query()->with('training');

        if ($user->role !== 'superadmin') {
            $query->whereHas('training', function ($q) use ($user) {
                $q->where('bidang', $user->bidang);
            });
        }

        $alumni = $query->get();
        $totalAlumni = $alumni->count(); // Total alumni untuk tabel & chart (terfilter)

        // --- 2. HITUNG ALUMNI KESELURUHAN (Khusus Superadmin) ---
        $totalAlumniAll = 0;
        if ($user->role === 'superadmin') {
            // Jika superadmin, totalAlumni dan totalAlumniAll sama nilainya
            $totalAlumniAll = Participant::count(); 
        }

        // 3. Data Gender
        $genderStats = [
            'Laki-Laki' => $alumni->where('gender', 'Laki-Laki')->count(),
            'Perempuan' => $alumni->where('gender', 'Perempuan')->count(),
        ];

        // 4. Data Wilayah 3T
        $list3T = array_unique(array_merge(
            config('wilayah.tertinggal', []),
            config('wilayah.perbatasan', [])
        ));
        
        $stats3T = [
            'Wilayah 3T' => $alumni->filter(fn($a) => in_array($a->kota, $list3T))->count(), // Gunakan $a->kota
            'Non-3T' => $alumni->filter(fn($a) => !in_array($a->kota, $list3T))->count(),   // Gunakan $a->kota
        ];

        // 5. Sebaran Provinsi & Kabupaten
        $provinsiStats = $alumni->groupBy('provinsi')->map->count();
        $kabupatenStats = $alumni->groupBy('kota')->map->count(); // Gunakan 'kota'

        // 6. Data Pendidikan (Dari Alumni Profile L34)
        $eduStats = AlumniProfile::whereIn('participant_id', $alumni->pluck('id'))
            ->select('edu_current', DB::raw('count(*) as total'))
            ->groupBy('edu_current')
            ->get();

        // 7. Status Kepegawaian
        $statusStats = $alumni->groupBy('status_kepegawaian')->map->count();

        $koordinatKabupaten = config('wilayah.koordinat_kota_kabupaten', []);
        $koordinatProvinsi = config('wilayah.koordinat_provinsi', []);

        return view('alumni.index', compact(
            'totalAlumni',
            'totalAlumniAll', // Kirim data tambahan ini
            'genderStats',
            'stats3T',
            'provinsiStats',
            'eduStats',
            'statusStats',
            'kabupatenStats',
            'list3T',
            'koordinatKabupaten',
            'koordinatProvinsi',
        ));
    }

    public function exportExcel()
    {
        $user = Auth::user();
        $bidang = $user->bidang;
        $isSuperadmin = ($user->role === 'superadmin');

        $fileName = 'Data_Alumni_INTEGRAL_' . date('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AlumniExport($bidang, $isSuperadmin),
            $fileName
        );
    }
}