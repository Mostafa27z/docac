<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseActivationCode;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'students_count' => User::where('role', 'student')->count(),
            'instructors_count' => User::where('role', 'instructor')->count(),
            'courses_count' => Course::count(),
            'codes_generated' => CourseActivationCode::count(),
            'codes_used' => CourseActivationCode::where('is_used', true)->count(),
        ];

        $instructors = User::where('role', 'instructor')->latest()->get();
        $courses = Course::with('instructor')->latest()->get();

        return view('admin.dashboard', compact('stats', 'instructors', 'courses'));
    }

    public function activationCodesIndex(Request $request)
    {
        $courses = Course::latest()->get();
        
        $query = CourseActivationCode::with(['course', 'student', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('course', function($sub) use ($search) {
                      $sub->where('title', 'like', "%{$search}%");
                  });
            });
        }

        $activationCodes = $query->latest()->paginate(20);

        return view('admin.activation_codes', compact('courses', 'activationCodes'));
    }

    public function teachersList()
    {
        $instructors = User::where('role', 'instructor')->latest()->get();
        return view('admin.teachers', compact('instructors'));
    }

    public function createInstructor(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'instructor',
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Instructor account created successfully.');
    }

    public function generateCodes(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $cleanTitle = substr(preg_replace('/[^A-Za-z0-9]/', '', $course->title), 0, 4);

        for ($i = 0; $i < $validated['quantity']; $i++) {
            $code = strtoupper($cleanTitle . '-' . Str::random(4) . '-' . Str::random(4));
            
            CourseActivationCode::create([
                'course_id' => $course->id,
                'code' => $code,
                'is_used' => false,
                'created_by_user_id' => auth()->id(),
            ]);
        }

        return redirect()->back()->with('success', "Successfully generated {$validated['quantity']} activation codes.");
    }

    public function studentsList(Request $request)
    {
        $query = User::where('role', 'student');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(15);

        return view('admin.students', compact('students'));
    }

    public function resetDevice(User $user)
    {
        if ($user->role !== 'student') {
            abort(403);
        }

        $user->update([
            'active_device_id' => null
        ]);

        return redirect()->back()->with('success', "تم إعادة تعيين رمز الجهاز (Device ID) للطالب {$user->name} بنجاح.");
    }

    public function toggleStatus(User $user)
    {
        if ($user->role !== 'student') {
            abort(403);
        }

        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update([
            'status' => $newStatus
        ]);

        $statusText = $newStatus === 'active' ? 'تنشيط' : 'إيقاف';
        return redirect()->back()->with('success', "تم {$statusText} حساب الطالب {$user->name} بنجاح.");
    }

    public function settingsIndex()
    {
        $settings = [
            'facebook_url' => Setting::getValue('facebook_url', ''),
            'youtube_url' => Setting::getValue('youtube_url', ''),
            'whatsapp_number' => Setting::getValue('whatsapp_number', ''),
            'telegram_number' => Setting::getValue('telegram_number', ''),
            'telegram_username' => Setting::getValue('telegram_username', ''),
        ];

        return view('admin.settings', compact('settings'));
    }

    public function settingsUpdate(Request $request)
    {
        $validated = $request->validate([
            'facebook_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'whatsapp_number' => 'nullable|string|max:20',
            'telegram_number' => 'nullable|string|max:20',
            'telegram_username' => 'nullable|string|max:100',
        ]);

        foreach ($validated as $key => $value) {
            Setting::setValue($key, $value);
        }

        return redirect()->back()->with('success', 'تم تحديث بيانات الاتصال بنجاح.');
    }
}
