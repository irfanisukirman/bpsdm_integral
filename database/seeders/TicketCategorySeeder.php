<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketCategory;

class TicketCategorySeeder extends Seeder
{
  public function run(): void
  {
    $categories = [
      ['name' => 'Cara Penggunaan', 'slug' => 'cara-penggunaan', 'sort_order' => 1],
      ['name' => 'Sertifikasi Kompetensi', 'slug' => 'sertifikasi-kompetensi', 'sort_order' => 2],
      ['name' => 'Teknis Umum', 'slug' => 'teknis-umum', 'sort_order' => 3],
      ['name' => 'Teknis Inti', 'slug' => 'teknis-inti', 'sort_order' => 4],
      ['name' => 'Manajerial', 'slug' => 'manajerial', 'sort_order' => 5],
    ];
    foreach ($categories as $c) {
      TicketCategory::updateOrCreate(['slug' => $c['slug']], $c);
    }
  }
}
