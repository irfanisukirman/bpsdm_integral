<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // List bidang agar mudah dipanggil di mana-mana
    public static $listBidang = [
        'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan',
        'Bidang Pengembangan Kompetensi Teknis Inti',
        'Bidang Pengembangan Kompetensi Teknis Umum',
        'Bidang Pengembangan Kompetensi Manajerial',
        'Sekretariat'
    ];

    public function index(Request $request)
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $adminRoles = ['superadmin', 'admin_bidang', 'admin_aset', 'pengelola_magang', 'resepsionis', 'pengelola_keuangan', 'manajemen_mutu', 'kasubag_pppk_pw'];

        $stats = [
            'all' => User::count(),
            'admin' => User::whereIn('role', $adminRoles)->count(),
            'peserta' => User::where('user_type', 'peserta')->count(),
            'narasumber' => User::where('user_type', 'narasumber')->count(),
            'mitra' => User::where('user_type', 'mitra')->count(),
        ];

        $users = User::latest()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%")
                        ->orWhere('username', 'LIKE', "%$search%")
                        ->orWhere('nip_nik', 'LIKE', "%$search%")
                        ->orWhere('bidang', 'LIKE', "%$search%")
                        ->orWhere('role', 'LIKE', "%$search%")
                        ->orWhere('user_type', 'LIKE', "%$search%")
                        ->orWhere('instansi', 'LIKE', "%$search%")
                        ->orWhere('jabatan', 'LIKE', "%$search%");
                });
            })
            ->when($category === 'admin', fn ($query) => $query->whereIn('role', $adminRoles))
            ->when(in_array($category, ['peserta', 'narasumber', 'mitra'], true),
                fn ($query) => $query->where('user_type', $category))
            ->paginate(15)
            ->withQueryString();

        $listBidang = self::$listBidang;

        return view('users.index', compact('users', 'listBidang', 'search', 'category', 'stats'));
    }
    public function store(Request $request)
    {
        if ($request->role !== 'admin_aset' && $request->bidang === 'Pengelola Aset') {
            throw \Illuminate\Validation\ValidationException::withMessages(['bidang' => 'Bidang Pengelola Aset hanya untuk role Admin Pengelola Aset.']);
        }
        $request->validate([
            'name'     => 'required|string|max:255',
            'nip_nik'  => 'nullable|string|max:50',
            'username' => 'required|string|unique:users,username',
            'whatsapp' => 'required|numeric',
            'role'     => 'required|in:superadmin,admin_bidang,admin_aset,pengelola_magang,resepsionis,pengelola_keuangan,manajemen_mutu,kasubag_pppk_pw,pengajar,participant,mitra,penandatangan',
            'bidang'   => ['required_if:role,admin_bidang,pengelola_keuangan', 'nullable', Rule::in(array_merge(self::$listBidang, ['Pengelola Aset']))],
            'password' => 'required|min:6',
        ]);

        $userType = match ($request->role) {
            'participant' => 'peserta',
            'pengajar' => 'narasumber',
            'mitra' => 'mitra',
            default => null,
        };

        User::create([
            'name'     => $request->name,
            'user_type' => $userType,
            'user_type_status' => 'approved',
            'nip_nik'  => $request->nip_nik,
            'username' => $request->username,
            'whatsapp' => $request->whatsapp,
            'role'     => $request->role,
            'bidang'   => match ($request->role) { 'admin_aset' => 'Pengelola Aset', 'admin_bidang', 'pengelola_keuangan' => $request->bidang, default => null },
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'User berhasil ditambahkan.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }
        if ($user->role !== 'participant') {
            return back()->with('error', 'Penghapusan menyeluruh hanya tersedia untuk akun peserta. Ubah atau pindahkan tanggung jawab akun administratif terlebih dahulu.');
        }

        $storagePaths = collect([$user->profile_photo])->filter();

        DB::transaction(function () use ($user, &$storagePaths) {
            $participantRows = DB::table('participants')->where('user_id', $user->id)->get();
            $participantIds = $participantRows->pluck('id');
            $participantFileIds = $participantRows->flatMap(fn ($row) => [
                $row->biodata_file_id ?? null,
                $row->surat_tugas_file_id ?? null,
                $row->pas_foto_file_id ?? null,
            ])->filter()->unique();

            if ($participantIds->isNotEmpty() && Schema::hasTable('participant_certificates')) {
                $storagePaths = $storagePaths->merge(
                    DB::table('participant_certificates')->whereIn('participant_id', $participantIds)
                        ->get(['generated_file_path', 'final_file_path'])
                        ->flatMap(fn ($row) => [$row->generated_file_path, $row->final_file_path])->filter()
                );
            }

            // Beberapa tabel lama memakai RESTRICT, bukan CASCADE. Bersihkan seluruh
            // hasil evaluasi dan profil alumni sebelum relasi participants terhapus.
            if ($participantIds->isNotEmpty()) {
                foreach (['evaluation_results_l1', 'evaluation_results_l34', 'alumni_profiles'] as $participantTable) {
                    if (Schema::hasTable($participantTable)) {
                        DB::table($participantTable)->whereIn('participant_id', $participantIds)->delete();
                    }
                }
            }

            $ownedFiles = \App\Models\File::with('versions')
                ->where('user_id', $user->id)
                ->orWhereIn('id', $participantFileIds)
                ->get();
            foreach ($ownedFiles as $file) {
                $storagePaths->push($file->file_path);
                $storagePaths = $storagePaths->merge($file->versions->pluck('file_path'));
                $file->delete();
            }

            $ownedFolders = \App\Models\Folder::where('user_id', $user->id)->get(['id', 'parent_id']);
            if ($ownedFolders->isNotEmpty()) {
                $ownedIds = $ownedFolders->pluck('id')->all();
                $allFolderIds = $ownedIds;
                $frontier = $ownedIds;
                while ($frontier !== []) {
                    $frontier = \App\Models\Folder::whereIn('parent_id', $frontier)->pluck('id')->all();
                    $allFolderIds = array_values(array_unique(array_merge($allFolderIds, $frontier)));
                }

                $folderFiles = \App\Models\File::with('versions')->whereIn('folder_id', $allFolderIds)->get();
                foreach ($folderFiles as $file) {
                    $storagePaths->push($file->file_path);
                    $storagePaths = $storagePaths->merge($file->versions->pluck('file_path'));
                }

                $topOwnedIds = $ownedFolders
                    ->filter(fn ($folder) => ! in_array((int) $folder->parent_id, $ownedIds, true))
                    ->pluck('id');
                \App\Models\Folder::whereIn('id', $topOwnedIds)->delete();
            }

            if (Schema::hasTable('daily_report_assignments')) DB::table('daily_report_assignments')->where('user_id', $user->id)->delete();
            if (Schema::hasTable('internship_participants')) {
                $internships = DB::table('internship_participants')->where('user_id', $user->id)->get();
                $storagePaths = $storagePaths->merge($internships->flatMap(fn ($row) => [
                    $row->certificate_generated_file_path ?? null,
                    $row->certificate_final_file_path ?? null,
                ])->filter());
                DB::table('internship_participants')->where('user_id', $user->id)->delete();
            }
            if (Schema::hasTable('asset_loan_requests')) {
                $loans = DB::table('asset_loan_requests')->where('submitted_by', $user->id)->get();
                $storagePaths = $storagePaths->merge($loans->pluck('document_path')->filter());
                DB::table('asset_loan_requests')->where('submitted_by', $user->id)->delete();
            }
            if (Schema::hasTable('ai_generations')) DB::table('ai_generations')->where('user_id', $user->id)->delete();
            if (Schema::hasTable('electronic_signature_attempts')) DB::table('electronic_signature_attempts')->where('user_id', $user->id)->delete();
            if (Schema::hasTable('electronic_signature_actors')) DB::table('electronic_signature_actors')->where('user_id', $user->id)->delete();
            if (Schema::hasTable('electronic_signature_requests')) DB::table('electronic_signature_requests')->where('created_by', $user->id)->delete();
            if (Schema::hasTable('activity_logs')) DB::table('activity_logs')->where('user_id', $user->id)->delete();

            $user->delete();
        });

        Storage::disk('public')->delete($storagePaths->filter()->unique()->values()->all());
        return redirect()->back()->with('success', 'Akun peserta beserta seluruh aktivitas, dokumen, dan riwayat terkait berhasil dihapus.');
    }

    public function approveUserType(User $user)
    {
        abort_unless($user->user_type_status === 'pending' && in_array($user->user_type, ['narasumber', 'mitra'], true), 422, 'Tidak ada pengajuan jenis akun yang menunggu persetujuan.');

        $user->update([
            'role' => $user->user_type === 'narasumber' ? 'pengajar' : 'mitra',
            'user_type_status' => 'approved',
            'bidang' => null,
        ]);

        return back()->with('success', 'Pengajuan sebagai '.ucfirst($user->user_type).' untuk '.$user->name.' berhasil disetujui.');
    }
    public function resetPassword(User $user)
    {
        $user->update(['password' => Hash::make('password123')]);
        return redirect()->back()->with('success', "Password direset ke: password123");
    }

    public function update(Request $request, User $user)
    {
        if ($request->role !== 'admin_aset' && $request->bidang === 'Pengelola Aset') {
            throw \Illuminate\Validation\ValidationException::withMessages(['bidang' => 'Bidang Pengelola Aset hanya untuk role Admin Pengelola Aset.']);
        }
        $request->validate([
            'name'     => 'required|string|max:255',
            // Update username ditambahkan, dengan validasi ignore ID agar tidak error "sudah dipakai" oleh dirinya sendiri
            'username' => 'required|string|unique:users,username,' . $user->id,
            'nip_nik'  => 'nullable|string|max:50',
            'role'     => 'required|in:superadmin,admin_bidang,admin_aset,pengelola_magang,resepsionis,pengelola_keuangan,manajemen_mutu,kasubag_pppk_pw,pengajar,participant,mitra,penandatangan',
            'whatsapp' => 'required|numeric',
            'bidang'   => ['required_if:role,admin_bidang,pengelola_keuangan', 'nullable', Rule::in(array_merge(self::$listBidang, ['Pengelola Aset']))],
        ]);

        $userType = match ($request->role) {
            'participant' => 'peserta',
            'pengajar' => 'narasumber',
            'mitra' => 'mitra',
            default => null,
        };
        $preservePendingRequest = $user->user_type_status === 'pending'
            && in_array($user->user_type, ['narasumber', 'mitra'], true)
            && $user->user_type === $userType;
        $effectiveRole = $preservePendingRequest ? 'participant' : $request->role;

        $user->update([
            'name'     => $request->name,
            'user_type' => $userType,
            'user_type_status' => $preservePendingRequest ? 'pending' : 'approved',
            'username' => $request->username,
            'nip_nik'  => $request->nip_nik,
            'role'     => $effectiveRole,
            'whatsapp' => $request->whatsapp,
            'bidang'   => match ($effectiveRole) { 'admin_aset' => 'Pengelola Aset', 'admin_bidang', 'pengelola_keuangan' => $request->bidang, default => null },
        ]);

        return redirect()->back()->with('success', 'Data user ' . $user->name . ' berhasil diperbarui.');
    }
}
