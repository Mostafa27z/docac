<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Services\BunnyStorageService;
use Illuminate\Http\Request;

class AdminAdController extends Controller
{
    protected BunnyStorageService $bunnyStorage;

    public function __construct(BunnyStorageService $bunnyStorage)
    {
        $this->bunnyStorage = $bunnyStorage;
    }

    public function index()
    {
        $ads = Ad::orderBy('sort_order', 'asc')->latest()->get();
        return view('admin.ads.index', compact('ads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_file' => 'required|image|max:10240',
            'link' => 'nullable|url|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $file = $request->file('image_file') ?? $request->file('image');
        if (!$file) {
            return redirect()->back()->withErrors(['image_file' => 'يرجى اختيار صورة الإعلان.'])->with('error', 'فشل الحفظ: يرجى رفع صورة الإعلان.');
        }

        $imagePath = $this->bunnyStorage->uploadFile($file, 'ads');

        Ad::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'link' => $validated['link'] ?? null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->back()->with('success', 'تم إضافة الإعلان بنجاح.');
    }

    public function update(Request $request, Ad $ad)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|max:10240',
            'link' => 'nullable|url|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $updateData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'link' => $validated['link'] ?? null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : false,
            'sort_order' => $validated['sort_order'] ?? 0,
        ];

        $file = $request->file('image_file') ?? $request->file('image');
        if ($file) {
            $imagePath = $this->bunnyStorage->uploadFile($file, 'ads');
            if ($imagePath) {
                $updateData['image'] = $imagePath;
            }
        }

        $ad->update($updateData);

        return redirect()->back()->with('success', 'تم تحديث بيانات الإعلان بنجاح.');
    }

    public function destroy(Ad $ad)
    {
        $ad->delete();
        return redirect()->back()->with('success', 'تم حذف الإعلان بنجاح.');
    }

    public function toggleActive(Ad $ad)
    {
        $ad->update([
            'is_active' => !$ad->is_active
        ]);

        $statusText = $ad->is_active ? 'تفعيل' : 'إيقاف';
        return redirect()->back()->with('success', "تم {$statusText} الإعلان بنجاح.");
    }
}
