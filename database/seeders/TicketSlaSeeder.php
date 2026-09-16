<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketSla;

class TicketSlaSeeder extends Seeder
{
  public function run(): void
  {
    TicketSla::updateOrCreate(
      ['service_id' => null, 'category_id' => null, 'bidang_id' => null],
      ['respond_hours' => 48, 'resolve_hours' => 72, 'working_hours_start' => '08:00', 'working_hours_end' => '17:00', 'is_active' => true]
    );
  }
}
