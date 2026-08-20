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
                            <div class="flex gap-2">
                                <a href="{{ route('instructor.courses.manage', $course->id) }}" class="inline-flex items-center gap-1.5 bg-[#0047AB]/10 hover:bg-[#0047AB] text-[#0047AB] hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-200">
                                    <i class="ph-bold ph-gear text-sm"></i>
                                    إدارة المنهج
                                </a>
                                <button type="button" onclick="openDeleteCourseModal({{ $course->id }}, '{{ addslashes($course->title) }}')" class="inline-flex items-center gap-1.5 bg-red-600/10 hover:bg-red-600 text-red-600 hover:text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-200">
                                    <i class="ph-bold ph-trash text-sm"></i>
                                    حذف الكورس
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </x-card>
    </div>

    {{-- Delete Course Modal --}}
    <div id="delete-course-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-md p-6 relative shadow-2xl">
            <h3 class="text-lg font-bold mb-2 text-right text-red-600 flex items-center gap-2">
                <i class="ph-bold ph-warning"></i>
                حذف الكورس نهائياً
            </h3>
            <p class="text-sm text-[#718096] mb-4">
                أنت على وشك حذف الكورس: <strong id="delete-course-title" class="text-[#1A202C]"></strong>.
                إن حذف الكورس سيؤدي إلى مسح جميع الأقسام، الدروس، المرفقات، والامتحانات المرتبطة به. لا يمكن استعادة هذه البيانات بعد الحذف.
            </p>
            <form id="delete-course-form" action="" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')
                <x-form-input label="يرجى إدخال كلمة المرور الخاصة بك لتأكيد الحذف" name="password" type="password" :required="true" placeholder="كلمة المرور الحالية" />

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeDeleteCourseModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <button type="submit" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">
                        <i class="ph-bold ph-trash"></i>
                        حذف الكورس نهائياً
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openDeleteCourseModal(courseId, courseTitle) {
        document.getElementById('delete-course-form').action = `/instructor/courses/${courseId}`;
        document.getElementById('delete-course-title').innerText = courseTitle;
        document.getElementById('delete-course-modal').style.display = 'flex';
    }
    function closeDeleteCourseModal() {
        document.getElementById('delete-course-modal').style.display = 'none';
    }
    </script>
    @endpush
@endsection
