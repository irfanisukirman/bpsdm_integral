<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketBidang;

class TicketBidangSeeder extends Seeder
{
  public function run(): void
  {
    $bidang = [
      'Bidang Pengembangan Kompetensi Teknis Inti',
      'Bidang Pengembangan Kompetensi Teknis Umum',
      'Bidang Pengembangan Kompetensi Manajerial',
      'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan',
      'Sekretariat',
    ];
    foreach ($bidang as $i => $name) {
      TicketBidang::updateOrCreate(['name' => $name], ['is_active' => true]);
    }
  }
}
