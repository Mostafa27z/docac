<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\Lesson;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCourseEnrollment
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Allow Admins and Instructors
        if (in_array($user->role, ['admin', 'instructor'])) {
            return $next($request);
        }

        // 1. Check if course parameter is present in the route (regardless of parameter name)
        $courseId = null;
        $course = $request->route('course') ?: $request->route('course_id');

        if ($course instanceof Course) {
            $courseId = $course->id;
        } elseif (is_numeric($course)) {
            $courseId = (int) $course;
        }

        // 2. Alternatively, check if lesson is present and fetch course_id
        if (!$courseId) {
            $lesson = $request->route('lesson') ?: $request->route('lecture_id');
            $lessonModel = null;

            if ($lesson instanceof Lesson) {
                $lessonModel = $lesson;
            } elseif (is_numeric($lesson)) {
                $lessonModel = Lesson::find($lesson);
            }

            if ($lessonModel) {
                $courseId = $lessonModel->section->course_id;
            }
        }

        if ($courseId) {
            $isEnrolled = $user->enrollments()->where('course_id', $courseId)->where('status', 'active')->exists();

            if (!$isEnrolled) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not enrolled in this course.'
                ], 403);
            }
        }

        return $next($request);
    }
}
