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
        'Bidang Pengembangan Kompetensi Manajerial'
    ];

    public function index(Request $request)
    {
        $search = $request->query('search');

        // Ambil data user dengan filter pencarian yang diperluas
        $users = User::latest()
            ->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('username', 'LIKE', "%$search%")
                    ->orWhere('nip_nik', 'LIKE', "%$search%")
                    ->orWhere('bidang', 'LIKE', "%$search%")
                    ->orWhere('role', 'LIKE', "%$search%")
                    // Tambahan pencarian untuk mempermudah mencari Participant
                    ->orWhere('instansi', 'LIKE', "%$search%")
                    ->orWhere('jabatan', 'LIKE', "%$search%");
                });
            })
            ->get();

        $listBidang = self::$listBidang;

        return view('users.index', compact('users', 'listBidang', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'nip_nik'  => 'nullable|string|max:50',
            'username' => 'required|string|unique:users,username',
            'whatsapp' => 'required|numeric',
            'role'     => 'required|in:superadmin,admin_bidang,pengajar,participant',
            'bidang'   => ['nullable', Rule::in(self::$listBidang)],
            'password' => 'required|min:6',
        ]);

        User::create([
            'name'     => $request->name,
            'nip_nik'  => $request->nip_nik,
            'username' => $request->username,
            'whatsapp' => $request->whatsapp,
            'role'     => $request->role,
            'bidang'   => $request->bidang,
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
        $request->validate([
            'name'     => 'required|string|max:255',
            // Update username ditambahkan, dengan validasi ignore ID agar tidak error "sudah dipakai" oleh dirinya sendiri
            'username' => 'required|string|unique:users,username,' . $user->id,
            'nip_nik'  => 'nullable|string|max:50',
            'role'     => 'required|in:superadmin,admin_bidang,pengajar,participant',
            'whatsapp' => 'required|numeric',
            'bidang'   => ['nullable', Rule::in(self::$listBidang)],
        ]);

        $user->update([
            'name'     => $request->name,
            'username' => $request->username, // Pastikan input username ada di form modal edit Anda
            'nip_nik'  => $request->nip_nik,
            'role'     => $request->role,
            'whatsapp' => $request->whatsapp,
            'bidang'   => $request->bidang,
        ]);

        return redirect()->back()->with('success', 'Data user ' . $user->name . ' berhasil diperbarui.');
    }
}