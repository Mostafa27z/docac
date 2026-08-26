<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Admin\AdminController;
use App\Http\Controllers\Web\Admin\AdminChatController;
use App\Http\Controllers\Web\Admin\AdminCategoryController;
use App\Http\Controllers\Web\Admin\AdminCourseController;
use App\Http\Controllers\Web\Admin\AdminBannerController;
use App\Http\Controllers\Web\Instructor\InstructorCourseController;
use App\Http\Controllers\Web\Instructor\InstructorQuizController;
use App\Http\Controllers\Web\Instructor\InstructorChatController;
use App\Http\Controllers\Web\ProfileController;

use App\Http\Controllers\Web\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', [HomeController::class, 'index'])->name('login');
Route::post('/login', [HomeController::class, 'login'])->name('web.login');
Route::post('/logout', [HomeController::class, 'logout'])->name('web.logout');

// Admin Panel Routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/teachers', [AdminController::class, 'teachersList'])->name('admin.teachers.index');
    Route::post('/teachers', [AdminController::class, 'createTeacher'])->name('admin.teachers.store');
    Route::put('/teachers/{user}', [AdminController::class, 'updateTeacher'])->name('admin.teachers.update');
    Route::delete('/teachers/{user}', [AdminController::class, 'destroyTeacher'])->name('admin.teachers.destroy');
    Route::post('/instructors', [AdminController::class, 'createTeacher'])->name('admin.instructors.create'); // Keep alias for backwards compatibility
    Route::post('/activation-codes', [AdminController::class, 'generateCodes'])->name('admin.codes.generate');
    Route::get('/activation-codes', [AdminController::class, 'activationCodesIndex'])->name('admin.codes.index');

    // Admin Banner Routes
    Route::get('/banners', [AdminBannerController::class, 'index'])->name('admin.banners.index');
    Route::post('/banners', [AdminBannerController::class, 'store'])->name('admin.banners.store');
    Route::put('/banners/{banner}', [AdminBannerController::class, 'update'])->name('admin.banners.update');
    Route::delete('/banners/{banner}', [AdminBannerController::class, 'destroy'])->name('admin.banners.destroy');
    Route::post('/banners/{banner}/toggle', [AdminBannerController::class, 'toggleActive'])->name('admin.banners.toggle');

    // Admin Course Management Routes
    Route::get('/courses', [AdminCourseController::class, 'index'])->name('admin.courses.index');
    Route::post('/courses', [AdminCourseController::class, 'store'])->name('admin.courses.store');
    Route::put('/courses/{course}', [AdminCourseController::class, 'update'])->name('admin.courses.update');
    Route::delete('/courses/{course}', [AdminCourseController::class, 'destroy'])->name('admin.courses.destroy');

    // Category & Subcategory Routes
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'storeCategory'])->name('admin.categories.store');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'updateCategory'])->name('admin.categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroyCategory'])->name('admin.categories.destroy');
    Route::post('/subcategories', [AdminCategoryController::class, 'storeSubcategory'])->name('admin.subcategories.store');
    Route::delete('/subcategories/{subcategory}', [AdminCategoryController::class, 'destroySubcategory'])->name('admin.subcategories.destroy');

    // Admin Chat Monitoring Routes
    Route::get('/chats', [AdminChatController::class, 'index'])->name('admin.chats.index');

    // Student Management Routes
    Route::get('/students', [AdminController::class, 'studentsList'])->name('admin.students.index');
    Route::post('/students/{user}/reset-device', [AdminController::class, 'resetDevice'])->name('admin.students.resetDevice');
    Route::post('/students/{user}/toggle-status', [AdminController::class, 'toggleStatus'])->name('admin.students.toggleStatus');
    Route::post('/students/{user}/upgrade', [AdminController::class, 'upgradeStudentToInstructor'])->name('admin.students.upgrade');
    Route::post('/students/subscribe', [AdminController::class, 'subscribeStudentToCourse'])->name('admin.students.subscribe');

    // Settings Routes
    Route::get('/settings', [AdminController::class, 'settingsIndex'])->name('admin.settings.index');
    Route::post('/settings', [AdminController::class, 'settingsUpdate'])->name('admin.settings.update');
});

// Instructor Panel Routes
Route::prefix('instructor')->middleware(['auth', 'role:instructor|admin'])->group(function () {
    // 1. Dashboard Overview
    Route::get('/dashboard', [InstructorCourseController::class, 'dashboard'])->name('instructor.dashboard');

    // 2. Course Management
    Route::get('/courses', [InstructorCourseController::class, 'index'])->name('instructor.courses.index');
    Route::post('/courses', [InstructorCourseController::class, 'storeCourse'])->name('instructor.courses.store');
    Route::get('/courses/{course}/manage', [InstructorCourseController::class, 'manage'])->name('instructor.courses.manage');
    Route::get('/courses/{course}/analytics', [InstructorCourseController::class, 'courseAnalytics'])->name('instructor.courses.analytics');
    Route::put('/courses/{course}', [InstructorCourseController::class, 'updateCourse'])->name('instructor.courses.update');
    Route::delete('/courses/{course}', [InstructorCourseController::class, 'destroyCourse'])->name('instructor.courses.destroy');
    Route::post('/courses/{course}/publish', [InstructorCourseController::class, 'togglePublish'])->name('instructor.courses.publish');
    Route::post('/courses/{course}/sections', [InstructorCourseController::class, 'addSection'])->name('instructor.sections.store');
    Route::post('/sections/{section}/lessons/chunked', [InstructorCourseController::class, 'uploadChunkedLesson'])->name('instructor.lessons.chunked');
    Route::put('/lessons/{lesson}', [InstructorCourseController::class, 'updateLesson'])->name('instructor.lessons.update');
    Route::delete('/lessons/{lesson}', [InstructorCourseController::class, 'destroyLesson'])->name('instructor.lessons.destroy');
    Route::post('/courses/{course}/attachments', [InstructorCourseController::class, 'uploadAttachment'])->name('instructor.attachments.store');
    Route::delete('/attachments/{file}', [InstructorCourseController::class, 'destroyAttachment'])->name('instructor.attachments.destroy');
    Route::post('/courses/{course}/live-sessions', [InstructorCourseController::class, 'storeLiveSession'])->name('instructor.live-sessions.store');
    Route::delete('/live-sessions/{liveSession}', [InstructorCourseController::class, 'destroyLiveSession'])->name('instructor.live-sessions.destroy');

    // 3. Subscriptions & Codes
    Route::get('/subscriptions', [InstructorCourseController::class, 'subscriptionsIndex'])->name('instructor.subscriptions.index');
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

// Profile Management Routes for Admin & Instructor
Route::middleware(['auth', 'role:admin|instructor'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});
