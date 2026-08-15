<?php

namespace App\Http\Controllers\Api\V1\Lessons;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\LessonResource;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\BunnyCdnService;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    protected BunnyCdnService $bunnyCdn;

    public function __construct(BunnyCdnService $bunnyCdn)
    {
        $this->bunnyCdn = $bunnyCdn;
    }

    public function courseLectures(Request $request, \App\Models\Course $course)
    {
        $user = $request->user();
        $sections = $course->sections()->with('lessons')->get();

        $data = $sections->map(function($section) use ($user) {
            return [
                'id' => $section->id,
                'title' => $section->title,
                'lessons' => $section->lessons->map(function($lesson) use ($user) {
                    $progress = LessonProgress::where('student_id', $user->id)
                        ->where('lesson_id', $lesson->id)
                        ->first();

                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'type' => $lesson->type,
                        'video_duration_seconds' => $lesson->video_duration_seconds,
                        'is_preview' => (bool)$lesson->is_preview,
                        'watched_seconds' => $progress ? $progress->watched_seconds : 0,
                        'is_completed' => $progress ? !is_null($progress->completed_at) : false,
                    ];
                })
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Course lectures retrieved successfully.',
            'data' => $data
        ]);
    }

    public function show(Request $request, Lesson $lesson)
    {
        // Route is protected by course.enrollment middleware
        $lesson->load('section');

        // If it's a video type and has video_url stored, sign it using Bunny CDN
        if ($lesson->type === 'video' && $lesson->video_url) {
            $lesson->video_url = $this->bunnyCdn->generateSignedUrl($lesson->video_url);
        }

        return (new LessonResource($lesson))->additional([
            'success' => true,
            'message' => 'Lesson details retrieved successfully.'
        ]);
    }

    public function updateProgress(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'watched_seconds' => 'required|integer|min:0',
            'is_completed' => 'sometimes|boolean',
        ]);

        $user = $request->user();
        $duration = $lesson->video_duration_seconds > 0 ? $lesson->video_duration_seconds : 1;

        // Calculate watched percentage
        $percentage = min(100.00, ($validated['watched_seconds'] / $duration) * 100);
        
        $shouldComplete = (isset($validated['is_completed']) && $validated['is_completed']) || ($percentage >= 90.00);
        $completedAt = $shouldComplete ? now() : null;

        // Upsert logic for efficiency
        $progress = LessonProgress::updateOrCreate(
            [
                'student_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'watched_seconds' => $validated['watched_seconds'],
                'duration_seconds' => $lesson->video_duration_seconds,
                'percentage' => $percentage,
                'last_position_seconds' => $validated['watched_seconds'], // Align position with watched seconds for convenience
                'completed_at' => $completedAt,
            ]
        );

        // Update overall course progress percentage
        $this->updateCourseEnrollmentProgress($user->id, $lesson->section->course_id);

        return response()->json([
            'success' => true,
            'message' => 'Progress tracked successfully.',
            'data' => [
                'watched_seconds' => $progress->watched_seconds,
                'percentage' => $progress->percentage,
                'last_position_seconds' => $progress->last_position_seconds,
                'completed_at' => $progress->completed_at,
            ]
        ]);
    }

    protected function updateCourseEnrollmentProgress(int $studentId, int $courseId)
    {
        // 1. Fetch total lessons of type video/quiz for the course
        $totalLessons = Lesson::whereHas('section', function($q) use ($courseId) {
            $q->where('course_id', $courseId);
        })->count();

        if ($totalLessons === 0) {
            return;
        }

        // 2. Fetch completed progress count
        $completedLessons = LessonProgress::where('student_id', $studentId)
            ->whereNotNull('completed_at')
            ->whereHas('lesson.section', function($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })
            ->count();

        $overallPercentage = ($completedLessons / $totalLessons) * 100;
        $status = $overallPercentage >= 100 ? 'completed' : 'active';
        $completedDate = $overallPercentage >= 100 ? now() : null;

        // Update enrollment
        \App\Models\CourseEnrollment::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->update([
                'progress_percentage' => min(100.00, $overallPercentage),
                'status' => $status,
                'completed_at' => $completedDate,
            ]);
    }
}
