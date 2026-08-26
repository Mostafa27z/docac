<?php

namespace App\Http\Controllers\Web\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\CourseFile;
use App\Models\CourseActivationCode;
use App\Models\CourseEnrollment;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Services\BunnyStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InstructorCourseController extends Controller
{
    protected BunnyStorageService $bunnyStorage;

    public function __construct(BunnyStorageService $bunnyStorage)
    {
        $this->bunnyStorage = $bunnyStorage;
    }

    public function dashboard()
    {
        $instructorId = auth()->id();
        
        $stats = [
            'my_courses_count' => Course::where('instructor_id', $instructorId)->count(),
            'students_count' => \App\Models\CourseEnrollment::whereHas('course', function($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            })->count(),
            'live_sessions_count' => \App\Models\LiveSession::whereHas('course', function($q) use ($instructorId) {
                $q->where('instructor_id', $instructorId);
            })->count(),
        ];

        return view('instructor.dashboard', compact('stats'));
    }

    public function index()
    {
        $courses = Course::where('instructor_id', auth()->id())->latest()->get();
        return view('instructor.courses.index', compact('courses'));
    }

    public function subscriptionsIndex()
    {
        $instructorId = auth()->id();
        $courses = Course::where('instructor_id', $instructorId)->get();

        $enrollments = \App\Models\CourseEnrollment::whereHas('course', function($q) use ($instructorId) {
            $q->where('instructor_id', $instructorId);
        })->with(['course', 'student', 'payments'])->latest()->get();

        return view('instructor.subscriptions.index', compact('courses', 'enrollments'));
    }

    public function updateCoursePrice(Request $request, Course $course)
    {
        if ($course->instructor_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $course->update([
            'price' => $validated['price']
        ]);

        return redirect()->back()->with('success', 'تم تحديث سعر الكورس بنجاح.');
    }

    public function addInstallment(Request $request, \App\Models\CourseEnrollment $enrollment)
    {
        if ($enrollment->course->instructor_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        $newPaidAmount = $enrollment->paid_amount + $validated['amount'];
        
        // Ensure paid doesn't exceed total_price, but let's allow flexibility or cap it
        if ($newPaidAmount > $enrollment->total_price) {
            return redirect()->back()->with('error', 'المبلغ المدفوع يتجاوز إجمالي ثمن الكورس المستحق على الطالب.');
        }

        // Create the payment record
        \App\Models\CoursePayment::create([
            'course_enrollment_id' => $enrollment->id,
            'amount' => $validated['amount'],
            'notes' => $validated['notes'],
        ]);

        // Update payment status
        $paymentStatus = 'partially_paid';
        if ($newPaidAmount >= $enrollment->total_price) {
            $paymentStatus = 'fully_paid';
        }

        $enrollment->update([
            'paid_amount' => $newPaidAmount,
            'payment_status' => $paymentStatus,
        ]);

        return redirect()->back()->with('success', "تم تسجيل دفعة بقيمة {$validated['amount']} للطالب {$enrollment->student->name} بنجاح.");
    }

    protected function checkCourseAccess(Course $course)
    {
        $user = auth()->user();
        if ($user->role !== 'admin' && $course->instructor_id !== $user->id) {
            abort(403, 'غير مصرح لك بإدارة هذا الكورس.');
        }
    }

    public function manage(Course $course)
    {
        $this->checkCourseAccess($course);

        $course->load(['sections.lessons.quiz.questions.options', 'files', 'liveSessions']);

        return view('instructor.courses.manage', compact('course'));
    }

    public function togglePublish(Course $course)
    {
        $this->checkCourseAccess($course);

        $newStatus = $course->status === 'published' ? 'draft' : 'published';
        $publishedAt = $newStatus === 'published' ? now() : null;

        $course->update([
            'status' => $newStatus,
            'published_at' => $publishedAt,
        ]);

        $message = $newStatus === 'published' ? 'تم نشر الكورس بنجاح.' : 'تم تحويل الكورس إلى مسودة.';
        return redirect()->back()->with('success', $message);
    }

    public function storeCourse(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:recorded,live,mixed',
        ]);

        $course = Course::create([
            'instructor_id' => auth()->id(),
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'description' => $validated['description'],
            'type' => $validated['type'],
            'status' => 'draft',
        ]);

        return redirect()->route('instructor.courses.manage', $course->id)->with('success', 'تم إنشاء مسودة الكورس بنجاح، يمكنك الآن إضافة الفيديوهات والامتحانات.');
    }

    public function addSection(Request $request, Course $course)
    {
        $this->checkCourseAccess($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        CourseSection::create([
            'course_id' => $course->id,
            'title' => $validated['title'],
            'sort_order' => $course->sections()->count() + 1,
        ]);

        return redirect()->back()->with('success', 'تم إضافة قسم جديد بنجاح.');
    }

    public function storeLesson(Request $request, CourseSection $section)
    {
        if ($section->course->instructor_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:video,quiz',
            'video_file' => 'nullable|file|mimetypes:video/mp4,video/quicktime|max:102400', // 100MB limit
            'video_duration_seconds' => 'nullable|integer',
            'is_preview' => 'nullable|boolean',
        ]);

        $videoPath = null;
        if ($request->hasFile('video_file') && $validated['type'] === 'video') {
            // Push video directly to Bunny Stream
            $videoPath = $this->bunnyStorage->uploadVideo($request->file('video_file'), $validated['title']);
        }

        Lesson::create([
            'section_id' => $section->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'video_url' => $videoPath,
            'video_duration_seconds' => $validated['video_duration_seconds'] ?? 0,
            'sort_order' => $section->lessons()->count() + 1,
            'is_preview' => $request->boolean('is_preview'),
        ]);

        return redirect()->back()->with('success', 'تم حفظ الدرس بنجاح ورفعه بنجاح.');
    }

    public function uploadChunkedLesson(Request $request, CourseSection $section)
    {
        $this->checkCourseAccess($section->course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,quiz',
            'video_guid' => 'nullable|string',
            'is_preview' => 'nullable|boolean',
        ]);

        // Step 1: Initialize Video object in Bunny Stream and generate TUS credentials
        if ($request->has('init') && $validated['type'] === 'video') {
            \Illuminate\Support\Facades\Log::info("Controller: Initializing TUS upload credentials for: {$validated['title']}");
            
            $videoGuid = $this->bunnyStorage->createStreamVideo($validated['title']);
            if (!$videoGuid) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل إنشاء إدخال الفيديو على Bunny Stream.'
                ], 500);
            }

            $libraryId = config('services.bunny.stream_library_id');
            $apiKey = config('services.bunny.stream_api_key');
            $expirationTime = time() + 7200; // 2 hours expiration

            // Generate SHA256 Signature: LibraryId + ApiKey + ExpirationTime + VideoId
            $signature = hash('sha256', $libraryId . $apiKey . $expirationTime . $videoGuid);

            return response()->json([
                'success' => true,
                'video_guid' => $videoGuid,
                'library_id' => $libraryId,
                'expiration_time' => $expirationTime,
                'signature' => $signature
            ]);
        }

        // Step 2: Finalize lesson metadata creation once upload completes
        $lesson = Lesson::create([
            'section_id' => $section->id,
            'title' => $validated['title'],
            'description' => $request->input('description'),
            'type' => $validated['type'],
            'video_url' => $validated['video_guid'] ?? null,
            'video_duration_seconds' => $request->input('video_duration_seconds', 0),
            'sort_order' => $section->lessons()->count() + 1,
            'is_preview' => $request->boolean('is_preview'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ الكورس والدرس بنجاح',
            'lesson' => $lesson
        ]);
    }

    public function uploadAttachment(Request $request, Course $course)
    {
        $this->checkCourseAccess($course);

        \Illuminate\Support\Facades\Log::info("Attachment Upload Debug Log", [
            'course_id' => $course->id,
            'php_ini' => [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'max_execution_time' => ini_get('max_execution_time'),
                'memory_limit' => ini_get('memory_limit'),
            ],
            'server' => [
                'content_length' => $_SERVER['CONTENT_LENGTH'] ?? null,
                'content_type' => $_SERVER['CONTENT_TYPE'] ?? null,
            ],
            'files_raw' => $_FILES['attachment'] ?? null,
            'laravel_file' => [
                'has_file' => $request->hasFile('attachment'),
                'is_valid' => $request->file('attachment')?->isValid(),
                'error_code' => $request->file('attachment')?->getError(),
                'error_message' => $request->file('attachment')?->getErrorMessage(),
                'size_bytes' => $request->file('attachment')?->getSize(),
                'client_name' => $request->file('attachment')?->getClientOriginalName(),
                'mime_type' => $request->file('attachment')?->getClientMimeType(),
            ]
        ]);

        // Check if upload failed due to size limit
        $uploadError = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
            $err = $_FILES['attachment']['error'];
            \Illuminate\Support\Facades\Log::warning("Attachment Upload Error Code Detected: " . $err, [
                'files_attachment' => $_FILES['attachment']
            ]);
            if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
                // Get request size in MB from Content-Length header as proxy size
                $contentLengthBytes = $_SERVER['CONTENT_LENGTH'] ?? 0;
                $approxSizeMb = round($contentLengthBytes / 1024 / 1024, 2);
                $uploadError = "الحد الأقصى المسموح به للملف هو 20 ميجابايت، وحجم الملف المرفوع حالياً هو {$approxSizeMb} ميجابايت (فشل الرفع بسبب إعدادات السيرفر).";
            }
        }

        if ($uploadError) {
            \Illuminate\Support\Facades\Log::error("Attachment Upload Blocked by PHP upload_max_filesize/post_max_size: " . $uploadError);
            return redirect()->back()->withErrors(['attachment' => $uploadError])->withInput();
        }

        $file = $request->file('attachment');
        $fileSizeMb = $file ? round($file->getSize() / 1024 / 1024, 2) : 0;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'attachment' => 'required|file|max:20480', // 20MB limit
            'lesson_id' => 'nullable|exists:lessons,id',
        ], [
            'attachment.max' => "الحد الأقصى المسموح به للملف هو 20 ميجابايت، وحجم الملف المرفوع حالياً هو {$fileSizeMb} ميجابايت.",
            'attachment.uploaded' => "فشل رفع الملف. قد يكون حجم الملف أكبر من الحد الأقصى المسموح به على السيرفر (20 ميجابايت).",
        ]);

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $this->bunnyStorage->uploadFile(
                $request->file('attachment'),
                'courses/attachments/course_' . $course->id
            );
        }

        if ($filePath) {
            CourseFile::create([
                'course_id' => $course->id,
                'lesson_id' => $validated['lesson_id'] ?? null,
                'title' => $validated['title'],
                'file_path' => $filePath,
                'file_name' => $request->file('attachment')->getClientOriginalName(),
                'mime_type' => $request->file('attachment')->getClientMimeType(),
                'file_size_bytes' => $request->file('attachment')->getSize(),
            ]);

            return redirect()->back()->with('success', 'تم رفع الملف بنجاح إلى Bunny Storage.');
        }

        return redirect()->back()->with('error', 'حدث خطأ أثناء رفع الملف.');
    }

    public function destroyAttachment(CourseFile $file)
    {
        $this->checkCourseAccess($file->course);

        // Delete from local storage if exists
        if (!empty($file->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($file->file_path);
        }

        // Delete database record
        $file->delete();

        return redirect()->back()->with('success', 'تم حذف الملف بنجاح.');
    }

    public function updateCourse(Request $request, Course $course)
    {
        $this->checkCourseAccess($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:recorded,live,mixed',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'child_subcategory_id' => 'nullable|exists:child_subcategories,id',
            'thumbnail_file' => 'nullable|image|max:10240',
            'thumbnail' => 'nullable|image|max:10240',
        ]);

        $updateData = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'price' => $validated['price'],
            'category_id' => $validated['category_id'] ?? null,
            'subcategory_id' => $validated['subcategory_id'] ?? null,
            'child_subcategory_id' => $validated['child_subcategory_id'] ?? null,
        ];

        $file = $request->file('thumbnail_file') ?? $request->file('thumbnail') ?? $request->file('image');
        if ($file) {
            $filePath = $this->bunnyStorage->uploadFile($file, 'courses/thumbnails');
            if ($filePath) {
                $updateData['thumbnail'] = $filePath;
                \Illuminate\Support\Facades\Log::info("InstructorCourseController updateCourse: Updated course thumbnail.", [
                    'course_id' => $course->id,
                    'thumbnail' => $filePath
                ]);
            }
        }

        $course->update($updateData);

        return redirect()->back()->with('success', 'تم تحديث بيانات الكورس بنجاح.');
    }

    public function destroyCourse(Request $request, Course $course)
    {
        $this->checkCourseAccess($course);

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->password, auth()->user()->password)) {
            return redirect()->back()->withErrors(['password' => 'كلمة المرور غير صحيحة.'])->with('error', 'فشل الحذف: كلمة المرور المدخلة غير صحيحة.');
        }

        // Delete course cascade handles sections, lessons, etc via DB foreign key cascades
        $course->delete();

        return redirect()->route('instructor.courses.index')->with('success', 'تم حذف الكورس وجميع محتوياته بنجاح.');
    }

    public function updateLesson(Request $request, Lesson $lesson)
    {
        $this->checkCourseAccess($lesson->section->course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_preview' => 'nullable|boolean',
            'video_duration_seconds' => 'nullable|integer|min:0',
        ]);

        $lesson->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_preview' => $request->boolean('is_preview'),
            'video_duration_seconds' => $validated['video_duration_seconds'] ?? 0,
        ]);

        return redirect()->back()->with('success', 'تم تحديث بيانات الدرس بنجاح.');
    }

    public function destroyLesson(Lesson $lesson)
    {
        $this->checkCourseAccess($lesson->section->course);

        $lesson->delete();

        return redirect()->back()->with('success', 'تم حذف الدرس بنجاح.');
    }

    public function storeLiveSession(Request $request, Course $course)
    {
        $this->checkCourseAccess($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'meeting_provider' => 'required|in:zoom,google_meet',
            'meeting_url' => 'required|string|max:255',
            'meeting_id' => 'nullable|string|max:100',
        ]);

        \App\Models\LiveSession::create([
            'course_id' => $course->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'],
            'meeting_provider' => $validated['meeting_provider'],
            'meeting_url' => $validated['meeting_url'],
            'meeting_id' => $validated['meeting_id'] ?? null,
            'status' => 'scheduled',
        ]);

        return redirect()->back()->with('success', 'تم إنشاء وتجدول جلسة البث المباشر بنجاح.');
    }

    public function destroyLiveSession(\App\Models\LiveSession $liveSession)
    {
        $this->checkCourseAccess($liveSession->course);

        $liveSession->delete();

        return redirect()->back()->with('success', 'تم إلغاء وحذف جلسة البث المباشر بنجاح.');
    }

    public function courseAnalytics(Course $course)
    {
        $this->checkCourseAccess($course);

        $course->load(['sections.lessons.quiz']);
        $totalLessons = $course->lessons()->count();

        $enrollments = CourseEnrollment::where('course_id', $course->id)
            ->with(['student'])
            ->get()
            ->map(function($enrollment) use ($course, $totalLessons) {
                $studentId = $enrollment->student_id;

                $completedLessonsCount = LessonProgress::where('student_id', $studentId)
                    ->whereNotNull('completed_at')
                    ->whereHas('lesson.section', function($q) use ($course) {
                        $q->where('course_id', $course->id);
                    })->count();

                $quizAttempts = QuizAttempt::where('student_id', $studentId)
                    ->whereHas('quiz.lesson.section', function($q) use ($course) {
                        $q->where('course_id', $course->id);
                    })->with('quiz.lesson')->latest()->get();

                return [
                    'enrollment' => $enrollment,
                    'student' => $enrollment->student,
                    'completed_lessons_count' => $completedLessonsCount,
                    'total_lessons_count' => $totalLessons,
                    'progress_percentage' => $enrollment->progress_percentage,
                    'quiz_attempts' => $quizAttempts,
                ];
            });

        return view('instructor.courses.analytics', compact('course', 'enrollments', 'totalLessons'));
    }

    public function generateCourseCodes(Request $request, Course $course)
    {
        $this->checkCourseAccess($course);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $quantity = (int) $validated['quantity'];
        for ($i = 0; $i < $quantity; $i++) {
            \App\Models\CourseActivationCode::create([
                'course_id' => $course->id,
                'created_by_user_id' => auth()->id(),
                'code' => strtoupper(\Illuminate\Support\Str::random(12)),
                'status' => 'unused',
            ]);
        }

        return redirect()->back()->with('success', "تم إنشاء {$quantity} كود تفعيل بنجاح.");
    }
}
