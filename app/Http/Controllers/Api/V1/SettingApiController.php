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
        ]);
    }
}

