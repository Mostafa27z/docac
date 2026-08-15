@extends('layouts.panel')

@section('title', 'لوحة المشرف العام - Doc Academy')
@section('role_title', 'لوحة المشرف العام')



@section('page_title', 'نظرة عامة')

@section('content')
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter mb-stack-lg">
        <div class="bg-surface-container-lowest border border-surface-container-highest rounded-xl p-stack-lg flex flex-col justify-between shadow-sm relative overflow-hidden group">
            <div class="flex justify-between items-start mb-4">
                <span class="text-on-surface-variant font-semibold text-sm">إجمالي الطلاب</span>
                <div class="p-2 bg-primary-container/10 rounded-lg text-primary">
                    <span class="material-symbols-outlined">group</span>
                </div>
            </div>
            <div>
                <h3 class="text-3xl font-bold text-primary mb-1">{{ $stats['students_count'] }}</h3>
                <div class="text-xs text-tertiary">طالب مسجل بالمنصة</div>
            </div>
        </div>
        <div class="bg-surface-container-lowest border border-surface-container-highest rounded-xl p-stack-lg flex flex-col justify-between shadow-sm relative overflow-hidden group">
            <div class="flex justify-between items-start mb-4">
                <span class="text-on-surface-variant font-semibold text-sm">إجمالي المدرسين</span>
                <div class="p-2 bg-primary-container/10 rounded-lg text-primary">
                    <span class="material-symbols-outlined">school</span>
                </div>
            </div>
            <div>
                <h3 class="text-3xl font-bold text-primary mb-1">{{ $stats['instructors_count'] }}</h3>
                <div class="text-xs text-tertiary">محاضر معتمد بالمنصة</div>
            </div>
        </div>
        <div class="bg-surface-container-lowest border border-surface-container-highest rounded-xl p-stack-lg flex flex-col justify-between shadow-sm relative overflow-hidden group">
            <div class="flex justify-between items-start mb-4">
                <span class="text-on-surface-variant font-semibold text-sm">إجمالي الكورسات</span>
                <div class="p-2 bg-primary-container/10 rounded-lg text-primary">
                    <span class="material-symbols-outlined">menu_book</span>
                </div>
            </div>
            <div>
                <h3 class="text-3xl font-bold text-primary mb-1">{{ $stats['courses_count'] }}</h3>
                <div class="text-xs text-tertiary">كورس مسجل</div>
            </div>
        </div>
        <div class="bg-surface-container-lowest border border-surface-container-highest rounded-xl p-stack-lg flex flex-col justify-between shadow-sm relative overflow-hidden group">
            <div class="flex justify-between items-start mb-4">
                <span class="text-on-surface-variant font-semibold text-sm">أكواد التفعيل</span>
                <div class="p-2 bg-primary-container/10 rounded-lg text-primary">
                    <span class="material-symbols-outlined">vpn_key</span>
                </div>
            </div>
            <div>
                <h3 class="text-3xl font-bold text-primary mb-1">{{ $stats['codes_generated'] }}</h3>
                <div class="text-xs text-tertiary">تم استخدام {{ $stats['codes_used'] }} كود</div>
            </div>
        </div>
    </div>

    <!-- Forms & Management Panel -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter">
        <div class="bg-surface-container-lowest border border-surface-container-highest rounded-xl p-gutter shadow-sm">
            <h2 class="text-xl font-bold text-primary mb-4">إنشاء حساب محاضر جديد</h2>
            <form action="{{ route('admin.instructors.create') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">الاسم بالكامل</label>
                    <input type="text" name="name" required class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary" placeholder="مثال: د. أحمد علي">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" required class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary" placeholder="مثال: ahmed@lms.com">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">رقم الهاتف</label>
                    <input type="text" name="phone" class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary" placeholder="مثال: 01000000000">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">كلمة المرور</label>
                    <input type="password" name="password" required class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary" placeholder="8 خانات على الأقل">
                </div>
                <button type="submit" class="bg-primary text-white font-bold px-6 py-3 rounded-lg hover:bg-primary-container transition-colors duration-150">إنشاء الحساب</button>
            </form>
        </div>

        <div class="bg-surface-container-lowest border border-surface-container-highest rounded-xl p-gutter shadow-sm">
            <h2 class="text-xl font-bold text-primary mb-4">توليد أكواد التفعيل للجملة</h2>
            <form action="{{ route('admin.codes.generate') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">اختر الكورس</label>
                    <select name="course_id" required class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary">
                        <option value="">-- اختر الكورس --</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">الكمية المطلوبة</label>
                    <input type="number" name="quantity" min="1" max="100" value="10" required class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary">
                </div>
                <button type="submit" class="bg-primary text-white font-bold px-6 py-3 rounded-lg hover:bg-primary-container transition-colors duration-150">توليد الأكواد</button>
            </form>
        </div>
    </div>
@endsection
