@extends('layouts.panel')

@section('title', 'لوحة المحاضر - Doc Academy')
@section('role_title', 'لوحة المحاضر')

@section('page_title', 'لوحة قيادة المدرس')

@section('content')
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-stack-lg">
        <div class="bg-surface-container-lowest border border-surface-container-highest rounded-xl p-stack-lg flex flex-col justify-between shadow-sm relative overflow-hidden group">
            <div class="flex justify-between items-start mb-4">
                <span class="text-on-surface-variant font-semibold text-sm">الكورسات الخاصة بي</span>
                <div class="p-2 bg-primary-container/10 rounded-lg text-primary">
                    <span class="material-symbols-outlined">menu_book</span>
                </div>
            </div>
            <div>
                <h3 class="text-3xl font-bold text-primary mb-1">{{ $stats['my_courses_count'] }}</h3>
                <div class="text-xs text-tertiary">كورسات مسجلة ونشطة</div>
            </div>
        </div>
        <div class="bg-surface-container-lowest border border-surface-container-highest rounded-xl p-stack-lg flex flex-col justify-between shadow-sm relative overflow-hidden group">
            <div class="flex justify-between items-start mb-4">
                <span class="text-on-surface-variant font-semibold text-sm">إجمالي الطلاب المشتركين</span>
                <div class="p-2 bg-primary-container/10 rounded-lg text-primary">
                    <span class="material-symbols-outlined">group</span>
                </div>
            </div>
            <div>
                <h3 class="text-3xl font-bold text-primary mb-1">{{ $stats['students_count'] }}</h3>
                <div class="text-xs text-tertiary">طالب ملتحق بمساراتي</div>
            </div>
        </div>
        <div class="bg-surface-container-lowest border border-surface-container-highest rounded-xl p-stack-lg flex flex-col justify-between shadow-sm relative overflow-hidden group">
            <div class="flex justify-between items-start mb-4">
                <span class="text-on-surface-variant font-semibold text-sm">بث مباشر مجدول</span>
                <div class="p-2 bg-primary-container/10 rounded-lg text-primary">
                    <span class="material-symbols-outlined">video_camera_front</span>
                </div>
            </div>
            <div>
                <h3 class="text-3xl font-bold text-primary mb-1">{{ $stats['live_sessions_count'] }}</h3>
                <div class="text-xs text-tertiary">محاضرات بث قادمة</div>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts Panel -->
    <div class="bg-surface-container-lowest border border-surface-container-highest rounded-xl p-gutter shadow-sm">
        <h2 class="text-xl font-bold text-primary mb-4">روابط سريعة لوحدات التحكم</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('instructor.courses.index') }}" class="p-6 bg-surface border border-outline-variant/30 rounded-xl hover:bg-primary/5 transition-all text-center">
                <span class="material-symbols-outlined text-4xl text-primary mb-2">menu_book</span>
                <h3 class="font-bold text-on-surface">إدارة المناهج والكورسات</h3>
                <p class="text-xs text-secondary mt-1">إنشاء دورات ورفع الفيديوهات الدراسية</p>
            </a>
            <a href="{{ route('instructor.subscriptions.index') }}" class="p-6 bg-surface border border-outline-variant/30 rounded-xl hover:bg-primary/5 transition-all text-center">
                <span class="material-symbols-outlined text-4xl text-primary mb-2">vpn_key</span>
                <h3 class="font-bold text-on-surface">المشتركين وأكواد التفعيل</h3>
                <p class="text-xs text-secondary mt-1">توليد أكواد التسجيل ومتابعة الطلاب</p>
            </a>
            <a href="{{ route('instructor.quizzes.index') }}" class="p-6 bg-surface border border-outline-variant/30 rounded-xl hover:bg-primary/5 transition-all text-center">
                <span class="material-symbols-outlined text-4xl text-primary mb-2">quiz</span>
                <h3 class="font-bold text-on-surface">الامتحانات والـ MCQs</h3>
                <p class="text-xs text-secondary mt-1">إضافة أسئلة الاختبار والتقييمات</p>
            </a>
        </div>
    </div>
@endsection
