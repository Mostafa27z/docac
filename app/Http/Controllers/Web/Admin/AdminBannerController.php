<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\BunnyStorageService;
use Illuminate\Http\Request;

class AdminBannerController extends Controller
{
    protected BunnyStorageService $bunnyStorage;

    public function __construct(BunnyStorageService $bunnyStorage)
    {
        $this->bunnyStorage = $bunnyStorage;
    }

    public function index()
    {
        $banners = Banner::orderBy('sort_order', 'asc')->latest()->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_file' => 'required|image|max:10240',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $file = $request->file('image_file') ?? $request->file('image');
        if (!$file) {
            return redirect()->back()->withErrors(['image_file' => 'يرجى اختيار صورة البنر.'])->with('error', 'فشل الحفظ: يرجى رفع صورة البنر.');
        }

        $imagePath = $this->bunnyStorage->uploadFile($file, 'banners');

        Banner::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->back()->with('success', 'تم إضافة البنر الإعلاني بنجاح.');
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|max:10240',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $updateData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : false,
            'sort_order' => $validated['sort_order'] ?? 0,
        ];

        $file = $request->file('image_file') ?? $request->file('image');
        if ($file) {
            $imagePath = $this->bunnyStorage->uploadFile($file, 'banners');
            if ($imagePath) {
                $updateData['image'] = $imagePath;
            }
        }

        $banner->update($updateData);

        return redirect()->back()->with('success', 'تم تحديث بيانات البنر بنجاح.');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        return redirect()->back()->with('success', 'تم حذف البنر بنجاح.');
    }

    public function toggleActive(Banner $banner)
    {
        $banner->update([
            'is_active' => !$banner->is_active
        ]);

        $statusText = $banner->is_active ? 'تفعيل' : 'إيقاف';
        return redirect()->back()->with('success', "تم {$statusText} البنر بنجاح.");
    }
}
