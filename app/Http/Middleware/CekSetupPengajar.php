<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CekSetupPengajar
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'pengajar') {
            // Jika data di tabel pengajars belum ada, arahkan ke halaman setup
            if (!Auth::user()->pengajar) {
                // Cegah redirect loop (jangan redirect jika sedang di halaman setup atau logout)
                if (!$request->is('pengajar/setup-profil') && !$request->is('logout')) {
                    return redirect()->route('pengajar.setup')->with('warning', 'Selamat datang! Silakan perbarui password dan lengkapi profil Anda untuk melanjutkan.');
                }
            }
        }

        return $next($request);
    }
}