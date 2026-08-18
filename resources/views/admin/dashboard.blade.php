@extends('layouts.panel')

@section('title', 'لوحة المشرف العام - Doc Academy')
@section('role_title', 'لوحة المشرف العام')
@section('page_title', 'نظرة عامة')

@section('content')
    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stat-card icon="users-three" label="إجمالي الطلاب" :value="$stats['students_count']" subtitle="طالب مسجل بالمنصة" color="primary" />
        <x-stat-card icon="chalkboard-teacher" label="إجمالي المدرسين" :value="$stats['instructors_count']" subtitle="محاضر معتمد بالمنصة" color="teal" />
        <x-stat-card icon="book-open-text" label="إجمالي الكورسات" :value="$stats['courses_count']" subtitle="كورس مسجل" color="ocean" />
        <x-stat-card icon="key" label="أكواد التفعيل" :value="$stats['codes_generated']" :subtitle="'تم استخدام ' . $stats['codes_used'] . ' كود'" color="cyan" />
    </div>

    {{-- Forms & Management --}}
    <div class="max-w-xl mx-auto">
        {{-- Create Instructor Form --}}
        <x-card>
            <div class="flex items-center gap-3 mb-5">
                <div class="p-2.5 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                    <i class="ph-bold ph-user-plus text-xl"></i>
                </div>
                <h2 class="text-lg font-bold text-[#1A202C]">إنشاء حساب محاضر جديد</h2>
            </div>
            <form action="{{ route('admin.instructors.create') }}" method="POST" class="space-y-4">
                @csrf
                <x-form-input label="الاسم بالكامل" name="name" :required="true" placeholder="مثال: د. أحمد علي" />
                <x-form-input label="البريد الإلكتروني" name="email" type="email" :required="true" placeholder="مثال: ahmed@lms.com" />
                <x-form-input label="رقم الهاتف" name="phone" placeholder="مثال: 01000000000" />
                <x-form-input label="كلمة المرور" name="password" type="password" :required="true" placeholder="8 خانات على الأقل" />
                <x-btn-primary icon="user-plus" class="w-full">إنشاء الحساب</x-btn-primary>
            </form>
        </x-card>
    </div>
@endsection
