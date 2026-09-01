<?php

namespace App\Http\Controllers;

use App\Models\Pengajar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajarSetupController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()?->isNarasumber(), 403, 'Halaman ini hanya untuk akun narasumber.');

        if (Auth::user()->pengajar) {
            return redirect()->route('pengajar.index')->with('info', 'Data administrasi narasumber sudah lengkap.');
        }

        return view('pengajar.setup');
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()?->isNarasumber(), 403, 'Halaman ini hanya untuk akun narasumber.');

        $user = Auth::user();
        $data = $request->validate([
            'npwp' => 'required|string|max:50',
            'nomor_rekening' => ['required', 'string', 'max:50', 'regex:/^[0-9]+$/'],
            'nama_bank' => 'required|string|max:100',
            'nama_rekening' => 'required|string|max:255',
        ], [
            'npwp.required' => 'Nomor NPWP wajib diisi.',
            'nomor_rekening.regex' => 'Nomor rekening hanya boleh berisi angka.',
        ]);

        Pengajar::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($data, ['instansi' => $user->instansi])
        );

        return redirect()->route('pengajar.index')
            ->with('success', 'Data administrasi narasumber berhasil disimpan.');
    }
}
