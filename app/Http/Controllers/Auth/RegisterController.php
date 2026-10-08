<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\ProfileDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(private readonly ProfileDataService $profileData) {}

    /**
     * Middleware Laravel 11/12 style.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('guest'),
        ];
    }

    /**
     * Halaman pendaftaran publik.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register', [
            'user' => new User,
            'golonganOptions' => \App\Http\Requests\ProfileDataRequest::GOLONGAN,
        ]);
    }

    /**
     * Simpan akun baru beserta data profil lengkapnya.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($request, $data) {
            $user = User::create($request->accountData());

            return $this->profileData->apply($user, $data);
        });

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        if ($user->user_type === 'narasumber') {
            return redirect()->route('pengajar.setup')
                ->with('success', 'Pendaftaran berhasil. Lengkapi data pengajar untuk melanjutkan.');
        }

        if ($user->user_type === 'mitra') {
            return redirect()->route('participant.dashboard')
                ->with('success', 'Pendaftaran berhasil. Pengajuan sebagai Mitra menunggu persetujuan admin.');
        }

        return redirect()->route('participant.dashboard')
            ->with('success', 'Pendaftaran berhasil. Selamat datang di INTEGRAL.');
    }
}