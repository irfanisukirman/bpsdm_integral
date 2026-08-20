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
        $query = Participant::query()->with('training');

        // Filter Bidang untuk Admin Bidang
        if ($user->role !== 'superadmin') {
            $query->whereHas('training', function ($q) use ($user) {
                $q->where('bidang', $user->bidang);
            });
        }

        $alumni = $query->get();
        $totalAlumni = $alumni->count();

        // 1. Data Gender
        $genderStats = [
            'Laki-Laki' => $alumni->where('gender', 'Laki-Laki')->count(),
            'Perempuan' => $alumni->where('gender', 'Perempuan')->count(),
        ];

        // 2. Data Wilayah 3T (Terdepan, Terluar, Tertinggal)
        // Logika: Membandingkan kabupaten_kota dengan list daerah 3T di Indonesia
        $list3T = $list3T = array_unique(array_merge(
            config('wilayah.tertinggal'),
            config('wilayah.perbatasan')
        ));
        $stats3T = [
            'Wilayah 3T' => $alumni->filter(fn($a) => in_array($a->kabupaten_kota, $list3T))->count(),
            'Non-3T' => $alumni->filter(fn($a) => !in_array($a->kabupaten_kota, $list3T))->count(),
        ];

        // 3. Sebaran Provinsi
        $provinsiStats = $alumni->groupBy('provinsi')->map->count();

        // 3b. Sebaran per Kabupaten/Kota (untuk peta)
        $kabupatenStats = $alumni->groupBy('kabupaten_kota')->map->count();

        // 4. Data Pendidikan (Dari Alumni Profile L34)
        $eduStats = AlumniProfile::whereIn('participant_id', $alumni->pluck('id'))
            ->select('edu_current', DB::raw('count(*) as total'))
            ->groupBy('edu_current')
            ->get();

        // 5. Status Kepegawaian
        $statusStats = $alumni->groupBy('status_kepegawaian')->map->count();

        $koordinatKabupaten = config('wilayah.koordinat_kota_kabupaten');
        $koordinatProvinsi = config('wilayah.koordinat_provinsi');

        return view('alumni.index', compact(
            'totalAlumni',
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
