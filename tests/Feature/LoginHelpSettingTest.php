<?php

namespace Tests\Feature;

use App\Models\LoginHelpSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginHelpSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_displays_password_toggle_and_configured_help(): void
    {
        LoginHelpSetting::current()->update([
            'title' => 'Bantuan Akun Internal',
            'contacts' => [['name' => 'Admin Test', 'phone' => '628123456789']],
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('toggleLoginPassword')
            ->assertSee('Bantuan Akun Internal')
            ->assertSee('Admin Test');
    }

    public function test_only_superadmin_can_manage_login_help(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $admin = User::factory()->create(['role' => 'admin_bidang']);

        $this->actingAs($superadmin)->get(route('settings.login-help.edit'))->assertOk();
        $this->actingAs($admin)->get(route('settings.login-help.edit'))->assertForbidden();
    }
}
