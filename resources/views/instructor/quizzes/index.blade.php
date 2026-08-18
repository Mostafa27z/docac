@extends('layouts.panel')

@section('title', 'الامتحانات والـ MCQs - Doc Academy')
@section('role_title', 'لوحة المحاضر')
@section('page_title', 'إدارة الاختبارات الطبية')

@section('content')
    <x-card>
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[#E2E8F0]">
            <div class="p-2.5 rounded-xl bg-[#00A896]/10 text-[#00A896]">
                <i class="ph-bold ph-exam text-xl"></i>
            </div>
            <h2 class="text-lg font-bold text-[#1A202C]">كافة امتحانات واختبارات الكورسات</h2>
        </div>

        <x-data-table :headers="['الكورس', 'الدرس المرتبط', 'الامتحان', 'نسبة النجاح', 'المحاولات المتاحة', 'الإجراءات']">
            @forelse($quizzes as $quiz)
                <tr class="border-b border-[#E2E8F0] hover:bg-[#F8F9FA] transition-colors">
                    <td class="py-4 px-4 text-[#1A202C] text-sm font-semibold">{{ $quiz->lesson->section->course->title }}</td>
                    <td class="py-4 px-4 text-[#718096] text-sm">{{ $quiz->lesson->title }}</td>
                    <td class="py-4 px-4 text-[#1A202C] text-sm font-medium">{{ $quiz->title }}</td>
                    <td class="py-4 px-4">
                        <x-badge variant="info">{{ $quiz->pass_percentage }}%</x-badge>
                    </td>
                    <td class="py-4 px-4 text-[#718096] text-sm">{{ $quiz->attempts_allowed ?? 'غير محدد' }}</td>
                    <td class="py-4 px-4">
                        <a href="{{ route('instructor.courses.manage', $quiz->lesson->section->course->id) }}#quiz-builder-{{ $quiz->lesson->id }}" class="inline-flex items-center gap-1.5 bg-[#0047AB]/10 hover:bg-[#0047AB] text-[#0047AB] hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-200">
                            <i class="ph-bold ph-pencil-simple text-sm"></i>
                            تعديل الأسئلة
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-[#718096] text-sm">لا توجد امتحانات مضافة حالياً. قم بإضافة امتحانات داخل الكورسات.</td>
                </tr>
            @endforelse
        </x-data-table>
    </x-card>
@endsection
