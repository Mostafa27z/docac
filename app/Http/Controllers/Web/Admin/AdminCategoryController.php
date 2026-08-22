<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use App\Services\BunnyStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    protected BunnyStorageService $bunnyStorage;

    public function __construct(BunnyStorageService $bunnyStorage)
    {
        $this->bunnyStorage = $bunnyStorage;
    }

    public function index()
    {
        $categories = Category::with(['subcategories'])->withCount('courses')->latest()->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl = $this->bunnyStorage->uploadFile(
                $request->file('image'),
                'categories'
            );
        }

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
            'image_url' => $imageUrl,
        ]);

        return redirect()->back()->with('success', 'تم إنشاء التصنيف الرئيسي بنجاح.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
        ];

        if ($request->hasFile('image')) {
            $imageUrl = $this->bunnyStorage->uploadFile(
                $request->file('image'),
                'categories'
            );
            if ($imageUrl) {
                $updateData['image_url'] = $imageUrl;
            }
        }

        $category->update($updateData);

        return redirect()->back()->with('success', 'تم تحديث التصنيف بنجاح.');
    }

    public function destroyCategory(Category $category)
    {
        $category->delete();
        return redirect()->back()->with('success', 'تم حذف التصنيف وجميع تصنيفاته الفرعية بنجاح.');
    }

    public function storeSubcategory(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
        ]);

        Subcategory::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
        ]);

        return redirect()->back()->with('success', 'تم إضافة التصنيف الفرعي بنجاح.');
    }

    public function destroySubcategory(Subcategory $subcategory)
    {
        $subcategory->delete();
        return redirect()->back()->with('success', 'تم حذف التصنيف الفرعي بنجاح.');
    }
}
