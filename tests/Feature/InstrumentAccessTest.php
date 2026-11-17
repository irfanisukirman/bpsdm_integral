<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstrumentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_field_admin_cannot_view_or_modify_instrument_banks(): void
    {
        $user = User::factory()->create(['role' => 'admin_bidang']);
        $this->actingAs($user);
        foreach (['questions.index', 'questions.export', 'questions.template', 'indicators.index', 'indicators.export', 'indicators.template'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
        foreach (['questions.store', 'questions.import', 'indicators.store', 'indicators.import'] as $route) {
            $this->post(route($route), [])->assertForbidden();
        }
        $sidebar = view('layouts.sidebar')->render();
        $this->assertStringNotContainsString('Kelola Instrumen', $sidebar);
        $this->assertStringNotContainsString('Bank Instrumen', $sidebar);
    }

    public function test_superadmin_can_view_instrument_banks_and_menu(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($user)->get(route('questions.index'))->assertOk()->assertSeeInOrder(['Pengaturan Sistem', 'Bank Instrumen'])->assertDontSee('Kelola Instrumen');
        $this->get(route('indicators.index'))->assertOk()->assertSee('Bank Instrumen');
    }
}
