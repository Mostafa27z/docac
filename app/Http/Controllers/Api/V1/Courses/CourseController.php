<?php

namespace App\Http\Controllers\Api\V1\Courses;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CourseResource;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::where('status', 'published');

        // Search support
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtering by category_id (if passed)
        if ($request->has('category_id')) {
            // Note: Since no dynamic DB relationship exists yet, we simulate matching or filter placeholder logic
        }

        // Filtering by subcategory_id (if passed)
        if ($request->has('subcategory_id')) {
            // Note: Since no dynamic DB relationship exists yet, we simulate matching or filter placeholder logic
        }

        // Filtering by type (recorded, live, mixed)
        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        $courses = $query->with('instructor')->paginate(15);

        return CourseResource::collection($courses)->additional([
            'success' => true,
            'message' => 'Courses retrieved successfully.'
        ]);
    }

    public function show(Course $course)
    {
        if ($course->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Course not found or is in draft mode.'
            ], 404);
        }

        $course->load(['instructor', 'sections.lessons']);

        return (new CourseResource($course))->additional([
            'success' => true,
            'message' => 'Course details retrieved successfully.'
        ]);
    }

    public function enroll(Request $request, Course $course)
    {
        $validated = $request->validate([
            'code' => 'required|string|exists:course_activation_codes,code',
        ]);

        $user = $request->user();

        if ($course->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot enroll in an unpublished course.'
            ], 400);
        }

        $activationCode = \App\Models\CourseActivationCode::where('code', $validated['code'])
            ->where('course_id', $course->id)
            ->first();

        if (!$activationCode) {
            return response()->json([
                'success' => false,
                'message' => 'The provided activation code does not match this course.'
            ], 400);
        }

        if ($activationCode->is_used) {
            return response()->json([
                'success' => false,
                'message' => 'This activation code has already been used.'
            ], 400);
        }

        $existing = CourseEnrollment::where('course_id', $course->id)
            ->where('student_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this course.'
            ], 400);
        }

        CourseEnrollment::create([
            'course_id' => $course->id,
            'student_id' => $user->id,
            'status' => 'active',
            'progress_percentage' => 0.00,
            'enrolled_at' => now(),
        ]);

        // Mark activation code as used
        $activationCode->update([
            'is_used' => true,
            'used_by_student_id' => $user->id,
            'used_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully enrolled in course using activation code.'
        ], 201);
    }

    public function generateActivationCode(Request $request, Course $course)
    {
        $user = $request->user();

        // 1. Authorization: Only admin and the teacher/instructor of this course can generate code
        if ($user->role !== 'admin' && ($user->role !== 'instructor' || $course->instructor_id !== $user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins and course instructors can generate activation codes.'
            ], 403);
        }

        // Generate unique code format (e.g. ECG-XXXX-XXXX)
        $cleanTitle = substr(preg_replace('/[^A-Za-z0-9]/', '', $course->title), 0, 4);
        $code = strtoupper($cleanTitle . '-' . Str::random(4) . '-' . Str::random(4));

        $activationCode = \App\Models\CourseActivationCode::create([
            'course_id' => $course->id,
            'code' => $code,
            'is_used' => false,
            'created_by_user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Activation code generated successfully.',
            'data' => [
                'code' => $activationCode->code,
                'course_id' => $activationCode->course_id,
                'is_used' => $activationCode->is_used,
            ]
        ], 201);
    }

    public function activateCourseWithCode(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|exists:course_activation_codes,code',
        ]);

        $user = $request->user();

        $activationCode = \App\Models\CourseActivationCode::where('code', $validated['code'])->first();

        // Check if code is already used
        if ($activationCode->is_used) {
            return response()->json([
                'success' => false,
                'message' => 'This activation code has already been used.'
            ], 400);
        }

        $course = $activationCode->course;

        // Check if student is already enrolled
        $existing = CourseEnrollment::where('course_id', $course->id)
            ->where('student_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this course.'
            ], 400);
        }

        // Create enrollment
        CourseEnrollment::create([
            'course_id' => $course->id,
            'student_id' => $user->id,
            'status' => 'active',
            'progress_percentage' => 0.00,
            'enrolled_at' => now(),
        ]);

        // Mark code as used
        $activationCode->update([
            'is_used' => true,
            'used_by_student_id' => $user->id,
            'used_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Course activated successfully. You are now enrolled.',
            'data' => [
                'course_id' => $course->id,
                'course_title' => $course->title,
            ]
        ]);
    }

    public function myCourses(Request $request)
    {
        $user = $request->user();

        $courses = Course::whereHas('enrollments', function($q) use ($user) {
            $q->where('student_id', $user->id);
        })->with('instructor')->paginate(15);

        // Map to standard details with overall progress %
        $items = $courses->map(function($course) use ($user) {
            $enrollment = $course->enrollments()->where('student_id', $user->id)->first();
            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'description' => $course->description,
                'thumbnail' => $course->thumbnail,
                'type' => $course->type,
                'instructor' => $course->instructor ? [
                    'id' => $course->instructor->id,
                    'name' => $course->instructor->name
                ] : null,
                'overall_progress_percentage' => $enrollment ? (float)$enrollment->progress_percentage : 0.00,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Enrolled courses retrieved successfully.',
            'data' => $items,
            'meta' => [
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
            ]
        ]);
    }

    public function categories(Request $request)
    {
        // Spec 2. التصنيفات
        // We will return a predefined rich mockup list of categories since the database is medical-centric.
        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully.',
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Cardiology (القلب والأوعية الدموية)',
                    'image_url' => 'https://lms.test/categories/cardiology.jpg',
                ],
                [
                    'id' => 2,
                    'name' => 'Pediatrics (طب الأطفال)',
                    'image_url' => 'https://lms.test/categories/pediatrics.jpg',
                ],
                [
                    'id' => 3,
                    'name' => 'Clinical Pathology (التحاليل الطبية)',
                    'image_url' => 'https://lms.test/categories/pathology.jpg',
                ]
            ]
        ]);
    }

    public function subcategories(Request $request, $categoryId)
    {
        // Spec 2. التصنيفات الفرعية
        $subcategories = [];
        if ((int)$categoryId === 1) {
            $subcategories = [
                ['id' => 11, 'category_id' => 1, 'name' => 'ECG Reading (قراءة رسم القلب)'],
                ['id' => 12, 'category_id' => 1, 'name' => 'Cardiac Arrhythmias (اضطرابات ضربات القلب)']
            ];
        } elseif ((int)$categoryId === 2) {
            $subcategories = [
                ['id' => 21, 'category_id' => 2, 'name' => 'Neonatology (حديثي الولادة)'],
                ['id' => 22, 'category_id' => 2, 'name' => 'Pediatric Emergencies (طوارئ الأطفال)']
            ];
        } else {
            $subcategories = [
                ['id' => 31, 'category_id' => $categoryId, 'name' => 'Hematology (أمراض الدم)'],
                ['id' => 32, 'category_id' => $categoryId, 'name' => 'Biochemistry (الكيمياء الحيوية)']
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Subcategories retrieved successfully.',
            'data' => $subcategories
        ]);
    }

    public function studentDashboard(Request $request)
    {
        $user = $request->user();

        // 1. Enrolled courses (limit 5)
        $myCourses = Course::whereHas('enrollments', function($q) use ($user) {
            $q->where('student_id', $user->id);
        })->with('instructor')->limit(5)->get();

        // 2. Upcoming live sessions (limit 5)
        $upcomingLive = \App\Models\LiveSession::whereHas('course.enrollments', function($q) use ($user) {
            $q->where('student_id', $user->id);
        })->where('start_at', '>=', now())
          ->where('status', 'scheduled')
          ->with('course')
          ->orderBy('start_at', 'asc')
          ->limit(5)
          ->get();

        return response()->json([
            'success' => true,
            'message' => 'Student dashboard statistics retrieved successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'my_courses' => CourseResource::collection($myCourses),
                'upcoming_live_sessions' => $upcomingLive->map(function($session) {
                    return [
                        'id' => $session->id,
                        'course_title' => $session->course->title,
                        'title' => $session->title,
                        'description' => $session->description,
                        'start_at' => $session->start_at,
                        'meeting_provider' => $session->meeting_provider,
                        'meeting_url' => $session->meeting_url,
                    ];
                }),
                'stats' => [
                    'enrolled_courses_count' => $user->enrollments()->count(),
                    'completed_courses_count' => $user->enrollments()->where('status', 'completed')->count(),
                ]
            ]
        ]);
    }
}
