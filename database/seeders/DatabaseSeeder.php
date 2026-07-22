<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Komentari atau hapus baris ini jika tidak diperlukan
        // \App\Models\User::factory(10)->create();

        // Atau ubah menjadi seperti ini:
        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'username' => 'testuser',
            'role' => 'superadmin',
            'bidang' => 'Bidang Pengembangan Kompetensi Teknis Inti',
            'password' => bcrypt('password'),
        ]);

        // Tambahkan panggil Seeder L34 yang kita buat sebelumnya
        $this->call([
            EvaluasiL34Seeder::class,
        ]);
    }
}
