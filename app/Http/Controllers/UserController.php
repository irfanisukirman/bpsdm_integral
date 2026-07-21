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

    public function index()
    {
        // Menampilkan SEMUA user (Superadmin & Admin Bidang)
        $users = User::latest()->get();
        $listBidang = self::$listBidang;
        return view('users.index', compact('users', 'listBidang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'whatsapp' => 'required|numeric',
            'role' => 'required|in:superadmin,admin_bidang',
            'bidang' => ['required', Rule::in(self::$listBidang)],
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'whatsapp' => $request->whatsapp,
            'role' => $request->role,
            'bidang' => $request->bidang,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'User berhasil ditambahkan.');
    }

    public function destroy(User $user)
    {
        // Proteksi agar tidak menghapus diri sendiri
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
}