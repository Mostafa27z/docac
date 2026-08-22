@extends('layouts.panel')

@section('title', 'متابعة نتايج وتقدم الطلاب - ' . $course->title)
@section('role_title', auth()->user()->role === 'admin' ? 'لوحة المشرف العام' : 'لوحة المحاضر')

@section('page_title')
    <div class="flex justify-between items-center w-full">
        <span>متابعة نتائج وتقدم الطلاب: {{ $course->title }}</span>
        <a href="{{ route('instructor.courses.manage', $course->id) }}" class="inline-flex items-center gap-1.5 bg-[#F8F9FA] border border-[#E2E8F0] text-[#4A5568] hover:bg-[#E2E8F0] text-xs font-semibold px-4 py-2 rounded-xl transition-all">
            <i class="ph-bold ph-arrow-right text-sm"></i>
            العودة لصفحة المنهج
        </a>
    </div>
@endsection

@section('content')
    <x-page-header title="متابعة نتائج وتقييمات الطلاب" subtitle="تقرير تفصيلي بمعدلات إنجاز الطلاب ونتائج الامتحانات في هذا الكورس." />

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-stat-card icon="users" label="إجمالي الطلاب المشتركين" :value="$enrollments->count()" color="primary" />
        <x-stat-card icon="book-open" label="إجمالي الدروس بالكورس" :value="$totalLessons" color="teal" />
        <x-stat-card icon="chart-line-up" label="متوسط نسبة إنجاز الطلاب" :value="round($enrollments->avg('progress_percentage') ?? 0, 1) . '%'" color="ocean" />
    </div>

    {{-- Analytics Table --}}
    <x-card>
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[#E2E8F0]">
            <div class="p-2 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                <i class="ph-bold ph-user-check text-lg"></i>
            </div>
            <h3 class="font-bold text-[#1A202C]">جدول تقدم ونتائج الطلاب</h3>
        </div>

        <x-data-table :headers="['اسم الطالب والمعلومات', 'تاريخ الاشتراك', 'نسبة الإنجاز (معدل المشاهدة)', 'نتائج الامتحانات (Quiz Scores)']">
            @forelse($enrollments as $item)
                <tr class="border-b border-[#E2E8F0] hover:bg-[#F8F9FA] transition-colors">
                    <td class="py-4 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#0047AB] to-[#00A896] flex items-center justify-center text-white font-bold text-sm">
                                {{ mb_substr($item['student']->name, 0, 1) }}
                            </div>
                            <div>
                                <span class="font-bold text-[#1A202C] text-sm block">{{ $item['student']->name }}</span>
                                <span class="text-xs text-[#718096]">{{ $item['student']->email }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-4 text-[#718096] text-sm">
                        {{ $item['enrollment']->enrolled_at ? $item['enrollment']->enrolled_at->format('Y-m-d') : '-' }}
                    </td>
                    <td class="py-4 px-4">
                        <div class="w-48">
                            <div class="flex justify-between text-xs font-semibold mb-1">
                                <span class="text-[#718096]">{{ $item['completed_lessons_count'] }} من {{ $item['total_lessons_count'] }} درس</span>
                                <span class="text-[#0047AB] font-bold">{{ round($item['progress_percentage'], 1) }}%</span>
                            </div>
                            <div class="w-full bg-[#E2E8F0] rounded-full h-2 overflow-hidden">
                                <div class="bg-gradient-to-r from-[#0047AB] to-[#00A896] h-full rounded-full" style="width: {{ min(100, $item['progress_percentage']) }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-4">
                        <div class="space-y-1.5 max-w-sm">
                            @forelse($item['quiz_attempts'] as $attempt)
                                <div class="flex items-center justify-between bg-white border border-[#E2E8F0] px-3 py-1.5 rounded-xl text-xs">
                                    <span class="font-medium text-[#1A202C] truncate max-w-[160px]" title="{{ $attempt->quiz->title ?? 'امتحان' }}">
                                        {{ $attempt->quiz->title ?? 'امتحان' }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-[#0047AB]">{{ (float)$attempt->score }}%</span>
                                        @if($attempt->passed)
                                            <x-badge variant="success">ناجح</x-badge>
                                        @else
                                            <x-badge variant="error">راسب</x-badge>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <span class="text-xs text-[#718096] italic">لم يخضع لأي امتحانات حتى الآن.</span>
                            @endforelse
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-8 text-center text-[#718096]">
                        <x-empty-state icon="chart-line-up" title="لا يوجد طلاب مشتركين بعد" description="عند انضمام الطلاب سيتم عرض معدلات إنجازهم ونتائج امتحاناتهم هنا." />
                    </td>
                </tr>
            @endforelse
        </x-data-table>
    </x-card>
@endsection
