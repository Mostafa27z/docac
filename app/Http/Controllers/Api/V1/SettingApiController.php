<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingApiController extends Controller
{
    /**
     * Get application settings / feature flags.
     */
    public function index(Request $request): JsonResponse
    {
        $settings = AppSetting::getSettings();

        $data = [
            'enable' => (bool) $settings->enable,
        ];

        // Automatically include any additional fillable settings added in future migrations
        foreach ($settings->getFillable() as $attribute) {
            if ($attribute !== 'enable') {
                $data[$attribute] = $settings->{$attribute};
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings retrieved successfully.',
            'data' => $data,
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Toggle the 'enable' setting flag.
     * Public endpoint accessible directly via browser (GET) or via API (POST/GET).
     */
    public function toggleEnable(Request $request): JsonResponse
    {
        $settings = AppSetting::getSettings();
        $settings->enable = !$settings->enable;
        $settings->save();

        $stateText = $settings->enable ? 'true (enabled)' : 'false (disabled)';

        return response()->json([
            'success' => true,
            'message' => "Setting 'enable' successfully toggled to {$stateText}.",
            'data' => [
                'enable' => (bool) $settings->enable,
            ]
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
