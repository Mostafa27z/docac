<?php

namespace Tests\Feature\Api\V1;

use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_retrieve_default_settings(): void
    {
        $response = $this->getJson('/api/v1/settings');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Settings retrieved successfully.',
                'data' => [
                    'enable' => false,
                ]
            ]);
    }

    public function test_can_retrieve_updated_enable_setting(): void
    {
        $setting = AppSetting::getSettings();
        $setting->update(['enable' => true]);

        $response = $this->getJson('/api/v1/settings');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'enable' => true,
                ]
            ]);
    }

    public function test_v2_and_app_settings_endpoints_work(): void
    {
        $responseV2 = $this->getJson('/api/v2/settings');
        $responseV2->assertStatus(200);

        $responseAlias = $this->getJson('/api/v1/app-settings');
        $responseAlias->assertStatus(200);
    }

    public function test_browser_public_toggle_endpoint_switches_value(): void
    {
        // 1. Initial state is false
        $this->assertFalse(AppSetting::getSettings()->enable);

        // 2. Hit toggle in browser (GET request)
        $response1 = $this->get('/settings/toggle');
        $response1->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'enable' => true,
                ]
            ]);
        $this->assertTrue(AppSetting::getSettings()->fresh()->enable);

        // 3. Hit toggle again (GET request)
        $response2 = $this->get('/settings/toggle');
        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'enable' => false,
                ]
            ]);
        $this->assertFalse(AppSetting::getSettings()->fresh()->enable);
    }

    public function test_api_settings_toggle_route(): void
    {
        $response = $this->get('/api/v1/settings/toggle');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'enable' => true,
                ]
            ]);
    }
}
