<?php

namespace Tests\Feature\Admin;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $instructor;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->instructor = User::create([
            'name' => 'Instructor User',
            'email' => 'instructor@test.com',
            'password' => bcrypt('password123'),
            'role' => 'instructor',
            'status' => 'active',
        ]);

        $this->student = User::create([
            'name' => 'Student User',
            'email' => 'student@test.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'status' => 'active',
        ]);
    }

    public function test_guests_cannot_access_or_toggle_admin_settings(): void
    {
        $this->get('/admin/settings')->assertRedirect('/login');
        $this->post('/admin/settings/app-toggle')->assertRedirect('/login');
    }

    public function test_non_admins_cannot_access_or_toggle_admin_settings(): void
    {
        $this->actingAs($this->student)->get('/admin/settings')->assertStatus(403);
        $this->actingAs($this->student)->post('/admin/settings/app-toggle')->assertStatus(403);

        $this->actingAs($this->instructor)->get('/admin/settings')->assertStatus(403);
        $this->actingAs($this->instructor)->post('/admin/settings/app-toggle')->assertStatus(403);
    }

    public function test_admin_can_view_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings');

        $response->assertStatus(200);
        $response->assertSee('إعدادات التطبيق وميزات الـ API');
        $response->assertSee('Enable = False');
    }

    public function test_admin_can_toggle_enable_setting_via_dashboard(): void
    {
        $this->assertFalse(AppSetting::getSettings()->enable);

        // 1. Toggle ON
        $response = $this->actingAs($this->admin)->post('/admin/settings/app-toggle');
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue(AppSetting::getSettings()->fresh()->enable);

        // 2. Toggle OFF
        $response2 = $this->actingAs($this->admin)->post('/admin/settings/app-toggle');
        $response2->assertRedirect();
        $response2->assertSessionHas('success');
        $this->assertFalse(AppSetting::getSettings()->fresh()->enable);
    }
}

