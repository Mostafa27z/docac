@extends('layouts.panel')

@section('title', 'لوحة المحاضر - Doc Academy')
@section('role_title', 'لوحة المحاضر')
@section('page_title', 'لوحة القيادة')

@section('content')
    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-stat-card icon="book-open-text" label="الكورسات الخاصة بي" :value="$stats['my_courses_count']" subtitle="كورسات مسجلة ونشطة" color="primary" />
        <x-stat-card icon="users-three" label="إجمالي الطلاب المشتركين" :value="$stats['students_count']" subtitle="طالب ملتحق بمساراتي" color="teal" />
        <x-stat-card icon="video-camera" label="بث مباشر مجدول" :value="$stats['live_sessions_count']" subtitle="محاضرات بث قادمة" color="ocean" />
    </div>

    {{-- Quick Links --}}
    <x-card>
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                <i class="ph-bold ph-link text-lg"></i>
            </div>
            <h2 class="text-lg font-bold text-[#1A202C]">روابط سريعة لوحدات التحكم</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <a href="{{ route('instructor.courses.index') }}" class="group p-6 bg-[#F8F9FA] border border-[#E2E8F0] rounded-2xl hover:border-[#0047AB]/30 hover:shadow-md transition-all text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-[#0047AB]/10 text-[#0047AB] flex items-center justify-center mb-4 group-hover:bg-[#0047AB] group-hover:text-white transition-all">
                    <i class="ph-bold ph-book-open-text text-2xl"></i>
                </div>
                <h3 class="font-bold text-[#1A202C] mb-1">إدارة المناهج والكورسات</h3>
                <p class="text-xs text-[#718096]">إنشاء دورات ورفع الفيديوهات الدراسية</p>
            </a>
            <a href="{{ route('instructor.subscriptions.index') }}" class="group p-6 bg-[#F8F9FA] border border-[#E2E8F0] rounded-2xl hover:border-[#00A896]/30 hover:shadow-md transition-all text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-[#00A896]/10 text-[#00A896] flex items-center justify-center mb-4 group-hover:bg-[#00A896] group-hover:text-white transition-all">
                    <i class="ph-bold ph-key text-2xl"></i>
                </div>
                <h3 class="font-bold text-[#1A202C] mb-1">المشتركين وأكواد التفعيل</h3>
                <p class="text-xs text-[#718096]">توليد أكواد التسجيل ومتابعة الطلاب</p>
            </a>
            <a href="{{ route('instructor.quizzes.index') }}" class="group p-6 bg-[#F8F9FA] border border-[#E2E8F0] rounded-2xl hover:border-[#0088CC]/30 hover:shadow-md transition-all text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-[#0088CC]/10 text-[#0088CC] flex items-center justify-center mb-4 group-hover:bg-[#0088CC] group-hover:text-white transition-all">
                    <i class="ph-bold ph-exam text-2xl"></i>
                </div>
                <h3 class="font-bold text-[#1A202C] mb-1">الامتحانات والـ MCQs</h3>
                <p class="text-xs text-[#718096]">إضافة أسئلة الاختبار والتقييمات</p>
            </a>
        </div>
    </x-card>
@endsection
