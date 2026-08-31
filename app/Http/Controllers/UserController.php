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
            'bidang' => User::whereNotIn('role', $adminRoles)
                ->whereNotNull('bidang')->where('bidang', '<>', '')->count(),
            'external' => User::whereNotIn('role', $adminRoles)
                ->where(function ($query) {
                    $query->whereNull('bidang')->orWhere('bidang', '');
                })->count(),
        ];

        $users = User::latest()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%")
                        ->orWhere('username', 'LIKE', "%$search%")
                        ->orWhere('nip_nik', 'LIKE', "%$search%")
                        ->orWhere('bidang', 'LIKE', "%$search%")
                        ->orWhere('role', 'LIKE', "%$search%")
                        ->orWhere('instansi', 'LIKE', "%$search%")
                        ->orWhere('jabatan', 'LIKE', "%$search%");
                });
            })
            ->when($category === 'admin', fn ($query) => $query->whereIn('role', $adminRoles))
            ->when($category === 'bidang', function ($query) use ($adminRoles) {
                $query->whereNotIn('role', $adminRoles)
                    ->whereNotNull('bidang')->where('bidang', '<>', '');
            })
            ->when($category === 'external', function ($query) use ($adminRoles) {
                $query->whereNotIn('role', $adminRoles)
                    ->where(function ($scope) {
                        $scope->whereNull('bidang')->orWhere('bidang', '');
                    });
            })
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
            'role'     => 'required|in:superadmin,admin_bidang,admin_aset,pengajar,participant',
            'bidang'   => ['required_if:role,admin_bidang', 'nullable', Rule::in(array_merge(self::$listBidang, ['Pengelola Aset']))],
            'password' => 'required|min:6',
        ]);

        User::create([
            'name'     => $request->name,
            'nip_nik'  => $request->nip_nik,
            'username' => $request->username,
            'whatsapp' => $request->whatsapp,
            'role'     => $request->role,
            'bidang'   => $request->role === 'admin_aset' ? 'Pengelola Aset' : $request->bidang,
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
            'role'     => 'required|in:superadmin,admin_bidang,admin_aset,pengajar,participant',
            'whatsapp' => 'required|numeric',
            'bidang'   => ['required_if:role,admin_bidang', 'nullable', Rule::in(array_merge(self::$listBidang, ['Pengelola Aset']))],
        ]);

        $user->update([
            'name'     => $request->name,
            'username' => $request->username, // Pastikan input username ada di form modal edit Anda
            'nip_nik'  => $request->nip_nik,
            'role'     => $request->role,
            'whatsapp' => $request->whatsapp,
            'bidang'   => $request->role === 'admin_aset' ? 'Pengelola Aset' : $request->bidang,
        ]);

        return redirect()->back()->with('success', 'Data user ' . $user->name . ' berhasil diperbarui.');
    }
}
