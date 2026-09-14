<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureImportedParticipantProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if ($user && $user->role === 'participant' && ($user->must_complete_profile || $user->must_change_password)) {
            if (!$request->routeIs('participant.profile.complete', 'participant.profile.store', 'logout')) {
                return redirect()->route('participant.profile.complete')
                    ->with('warning', 'Akun Anda dibuat melalui import peserta. Ganti password awal dan lengkapi profil untuk melanjutkan.');
            }
        }
        return $next($request);
    }
}