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
        $courses = Course::with(['category', 'subcategory', 'childSubcategory'])->latest()->get();
        
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

    public function createTeacher(Request $request)
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

        return redirect()->back()->with('success', 'تم إنشاء حساب المحاضر بنجاح.');
    }

    public function updateTeacher(Request $request, User $user)
    {
        if ($user->role !== 'instructor') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,suspended',
            'password' => 'nullable|string|min:8',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => $validated['status'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'تم تحديث بيانات المحاضر بنجاح.');
    }

    public function destroyTeacher(User $user)
    {
        if ($user->role !== 'instructor') {
            abort(403);
        }

        $user->delete();

        return redirect()->back()->with('success', 'تم حذف حساب المحاضر بنجاح.');
    }

    public function adminsList()
    {
        $admins = User::where('role', 'admin')->latest()->get();
        return view('admin.admins', compact('admins'));
    }

    public function createAdmin(Request $request)
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
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'تم إنشاء حساب المشرف بنجاح.');
    }

    public function updateAdmin(Request $request, User $user)
    {
        if ($user->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,suspended',
            'password' => 'nullable|string|min:8',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'تم تحديث بيانات المشرف بنجاح.');
    }

    public function destroyAdmin(User $user)
    {
        if ($user->role !== 'admin') {
            abort(403);
        }

        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'لا يمكنك حذف حسابك الشخصي.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'تم حذف حساب المشرف بنجاح.');
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
        $courses = Course::with(['category', 'subcategory', 'childSubcategory'])->latest()->get();

        return view('admin.students', compact('students', 'courses'));
    }

    public function createStudent(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'allow_multiple_devices' => 'nullable|boolean',
        ]);

        $student = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'student',
            'status' => 'active',
            'allow_multiple_devices' => $request->boolean('allow_multiple_devices'),
        ]);

        return redirect()->back()->with('success', "تم إنشاء حساب الطالب {$student->name} بنجاح.");
    }

    public function toggleMultiDevice(User $user)
    {
        if ($user->role !== 'student') {
            abort(403);
        }

        $user->update([
            'allow_multiple_devices' => !$user->allow_multiple_devices
        ]);

        $statusText = $user->allow_multiple_devices ? 'تفعيل السماح بالدخول من أجهزة متعددة' : 'تقييد الحساب بجهاز واحد';
        return redirect()->back()->with('success', "تم {$statusText} للطالب {$user->name} بنجاح.");
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

    public function upgradeStudentToInstructor(User $user)
    {
        if ($user->role !== 'student') {
            return redirect()->back()->with('error', 'هذا الحساب ليس حساب طالب.');
        }

        $user->update([
            'role' => 'instructor',
            'status' => 'active'
        ]);

        return redirect()->back()->with('success', "تم ترقية الطالب {$user->name} ليكون محاضراً بنجاح.");
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

    public function subscribeStudentToCourse(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'course_id' => 'required|exists:courses,id',
        ]);

        $student = User::where('email', $validated['email'])->firstOrFail();

        if ($student->role !== 'student') {
            return redirect()->back()->withErrors(['email' => 'هذا البريد الإلكتروني لا ينتمي لحساب طالب.'])->with('error', 'فشل الاشتراك: الحساب ليس لطالب.');
        }

        $course = Course::findOrFail($validated['course_id']);

        // Check if already enrolled
        $exists = \App\Models\CourseEnrollment::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "الطالب {$student->name} مشترك بالفعل في هذا الكورس.");
        }

        // Create the enrollment
        \App\Models\CourseEnrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'total_price' => $course->price,
            'paid_amount' => $course->price, // Default fully paid by admin manual subscription
            'payment_status' => 'fully_paid',
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        return redirect()->back()->with('success', "تم تسجيل الطالب {$student->name} في الكورس {$course->title} بنجاح.");
    }

    public function bulkSubscribeStudents(Request $request)
    {
        $validated = $request->validate([
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'exists:courses,id',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:users,id',
            'emails' => 'nullable|string',
        ]);

        $courseIds = $validated['course_ids'];
        $courses = Course::whereIn('id', $courseIds)->get();

        // 1. Gather all target students
        $students = collect();

        // From student_ids
        if (!empty($validated['student_ids'])) {
            $studentsById = User::where('role', 'student')->whereIn('id', $validated['student_ids'])->get();
            $students = $students->merge($studentsById);
        }

        // From raw emails text (separated by newlines, commas, semicolons, or spaces)
        $missingEmails = [];
        if (!empty($validated['emails'])) {
            $rawEmails = preg_split('/[\r\n,; \t]+/', $validated['emails'], -1, PREG_SPLIT_NO_EMPTY);
            $cleanEmails = array_unique(array_filter(array_map('trim', $rawEmails)));

            foreach ($cleanEmails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $student = User::where('email', $email)->where('role', 'student')->first();
                    if ($student) {
                        $students->push($student);
                    } else {
                        $missingEmails[] = $email;
                    }
                } else {
                    $missingEmails[] = $email;
                }
            }
        }

        $students = $students->unique('id');

        if ($students->isEmpty()) {
            return redirect()->back()->withErrors([
                'emails' => 'لم يتم العثور على أي طلاب مسجلين بالبيانات المدخلة.'
            ])->with('error', 'فشل الاشتراك الجماعي: لم يتم تحديد أو العثور على أي طلاب مسجلين.');
        }

        $newEnrollmentsCount = 0;
        $reactivatedCount = 0;
        $alreadyEnrolledCount = 0;

        \Illuminate\Support\Facades\DB::transaction(function() use ($students, $courses, &$newEnrollmentsCount, &$reactivatedCount, &$alreadyEnrolledCount) {
            foreach ($students as $student) {
                foreach ($courses as $course) {
                    $enrollment = \App\Models\CourseEnrollment::where('student_id', $student->id)
                        ->where('course_id', $course->id)
                        ->first();

                    if ($enrollment) {
                        if ($enrollment->status !== 'active') {
                            $enrollment->update([
                                'status' => 'active',
                                'payment_status' => 'fully_paid',
                            ]);
                            $reactivatedCount++;
                        } else {
                            $alreadyEnrolledCount++;
                        }
                    } else {
                        \App\Models\CourseEnrollment::create([
                            'student_id' => $student->id,
                            'course_id' => $course->id,
                            'total_price' => $course->price ?? 0.00,
                            'paid_amount' => $course->price ?? 0.00,
                            'payment_status' => 'fully_paid',
                            'status' => 'active',
                            'progress_percentage' => 0.00,
                            'enrolled_at' => now(),
                        ]);
                        $newEnrollmentsCount++;
                    }
                }
            }
        });

        $studentsCount = $students->count();
        $coursesCount = $courses->count();

        $message = "تمت عملية الاشتراك الجماعي بنجاح: تفعيل {$newEnrollmentsCount} اشتراك جديد لـ {$studentsCount} طالب في {$coursesCount} كورس.";
        if ($reactivatedCount > 0) {
            $message .= " (تمت إعادة تنشيط {$reactivatedCount} اشتراك سابق).";
        }
        if ($alreadyEnrolledCount > 0) {
            $message .= " (تم تخطي {$alreadyEnrolledCount} اشتراك قائم بالفعل).";
        }
        if (!empty($missingEmails)) {
            $missingList = implode(', ', array_slice($missingEmails, 0, 5));
            if (count($missingEmails) > 5) {
                $missingList .= ' ...وغيرهم (' . count($missingEmails) . ' إيميل)';
            }
            $message .= " ⚠️ تنبيه: تعذر العثور على الإيميلات التالية: [{$missingList}].";
        }

        return redirect()->back()->with('success', $message);
    }

    public function showStudent(User $user)
    {
        if ($user->role !== 'student') {
            abort(404);
        }

        $user->load(['enrollments.course.category', 'enrollments.payments']);

        $enrolledCourseIds = $user->enrollments->pluck('course_id')->toArray();
        $availableCourses = Course::with(['category', 'subcategory', 'childSubcategory'])
            ->whereNotIn('id', $enrolledCourseIds)
            ->latest()
            ->get();


        return view('admin.students.show', compact('user', 'availableCourses'));
    }

    public function subscribeStudentFromProfile(Request $request, User $user)
    {
        if ($user->role !== 'student') {
            abort(403);
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $course = Course::findOrFail($validated['course_id']);

        $exists = \App\Models\CourseEnrollment::where('student_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "الطالب مشترك بالفعل في الكورس {$course->title}.");
        }

        \App\Models\CourseEnrollment::create([
            'student_id' => $user->id,
            'course_id' => $course->id,
            'total_price' => $course->price,
            'paid_amount' => $course->price,
            'payment_status' => 'fully_paid',
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        return redirect()->back()->with('success', "تم إضافة الطالب إلى الكورس {$course->title} بنجاح.");
    }

    public function unsubscribeStudentFromCourse(User $user, Course $course)
    {
        if ($user->role !== 'student') {
            abort(403);
        }

        $deleted = \App\Models\CourseEnrollment::where('student_id', $user->id)
            ->where('course_id', $course->id)
            ->delete();

        if ($deleted) {
            return redirect()->back()->with('success', "تم إلغاء اشتراك الطالب من الكورس {$course->title} بنجاح.");
        }

        return redirect()->back()->with('error', 'الاشتراك غير موجود.');
    }
}

