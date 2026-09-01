<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $adminRoles = ['superadmin', 'admin_bidang', 'admin_aset'];

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
            'role'     => 'required|in:superadmin,admin_bidang,admin_aset,pengajar,participant,mitra',
            'bidang'   => ['required_if:role,admin_bidang', 'nullable', Rule::in(array_merge(self::$listBidang, ['Pengelola Aset']))],
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
            'bidang'   => match ($request->role) { 'admin_aset' => 'Pengelola Aset', 'admin_bidang' => $request->bidang, default => null },
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'User berhasil ditambahkan.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }
        $user->delete();
        return redirect()->back()->with('success', 'Akun berhasil dihapus.');
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
            'role'     => 'required|in:superadmin,admin_bidang,admin_aset,pengajar,participant,mitra',
            'whatsapp' => 'required|numeric',
            'bidang'   => ['required_if:role,admin_bidang', 'nullable', Rule::in(array_merge(self::$listBidang, ['Pengelola Aset']))],
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
            'bidang'   => match ($effectiveRole) { 'admin_aset' => 'Pengelola Aset', 'admin_bidang' => $request->bidang, default => null },
        ]);

        return redirect()->back()->with('success', 'Data user ' . $user->name . ' berhasil diperbarui.');
    }
}
