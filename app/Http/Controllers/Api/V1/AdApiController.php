<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;

class AdApiController extends Controller
{
    public function index(Request $request)
    {
        $ads = Ad::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->latest()
            ->get()
            ->map(function ($ad) {
                return [
                    'id' => $ad->id,
                    'title' => $ad->title,
                    'description' => $ad->description,
                    'image' => $ad->image_url,
                    'link' => $ad->link,
                    'sort_order' => $ad->sort_order,
                    'created_at' => $ad->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Ads retrieved successfully.',
            'data' => $ads
        ]);
    }
}
