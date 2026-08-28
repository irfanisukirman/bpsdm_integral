<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'              => 'Super Administrator',
            'username'          => 'superadmin', // Gunakan ini untuk login
            'nip_nik'           => '19450817000000', // Contoh NIP
            'whatsapp'          => '6281234567890',
            'role'              => 'superadmin', // Role wajib 'superadmin'
            'bidang'            => 'Sekretariat', // Bidang utama
            'gender'            => 'Laki-Laki',
            'status_kepegawaian'=> 'PNS',
            'password'          => Hash::make('admin123'), // Password Anda
        ]);
    }
}