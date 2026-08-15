@extends('layouts.panel')

@section('title', 'إدارة الكورسات - Doc Academy')
@section('role_title', 'لوحة المحاضر')

@section('page_title', 'إدارة الكورسات والمناهج')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">
        <!-- Create Course Form -->
        <div class="lg:col-span-1 bg-surface-container-lowest border border-surface-container-highest rounded-xl p-gutter shadow-sm h-fit">
            <h2 class="text-xl font-bold text-primary mb-4">إنشاء كورس جديد (مسودة)</h2>
            <form action="{{ route('instructor.courses.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">عنوان الكورس</label>
                    <input type="text" name="title" required class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary" placeholder="مثال: أساسيات تخطيط القلب">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">نوع الكورس</label>
                    <select name="type" required class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary">
                        <option value="recorded">محاضرات مسجلة فقط</option>
                        <option value="live">جلسات بث مباشر فقط</option>
                        <option value="mixed">هجين (مسجل + بث مباشر)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface-variant mb-1">وصف مختصر للكورس</label>
                    <textarea name="description" rows="4" required class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-2 text-on-surface focus:outline-none focus:border-primary" placeholder="اكتب نبذة عن محتوى وأهداف الكورس..."></textarea>
                </div>
                <button type="submit" class="w-full bg-primary text-white font-bold px-6 py-3 rounded-lg hover:bg-primary-container transition-colors duration-150">حفظ مسودة الكورس</button>
            </form>
        </div>

        <!-- Instructor Courses List -->
        <div class="lg:col-span-2 bg-surface-container-lowest border border-surface-container-highest rounded-xl p-gutter shadow-sm">
            <h2 class="text-xl font-bold text-primary mb-4">قائمة الكورسات المتاحة</h2>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-surface-container-highest text-right">
                            <th class="py-3 text-on-surface-variant font-semibold text-sm">اسم الكورس</th>
                            <th class="py-3 text-on-surface-variant font-semibold text-sm">النوع</th>
                            <th class="py-3 text-on-surface-variant font-semibold text-sm">الحالة</th>
                            <th class="py-3 text-on-surface-variant font-semibold text-sm">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courses as $course)
                            <tr class="border-b border-surface-container-low hover:bg-surface-container-lowest">
                                <td class="py-4 text-on-surface font-medium">{{ $course->title }}</td>
                                <td class="py-4 text-on-surface-variant text-sm">
                                    @if($course->type === 'recorded') دروس مسجلة @elseif($course->type === 'live') بث مباشر @else هجين @endif
                                </td>
                                <td class="py-4 text-sm font-medium">
                                    @if($course->status === 'published')
                                        <span class="text-green-600">منشور</span>
                                    @else
                                        <span class="text-amber-600">مسودة</span>
                                    @endif
                                </td>
                                <td class="py-4">
                                    <a href="{{ route('instructor.courses.manage', $course->id) }}" class="inline-flex items-center bg-primary-fixed hover:bg-primary-fixed-dim text-on-primary-fixed-variant text-xs font-bold px-4 py-2 rounded-lg transition-colors">
                                        إدارة المنهج والدروس 🎬
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
