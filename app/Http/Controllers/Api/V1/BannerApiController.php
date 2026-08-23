<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerApiController extends Controller
{
    public function index(Request $request)
    {
        $banners = Banner::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->latest()
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'description' => $banner->description,
                    'image' => $banner->image_url,
                    'sort_order' => $banner->sort_order,
                    'created_at' => $banner->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Banners retrieved successfully.',
            'data' => $banners
        ]);
    }
}
