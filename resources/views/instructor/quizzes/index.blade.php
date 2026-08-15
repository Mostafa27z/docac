@extends('layouts.panel')

@section('title', 'الامتحانات والـ MCQs - Doc Academy')
@section('role_title', 'لوحة المحاضر')

@section('page_title', 'إدارة الاختبارات الطبية')

@section('content')
    <div class="bg-surface-container-lowest border border-surface-container-highest rounded-xl p-gutter shadow-sm">
        <h2 class="text-xl font-bold text-primary mb-4">كافة امتحانات واختبارات الكورسات</h2>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="border-b border-surface-container-highest text-right">
                        <th class="py-3 text-on-surface-variant font-semibold text-sm">الكورس</th>
                        <th class="py-3 text-on-surface-variant font-semibold text-sm">الدرس المرتبط</th>
                        <th class="py-3 text-on-surface-variant font-semibold text-sm">الامتحان</th>
                        <th class="py-3 text-on-surface-variant font-semibold text-sm">نسبة النجاح</th>
                        <th class="py-3 text-on-surface-variant font-semibold text-sm">المحاولات المتاحة</th>
                        <th class="py-3 text-on-surface-variant font-semibold text-sm">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quizzes as $quiz)
                        <tr class="border-b border-surface-container-low hover:bg-surface-container-lowest">
                            <td class="py-4 text-on-surface text-sm font-semibold">{{ $quiz->lesson->section->course->title }}</td>
                            <td class="py-4 text-on-surface-variant text-sm">{{ $quiz->lesson->title }}</td>
                            <td class="py-4 text-on-surface text-sm font-medium">{{ $quiz->title }}</td>
                            <td class="py-4 text-on-surface-variant text-sm">{{ $quiz->pass_percentage }}%</td>
                            <td class="py-4 text-on-surface-variant text-sm">{{ $quiz->attempts_allowed ?? 'غير محدد' }}</td>
                            <td class="py-4">
                                <a href="{{ route('instructor.courses.manage', $quiz->lesson->section->course->id) }}#quiz-builder-{{ $quiz->lesson->id }}" class="inline-flex items-center bg-primary-fixed hover:bg-primary-fixed-dim text-on-primary-fixed-variant text-xs font-bold px-4 py-2 rounded-lg transition-colors">
                                    تعديل الأسئلة والـ MCQ 📝
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-on-surface-variant text-sm">لا توجد امتحانات مضافة حالياً. قم بإضافة امتحانات داخل الكورسات.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
