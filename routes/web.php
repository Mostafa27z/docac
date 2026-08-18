<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Admin\AdminController;
use App\Http\Controllers\Web\Admin\AdminChatController;
use App\Http\Controllers\Web\Instructor\InstructorCourseController;
use App\Http\Controllers\Web\Instructor\InstructorQuizController;
use App\Http\Controllers\Web\Instructor\InstructorChatController;

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
    Route::get('/activation-codes', [AdminController::class, 'activationCodesIndex'])->name('admin.codes.index');

    // Admin Chat Monitoring Routes
    Route::get('/chats', [AdminChatController::class, 'index'])->name('admin.chats.index');

    // Student Management Routes
    Route::get('/students', [AdminController::class, 'studentsList'])->name('admin.students.index');
    Route::post('/students/{user}/reset-device', [AdminController::class, 'resetDevice'])->name('admin.students.resetDevice');
    Route::post('/students/{user}/toggle-status', [AdminController::class, 'toggleStatus'])->name('admin.students.toggleStatus');

    // Settings Routes
    Route::get('/settings', [AdminController::class, 'settingsIndex'])->name('admin.settings.index');
    Route::post('/settings', [AdminController::class, 'settingsUpdate'])->name('admin.settings.update');
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
    Route::post('/courses/{course}/price', [InstructorCourseController::class, 'updateCoursePrice'])->name('instructor.courses.price');
    Route::post('/enrollments/{enrollment}/installments', [InstructorCourseController::class, 'addInstallment'])->name('instructor.enrollments.installment');

    // 4. Quizzes & MCQs
    Route::get('/quizzes', [InstructorQuizController::class, 'index'])->name('instructor.quizzes.index');
    Route::post('/lessons/{lesson}/quizzes', [InstructorQuizController::class, 'storeQuiz'])->name('instructor.quizzes.store');
    Route::post('/quizzes/{quiz}/questions', [InstructorQuizController::class, 'storeQuestion'])->name('instructor.questions.store');

    // 5. Chats & Messages
    Route::get('/chats', [InstructorChatController::class, 'index'])->name('instructor.chats.index');
    Route::post('/chats/start', [InstructorChatController::class, 'startChat'])->name('instructor.chats.start');
    Route::post('/chats/broadcast', [InstructorChatController::class, 'broadcast'])->name('instructor.chats.broadcast');
    Route::post('/chats/{conversation}/messages', [InstructorChatController::class, 'sendMessage'])->name('instructor.chats.sendMessage');
});
