@extends('layouts.panel')

@section('title', 'الكورسات المخصصة لك - Doc Academy')
@section('role_title', 'لوحة المحاضر')
@section('page_title', 'إدارة المناهج والدروس')

@section('content')
    <x-page-header title="الكورسات المسندة إليك" subtitle="إدارة المناهج، المحاضرات، الفيديوهات والامتحانات للكورسات المسندة إليك من الإدارة." />

    {{-- Courses List --}}
    <x-card>
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[#E2E8F0]">
            <div class="p-2 rounded-xl bg-[#00A896]/10 text-[#00A896]">
                <i class="ph-bold ph-list-dashes text-lg"></i>
            </div>
            <h2 class="text-lg font-bold text-[#1A202C]">قائمة الكورسات المتاحة لإدارتك</h2>
        </div>

        <x-data-table :headers="['اسم الكورس', 'التصنيف', 'النوع والسعر', 'الحالة', 'الإجراءات']">
            @forelse($courses as $course)
                <tr class="border-b border-[#E2E8F0] hover:bg-[#F8F9FA] transition-colors">
                    <td class="py-4 px-4 font-semibold text-[#1A202C]">
                        <div class="flex items-center gap-3">
                            @if($course->thumbnail)
                                <img src="{{ $course->thumbnail_url }}" class="w-10 h-10 rounded-xl object-cover border border-[#E2E8F0]" onerror="this.src='/logo.jfif'" />
                            @else
                                <div class="w-10 h-10 rounded-xl bg-[#0047AB]/10 text-[#0047AB] flex items-center justify-center font-bold text-xs">
                                    <i class="ph-bold ph-book-bookmark text-lg"></i>
                                </div>
                            @endif
                            <div>
                                <span class="block font-bold text-[#1A202C] text-sm">{{ $course->title }}</span>
                                <span class="text-xs text-[#718096]">{{ Str::limit($course->description, 40) }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-4 text-[#718096] text-sm">
                        <div>{{ $course->category->name ?? '-' }}</div>
                        @if($course->subcategory)
                            <span class="inline-block bg-[#F8F9FA] border border-[#E2E8F0] px-2 py-0.5 rounded text-[11px] text-[#00A896] mt-0.5 font-medium">{{ $course->subcategory->name }}</span>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-sm">
                        <div class="font-bold text-[#1A202C]">{{ $course->price }} ج.م</div>
                        <span class="text-xs text-[#718096]">
                            @if($course->type === 'recorded') مسجل @elseif($course->type === 'live') بث مباشر @else هجين @endif
                        </span>
                    </td>
                    <td class="py-4 px-4">
                        @if($course->status === 'published')
                            <x-badge variant="success">منشور</x-badge>
                        @else
                            <x-badge variant="warning">مسودة</x-badge>
                        @endif
                    </td>
                    <td class="py-4 px-4">
                        <div class="flex gap-2">
                            <a href="{{ route('instructor.courses.manage', $course->id) }}" class="inline-flex items-center gap-1.5 bg-[#0047AB]/10 hover:bg-[#0047AB] text-[#0047AB] hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-200">
                                <i class="ph-bold ph-gear text-sm"></i>
                                إدارة المنهج والدروس
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-[#718096] text-sm">لا توجد كورسات مسندة إليك حالياً. يرجى التواصل مع الإدارة.</td>
                </tr>
            @endforelse
        </x-data-table>
    </x-card>
@endsection
