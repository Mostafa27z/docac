<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Admin\AdminController;
use App\Http\Controllers\Web\Instructor\InstructorCourseController;
use App\Http\Controllers\Web\Instructor\InstructorQuizController;

use App\Http\Controllers\Web\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', [HomeController::class, 'index'])->name('login');
Route::post('/login', [HomeController::class, 'login'])->name('web.login');
Route::post('/logout', [HomeController::class, 'logout'])->name('web.logout');

// Admin Panel Routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/teachers', [AdminController::class, 'teachersList'])->name('admin.teachers.index');
    Route::post('/instructors', [AdminController::class, 'createInstructor'])->name('admin.instructors.create');
    Route::post('/activation-codes', [AdminController::class, 'generateCodes'])->name('admin.codes.generate');
});

// Instructor Panel Routes
Route::prefix('instructor')->middleware(['auth', 'role:instructor'])->group(function () {
    // 1. Dashboard Overview
    Route::get('/dashboard', [InstructorCourseController::class, 'dashboard'])->name('instructor.dashboard');

    // 2. Course Management
    Route::get('/courses', [InstructorCourseController::class, 'index'])->name('instructor.courses.index');
    Route::post('/courses', [InstructorCourseController::class, 'storeCourse'])->name('instructor.courses.store');
    Route::get('/courses/{course}/manage', [InstructorCourseController::class, 'manage'])->name('instructor.courses.manage');
    Route::post('/courses/{course}/publish', [InstructorCourseController::class, 'togglePublish'])->name('instructor.courses.publish');
    Route::post('/courses/{course}/sections', [InstructorCourseController::class, 'addSection'])->name('instructor.sections.store');
    Route::post('/sections/{section}/lessons/chunked', [InstructorCourseController::class, 'uploadChunkedLesson'])->name('instructor.lessons.chunked');
    Route::post('/courses/{course}/attachments', [InstructorCourseController::class, 'uploadAttachment'])->name('instructor.attachments.store');

    // 3. Subscriptions & Codes
    Route::get('/subscriptions', [InstructorCourseController::class, 'subscriptionsIndex'])->name('instructor.subscriptions.index');
    Route::post('/courses/{course}/activation-codes', [InstructorCourseController::class, 'generateActivationCodes'])->name('instructor.courses.codes');

    // 4. Quizzes & MCQs
    Route::get('/quizzes', [InstructorQuizController::class, 'index'])->name('instructor.quizzes.index');
    Route::post('/lessons/{lesson}/quizzes', [InstructorQuizController::class, 'storeQuiz'])->name('instructor.quizzes.store');
    Route::post('/quizzes/{quiz}/questions', [InstructorQuizController::class, 'storeQuestion'])->name('instructor.questions.store');
});
