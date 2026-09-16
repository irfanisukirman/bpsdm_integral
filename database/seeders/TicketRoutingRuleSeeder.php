<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketRoutingRule;
use App\Models\TicketService;
use App\Models\TicketCategory;
use App\Models\TicketBidang;

class TicketRoutingRuleSeeder extends Seeder
{
  public function run(): void
  {
    $integral = TicketService::where('slug', 'integral')->first();
    $jct = TicketService::where('slug', 'jabar-corpu-talent')->first();
    if (!$integral || !$jct) return;
    $rules = [
      ['service' => $integral, 'category' => 'cara-penggunaan', 'bidang' => 'Bidang Pengembangan Kompetensi Teknis Umum'],
      ['service' => $integral, 'category' => 'sertifikasi-kompetensi', 'bidang' => 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan'],
      ['service' => $integral, 'category' => 'teknis-umum', 'bidang' => 'Bidang Pengembangan Kompetensi Teknis Umum'],
      ['service' => $integral, 'category' => 'teknis-inti', 'bidang' => 'Bidang Pengembangan Kompetensi Teknis Inti'],
      ['service' => $integral, 'category' => 'manajerial', 'bidang' => 'Bidang Pengembangan Kompetensi Manajerial'],
      ['service' => $jct, 'category' => 'cara-penggunaan', 'bidang' => 'Bidang Pengembangan Kompetensi Teknis Umum'],
      ['service' => $jct, 'category' => 'sertifikasi-kompetensi', 'bidang' => 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan'],
      ['service' => $jct, 'category' => 'teknis-umum', 'bidang' => 'Bidang Pengembangan Kompetensi Teknis Umum'],
      ['service' => $jct, 'category' => 'teknis-inti', 'bidang' => 'Bidang Pengembangan Kompetensi Teknis Inti'],
      ['service' => $jct, 'category' => 'manajerial', 'bidang' => 'Bidang Pengembangan Kompetensi Manajerial'],
    ];
    foreach ($rules as $r) {
      $cat = TicketCategory::where('slug', $r['category'])->first();
      $bid = TicketBidang::where('name', $r['bidang'])->first();
      if ($cat && $bid) {
        TicketRoutingRule::updateOrCreate(
          ['service_id' => $r['service']->id, 'category_id' => $cat->id],
          ['bidang_id' => $bid->id, 'is_active' => true]
        );
      }
    }
  }
}
