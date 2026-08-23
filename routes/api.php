<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Courses\CourseController;
use App\Http\Controllers\Api\V1\Lessons\LessonController;
use App\Http\Controllers\Api\V1\Quizzes\QuizController;
use App\Http\Controllers\Api\V1\Live\LiveController;
use App\Http\Controllers\Api\V1\Chat\ChatController;
use App\Http\Controllers\Api\V1\Files\FileController;
use App\Http\Controllers\Api\V1\BannerApiController;

/*
|--------------------------------------------------------------------------
| API Routes - V1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // 1. Public Authentication Routes
    Route::post('student/register', [AuthController::class, 'register']);
    Route::post('student/login', [AuthController::class, 'login']);
    Route::get('contacts', [\App\Http\Controllers\Api\V1\ContactApiController::class, 'index']);
    Route::get('student/banners', [BannerApiController::class, 'index']);

    // 2. Protected routes by Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        
        // Auth Meta
        Route::post('student/logout', [AuthController::class, 'logout']);
        Route::get('student/profile', [AuthController::class, 'me']);
        Route::put('student/profile', [AuthController::class, 'updateProfile']);
        Route::post('student/device-token', [AuthController::class, 'registerDeviceToken']);

        // Categories (Spec: 2. التصنيفات)
        // Note: For now, we will add basic category/subcategory routes. Let's direct them to a CategoryController or define inline closures since Category tables don't exist yet, or dynamically return mock data that matches the specification. We can make a CategoryController!
        Route::get('student/categories', [CourseController::class, 'categories']);
        Route::get('student/categories/{category_id}/subcategories', [CourseController::class, 'subcategories']);

        // 3. Courses (Spec: 3. الكورسات)
        Route::middleware('single.device')->group(function () {
            Route::get('student/courses', [CourseController::class, 'index']);
            Route::get('student/courses/{course}', [CourseController::class, 'show']);
            Route::post('student/courses/{course}/enroll', [CourseController::class, 'enroll']);
            Route::get('student/my-courses', [CourseController::class, 'myCourses']);
            Route::get('student/installments', [CourseController::class, 'installments']);
        });

        // Other utility routes
        Route::post('courses/{course}/activation-code', [CourseController::class, 'generateActivationCode']);
        Route::post('courses/activate-with-code', [CourseController::class, 'activateCourseWithCode']);

        // Routes requiring Course Enrollment check middleware
        Route::middleware(['course.enrollment', 'single.device'])->group(function () {
            
            // 4. Lectures & Progress (Spec: 4. المحاضرات ومعدل التقدم)
            Route::get('student/courses/{course}/lectures', [LessonController::class, 'courseLectures']);
            Route::get('student/lectures/{lesson}', [LessonController::class, 'show']);
            Route::put('student/lectures/{lesson}/progress', [LessonController::class, 'updateProgress']);

            // 5. Files & Attachments (Spec: 5. الملفات والمرفقات)
            Route::get('student/courses/{course}/files', [FileController::class, 'getCourseFiles']);
            Route::get('student/files/{file_id}/download', [FileController::class, 'downloadFile']);

            // 6. Live Events (Spec: 6. البث المباشر)
            Route::get('student/courses/{course}/live-events', [LiveController::class, 'index']);

            // 7. Exams & MCQ (Spec: 7. الامتحانات)
            Route::get('student/lectures/{lesson}/quiz', [QuizController::class, 'getLessonQuiz']);
            Route::post('student/lectures/{lesson}/quiz/submit', [QuizController::class, 'submitLessonQuiz']);

            // 8. Chat System (Spec: 8. المحادثات)
            Route::get('student/chats', [ChatController::class, 'getConversations']);
            Route::get('student/courses/{course}/chat', [ChatController::class, 'getCourseChat']);
            Route::post('student/courses/{course}/chat/messages', [ChatController::class, 'sendCourseChatMessage']);
        });

    });

});
