<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Validation\Rule;

final class UserRoles
{
    public const LABELS = [
        'superadmin' => 'Superadmin',
        'admin_bidang' => 'Admin Bidang',
        'admin_aset' => 'Admin Pengelola Aset',
        'pengelola_magang' => 'Pengelola Magang/PKL',
        'resepsionis' => 'Pengelola Buku Tamu',
        'pengelola_keuangan' => 'Pengelola Keuangan',
        'manajemen_mutu' => 'Manajemen Mutu',
        'kasubag_pppk_pw' => 'Pengelola PPPK-PW',
        'evaluator' => 'Evaluator Pasca',
        'penandatangan' => 'Penandatangan',
        'participant' => 'Peserta',
        'pengajar' => 'Narasumber/Pengajar',
        'mitra' => 'Mitra',
        'intern' => 'Peserta Magang/PKL',
    ];

    public const ADMINISTRATIVE = ['superadmin', 'admin_bidang', 'admin_aset', 'pengelola_magang', 'resepsionis', 'pengelola_keuangan', 'manajemen_mutu', 'kasubag_pppk_pw', 'evaluator', 'penandatangan'];

    public const SCOPED = ['admin_bidang', 'pengelola_keuangan'];

    public static function existsRule(string $role)
    {
        $users = $role === 'pengajar' ? User::eligibleTeachers() : User::withRoles([$role]);

        return Rule::exists('users', 'id')->whereIn('id', $users->select('users.id'));
    }

    public static function home(string $role): string
    {
        return match ($role) {
            'participant' => 'participant.dashboard',
            'pengajar' => 'pengajar.index',
            'intern' => 'internships.dashboard',
            'mitra' => 'mitra.dashboard',
            'admin_aset' => 'assets.dashboard',
            'pengelola_magang' => 'internships.index',
            'resepsionis' => 'guest-book.index',
            'pengelola_keuangan' => 'finance.index',
            'manajemen_mutu' => 'quality-management.index',
            'kasubag_pppk_pw' => 'daily-report-management.reports',
            'evaluator' => 'post-evaluation.index',
            'penandatangan' => 'electronic-signatures.index',
            default => 'dashboard',
        };
    }
}
