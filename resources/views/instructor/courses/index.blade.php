@extends('layouts.panel')

@section('title', 'إدارة الكورسات - Doc Academy')
@section('role_title', 'لوحة المحاضر')
@section('page_title', 'إدارة الكورسات والمناهج')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Create Course Form --}}
        <x-card class="lg:col-span-1 h-fit">
            <div class="flex items-center gap-3 mb-5">
                <div class="p-2.5 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                    <i class="ph-bold ph-plus-circle text-xl"></i>
                </div>
                <h2 class="text-lg font-bold text-[#1A202C]">إنشاء كورس جديد</h2>
            </div>
            <form action="{{ route('instructor.courses.store') }}" method="POST" class="space-y-4">
                @csrf
                <x-form-input label="عنوان الكورس" name="title" :required="true" placeholder="مثال: أساسيات تخطيط القلب" />
                <x-form-select label="نوع الكورس" name="type" :required="true">
                    <option value="recorded">محاضرات مسجلة فقط</option>
                    <option value="live">جلسات بث مباشر فقط</option>
                    <option value="mixed">هجين (مسجل + بث مباشر)</option>
                </x-form-select>
                <x-form-textarea label="وصف مختصر للكورس" name="description" :required="true" placeholder="اكتب نبذة عن محتوى وأهداف الكورس..." />
                <x-btn-primary icon="floppy-disk" class="w-full">حفظ مسودة الكورس</x-btn-primary>
            </form>
        </x-card>

        {{-- Courses List --}}
        <x-card class="lg:col-span-2">
            <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[#E2E8F0]">
                <div class="p-2 rounded-xl bg-[#00A896]/10 text-[#00A896]">
                    <i class="ph-bold ph-list-dashes text-lg"></i>
                </div>
                <h2 class="text-lg font-bold text-[#1A202C]">قائمة الكورسات المتاحة</h2>
            </div>

            <x-data-table :headers="['اسم الكورس', 'النوع', 'الحالة', 'الإجراءات']">
                @foreach($courses as $course)
                    <tr class="border-b border-[#E2E8F0] hover:bg-[#F8F9FA] transition-colors">
                        <td class="py-4 px-4 font-semibold text-[#1A202C]">{{ $course->title }}</td>
                        <td class="py-4 px-4 text-[#718096] text-sm">
                            @if($course->type === 'recorded') دروس مسجلة @elseif($course->type === 'live') بث مباشر @else هجين @endif
                        </td>
                        <td class="py-4 px-4">
                            @if($course->status === 'published')
                                <x-badge variant="success">منشور</x-badge>
                            @else
                                <x-badge variant="warning">مسودة</x-badge>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            <a href="{{ route('instructor.courses.manage', $course->id) }}" class="inline-flex items-center gap-1.5 bg-[#0047AB]/10 hover:bg-[#0047AB] text-[#0047AB] hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-200">
                                <i class="ph-bold ph-gear text-sm"></i>
                                إدارة المنهج
                            </a>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </x-card>
    </div>
@endsection
