<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ContactApiController extends Controller
{
    public function index()
    {
        $contacts = [
            'facebook_url' => Setting::getValue('facebook_url', ''),
            'youtube_url' => Setting::getValue('youtube_url', ''),
            'whatsapp_number' => Setting::getValue('whatsapp_number', ''),
            'telegram_number' => Setting::getValue('telegram_number', ''),
            'telegram_username' => Setting::getValue('telegram_username', ''),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Contact details retrieved successfully.',
            'data' => $contacts
        ]);
    }
}
