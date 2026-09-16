<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketService;

class TicketServiceSeeder extends Seeder
{
  public function run(): void
  {
    $services = [
      ['name' => 'Integral', 'slug' => 'integral', 'sort_order' => 1],
      ['name' => 'Jabar Corpu Talent', 'slug' => 'jabar-corpu-talent', 'sort_order' => 2],
    ];
    foreach ($services as $s) {
      TicketService::updateOrCreate(['slug' => $s['slug']], $s);
    }
  }
}
