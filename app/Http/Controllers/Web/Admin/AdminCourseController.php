<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Services\BunnyStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCourseController extends Controller
{
    protected BunnyStorageService $bunnyStorage;

    public function __construct(BunnyStorageService $bunnyStorage)
    {
        $this->bunnyStorage = $bunnyStorage;
    }

    public function index()
    {
        $courses = Course::with(['instructor', 'category', 'subcategory'])->latest()->get();
        $instructors = User::where('role', 'instructor')->where('status', 'active')->get();
        $categories = Category::with('subcategories')->get();

        return view('admin.courses.index', compact('courses', 'instructors', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:recorded,live,mixed',
            'price' => 'required|numeric|min:0',
            'instructor_id' => 'required|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'thumbnail_file' => 'nullable|image|max:2048',
        ]);

        $thumbnail = null;
        if ($request->hasFile('thumbnail_file')) {
            $thumbnail = $this->bunnyStorage->uploadFile(
                $request->file('thumbnail_file'),
                'courses/thumbnails'
            );
        }

        $course = Course::create([
            'instructor_id' => $validated['instructor_id'],
            'category_id' => $validated['category_id'] ?? null,
            'subcategory_id' => $validated['subcategory_id'] ?? null,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::random(4),
            'description' => $validated['description'],
            'type' => $validated['type'],
            'price' => $validated['price'],
            'thumbnail' => $thumbnail,
            'status' => 'draft',
        ]);

        return redirect()->route('instructor.courses.manage', $course->id)->with('success', 'تم إنشاء الكورس وتعيين المحاضر بنجاح، يمكنك الآن إضافة المنهج والدروس.');
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:recorded,live,mixed',
            'price' => 'required|numeric|min:0',
            'instructor_id' => 'required|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'thumbnail_file' => 'nullable|image|max:2048',
        ]);

        $updateData = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'price' => $validated['price'],
            'instructor_id' => $validated['instructor_id'],
            'category_id' => $validated['category_id'] ?? null,
            'subcategory_id' => $validated['subcategory_id'] ?? null,
        ];

        if ($request->hasFile('thumbnail_file')) {
            $thumbnail = $this->bunnyStorage->uploadFile(
                $request->file('thumbnail_file'),
                'courses/thumbnails'
            );
            if ($thumbnail) {
                $updateData['thumbnail'] = $thumbnail;
            }
        }

        $course->update($updateData);

        return redirect()->back()->with('success', 'تم تحديث بيانات الكورس والمحاضر والتصنيفات بنجاح.');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'تم حذف الكورس بنجاح.');
    }
}
