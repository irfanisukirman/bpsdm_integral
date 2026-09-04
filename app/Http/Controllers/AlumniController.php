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
    public function index(Request $request)
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
                'latitude' => $participant->user?->latitude,
                'longitude' => $participant->user?->longitude,
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

        // Analisis pemerataan kesempatan pelatihan (informasi, bukan pembatasan).
        $currentYear = (int) now('Asia/Jakarta')->year;
        $year = (int) $request->query('year', $currentYear);
        $analysisTab = in_array($request->query('analysis_tab'), ['overview', 'repeat', 'institution'], true)
            ? $request->query('analysis_tab')
            : 'overview';
        $search = trim((string) $request->query('search'));
        $institutionFilter = trim((string) $request->query('institution'));
        $minimumFrequency = max(2, min(20, (int) $request->query('minimum_frequency', 2)));

        $yearQuery = Training::query();
        if ($user->role !== 'superadmin') {
            $yearQuery->where('bidang', $user->bidang);
        }
        $availableYears = (clone $yearQuery)->whereNotNull('tgl_mulai')
            ->selectRaw('YEAR(tgl_mulai) as training_year')
            ->distinct()->orderByDesc('training_year')->pluck('training_year')
            ->map(fn ($item) => (int) $item)->push($year)->unique()->sortDesc()->values();

        $analysisQuery = Participant::with(['training', 'user'])
            ->where('registration_status', 'approved')
            ->whereHas('training', function ($trainingQuery) use ($user, $year) {
                $trainingQuery->whereYear('tgl_mulai', $year);
                if ($user->role !== 'superadmin') {
                    $trainingQuery->where('bidang', $user->bidang);
                }
            });
        $yearParticipants = $analysisQuery->get();

        $nipToUserId = $yearParticipants->filter(fn ($participant) => $participant->user_id && filled($participant->nip_nik))
            ->mapWithKeys(fn ($participant) => [
                strtoupper(preg_replace('/[^a-z0-9]/i', '', (string) $participant->nip_nik)) => $participant->user_id,
            ]);
        $identityKey = static function ($participant) use ($nipToUserId): string {
            $nip = preg_replace('/[^a-z0-9]/i', '', (string) $participant->nip_nik);
            if ($participant->user_id) {
                return 'user:'.$participant->user_id;
            }
            if ($nip !== '' && $nipToUserId->has(strtoupper($nip))) {
                return 'user:'.$nipToUserId->get(strtoupper($nip));
            }
            return $nip !== '' ? 'nip:'.strtoupper($nip) : 'participant:'.$participant->id;
        };
        $institutionName = static fn ($participant): string => trim((string) ($participant->instansi ?: $participant->user?->instansi ?: 'Instansi belum diisi'));

        $people = $yearParticipants->groupBy($identityKey)->map(function ($records, $key) use ($institutionName) {
            $latest = $records->sortByDesc(fn ($item) => $item->training?->tgl_mulai)->first();
            $trainings = $records->filter(fn ($item) => $item->training)
                ->unique('training_id')->sortByDesc(fn ($item) => $item->training->tgl_mulai)->values();
            return [
                'key' => $key,
                'name' => $latest->name ?: $latest->user?->name ?: 'Nama belum diisi',
                'nip_nik' => $latest->nip_nik ?: $latest->user?->nip_nik ?: '-',
                'institution' => $institutionName($latest),
                'frequency' => $trainings->count(),
                'trainings' => $trainings,
                'last_training' => $trainings->first()?->training,
                'fields' => $trainings->pluck('training.bidang')->filter()->unique()->values(),
            ];
        })->values();

        $institutionOptions = $people->pluck('institution')->filter()->unique()->sort()->values();
        $repeatedPeople = $people->filter(fn ($person) => $person['frequency'] >= $minimumFrequency)
            ->when($institutionFilter !== '', fn ($items) => $items->where('institution', $institutionFilter))
            ->when($search !== '', function ($items) use ($search) {
                $needle = mb_strtolower($search);
                return $items->filter(fn ($person) => str_contains(mb_strtolower($person['name'].' '.$person['nip_nik'].' '.$person['institution']), $needle));
            })->sortByDesc('frequency')->values();

        $institutionRows = $yearParticipants->groupBy(fn ($participant) => $institutionName($participant))
            ->map(function ($records, $institution) use ($identityKey) {
                $participations = $records->unique(fn ($item) => $identityKey($item).'|'.$item->training_id)->count();
                $personGroups = $records->groupBy($identityKey);
                $uniquePeople = $personGroups->count();
                $repeatPeople = $personGroups->filter(fn ($items) => $items->pluck('training_id')->unique()->count() > 1)->count();
                $ratio = $participations > 0 ? round(($uniquePeople / $participations) * 100, 1) : 0;
                [$label, $color] = match (true) {
                    $ratio >= 80 => ['Merata', 'success'],
                    $ratio >= 60 => ['Cukup Merata', 'info'],
                    $ratio >= 40 => ['Kurang Merata', 'warning'],
                    default => ['Tidak Merata', 'danger'],
                };
                return compact('institution', 'participations', 'uniquePeople', 'repeatPeople', 'ratio', 'label', 'color');
            })
            ->when($institutionFilter !== '', fn ($items) => $items->only([$institutionFilter]))
            ->sortBy('ratio')->values();

        $repeatAll = $people->where('frequency', '>', 1);
        $topInstitution = $institutionRows->sortByDesc('repeatPeople')->first();
        $fairnessSummary = [
            'unique_people' => $people->count(),
            'repeat_people' => $repeatAll->count(),
            'repeat_percentage' => $people->count() ? round($repeatAll->count() / $people->count() * 100, 1) : 0,
            'highest_frequency' => (int) ($people->max('frequency') ?? 0),
            'top_institution' => ($topInstitution && $topInstitution['repeatPeople'] > 0) ? $topInstitution['institution'] : '-',
        ];

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
            'analysisTab',
            'year',
            'availableYears',
            'search',
            'institutionFilter',
            'minimumFrequency',
            'institutionOptions',
            'repeatedPeople',
            'institutionRows',
            'fairnessSummary',
        ));
    }

    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $bidang = $user->bidang;
        $isSuperadmin = ($user->role === 'superadmin');

        $year = (int) $request->query('year', now('Asia/Jakarta')->year);
        $institution = trim((string) $request->query('institution'));
        $minimumFrequency = max(2, min(20, (int) $request->query('minimum_frequency', 2)));
        $fileName = 'Analisis_Alumni_INTEGRAL_'.$year.'_'.date('Ymd_His').'.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AlumniComprehensiveExport($bidang, $isSuperadmin, $year, $institution, $minimumFrequency),
            $fileName
        );
    }
}
