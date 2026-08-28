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
        $query = Participant::query()->with(['training', 'user']);

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

        // Gunakan data peserta sebagai sumber utama dan profil user sebagai fallback.
        $normalizeWilayah = static function ($value) {
            return preg_replace('/\s+/', ' ', strtoupper(trim((string) $value)));
        };

        $wilayah3T = collect([
            'Tertinggal' => config('wilayah.tertinggal', []),
            'Terdepan' => config('wilayah.terdepan', config('wilayah.perbatasan', [])),
            'Terluar' => config('wilayah.terluar', []),
        ])->map(fn ($items) => collect($items)->map($normalizeWilayah)->values());

        $list3T = $wilayah3T->flatten()->unique()->values()->all();
        $wilayahRows = $alumni->map(function ($participant) use ($normalizeWilayah, $wilayah3T) {
            $provinsi = $participant->provinsi ?: $participant->user?->provinsi;
            $kota = $participant->kota ?: $participant->user?->kota;
            $kecamatan = $participant->kecamatan ?: $participant->user?->kecamatan;
            $kelurahan = $participant->kelurahan ?: $participant->user?->kelurahan;
            $normalizedKota = $normalizeWilayah($kota);
            $kategori3T = $wilayah3T
                ->filter(fn ($items) => $items->contains($normalizedKota))
                ->keys()
                ->values()
                ->all();

            return [
                'id' => $participant->id,
                'nama' => $participant->user?->name ?: $participant->name,
                'nip_nik' => $participant->nip_nik,
                'provinsi' => $normalizeWilayah($provinsi) ?: 'BELUM DIISI',
                'kota' => $normalizedKota ?: 'BELUM DIISI',
                'kecamatan' => $normalizeWilayah($kecamatan) ?: 'BELUM DIISI',
                'kelurahan' => $normalizeWilayah($kelurahan) ?: 'BELUM DIISI',
                'kategori_3t' => $kategori3T,
                'is_3t' => count($kategori3T) > 0,
            ];
        });

        // 2. Data Wilayah 3T (Terdepan, Terluar, Tertinggal)
        $stats3T = [
            'Wilayah 3T' => $wilayahRows->where('is_3t', true)->count(),
            'Non-3T' => $wilayahRows->where('is_3t', false)->count(),
        ];

        // 3. Sebaran Provinsi
        $provinsiStats = $wilayahRows->groupBy('provinsi')->map->count();

        // 3b. Sebaran per Kabupaten/Kota (untuk peta)
        $kabupatenStats = $wilayahRows->groupBy('kota')->map->count();

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
            'wilayahRows',
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
