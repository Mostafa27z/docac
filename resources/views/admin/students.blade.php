@extends('layouts.panel')

@section('title', 'إدارة الطلاب - Doc Academy')
@section('role_title', 'لوحة المشرف العام')
@section('page_title')
    <div class="flex justify-between items-center w-full flex-wrap gap-2">
        <span>إدارة الطلاب</span>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openBulkSubscribeModal()" class="inline-flex items-center gap-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all shadow-sm">
                <i class="ph-bold ph-users-three text-sm"></i>
                اشتراك جماعي متعدد للكورسات
            </button>
            <button onclick="openModal('add-student-modal')" class="inline-flex items-center gap-1.5 bg-[#0047AB] hover:bg-[#003B91] text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all shadow-sm">
                <i class="ph-bold ph-user-plus text-sm"></i>
                إضافة طالب جديد
            </button>
        </div>
    </div>
@endsection

@section('content')
    <x-page-header title="إدارة الطلاب" subtitle="عرض بيانات الطلاب المسجلين بالمنصة وإلغاء ارتباط الأجهزة والاشتراكات الفردية والجماعية.">
        <x-slot name="actions">
            {{-- Search inline --}}
            <form action="{{ route('admin.students.index') }}" method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث باسم الطالب، البريد، الهاتف..."
                       class="bg-white border border-[#E2E8F0] rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB] transition-all">
                <x-btn-primary icon="magnifying-glass" type="submit">بحث</x-btn-primary>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.students.index') }}" class="inline-flex items-center justify-center bg-[#F8F9FA] border border-[#E2E8F0] text-[#4A5568] px-4 py-2 rounded-xl text-sm hover:bg-[#E2E8F0] transition-all">إلغاء</a>
                @endif
            </form>
        </x-slot>
    </x-page-header>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border-r-4 border-red-500 rounded-xl text-sm text-red-600 font-medium">
            <ul class="list-disc mr-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Manual Course Subscription Card --}}
    <x-card class="mb-8">
        <div class="flex items-center justify-between flex-wrap gap-3 mb-5">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-purple-600/10 text-purple-600">
                    <i class="ph-bold ph-keyholder text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-[#1A202C]">تسجيل واشتراك طالب يدوي في كورس</h2>
                    <p class="text-xs text-[#718096]">تفعيل اشتراك فوري لطالب مسجل في كورس معين، أو استخدام الاشتراك الجماعي لعدة طلاب/كورسات.</p>
                </div>
            </div>
            <button type="button" onclick="openBulkSubscribeModal()" class="inline-flex items-center gap-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1.5 rounded-xl border border-purple-200 transition-all">
                <i class="ph-bold ph-stack-plus text-sm"></i>
                فتح نافذة الاشتراك الجماعي (Bulk)
            </button>
        </div>

        <form action="{{ route('admin.students.subscribe') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            @csrf
            <div>
                <x-form-input label="البريد الإلكتروني للطالب" name="email" type="email" :required="true" placeholder="student@gmail.com" />
            </div>
            <div>
                <x-form-select label="اختر الكورس المستهدف" name="course_id" :required="true">
                    <option value="">-- اختر الكورس --</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}@if($course->category_hierarchy) [{{ $course->category_hierarchy }}]@endif ({{ $course->price }} ج.م)</option>
                    @endforeach
                </x-form-select>
            </div>
            <div>
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-[#0047AB] hover:bg-[#003B91] text-white font-semibold px-5 py-2.5 rounded-xl transition-all duration-200 text-sm">
                    <i class="ph-bold ph-user-plus text-lg"></i>
                    <span>تسجيل وتفعيل الاشتراك فورا</span>
                </button>
            </div>
        </form>
    </x-card>

    {{-- Students Table --}}
    <x-card>
        <div class="flex items-center justify-between flex-wrap gap-3 mb-5 pb-4 border-b border-[#E2E8F0]">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                    <i class="ph-bold ph-users text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-[#1A202C]">قائمة الطلاب المسجلين</h3>
                    <p class="text-xs text-[#718096]">يمكنك تحديد عدة طلاب عبر مربعات الاختيار ثم النقر على زر الاشتراك الجماعي.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs text-[#718096]">
                <span>إجمالي الطلاب: <strong class="text-[#1A202C]">{{ $students->total() }}</strong></span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-[#F8F9FA] text-[#4A5568] text-xs font-bold border-b border-[#E2E8F0]">
                        <th class="py-3 px-4 w-10 text-center">
                            <input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll(this)" title="تحديد / إلغاء تحديد الكل في هذه الصفحة" class="w-4 h-4 rounded border-[#CBD5E0] text-[#0047AB] focus:ring-[#0047AB] cursor-pointer">
                        </th>
                        <th class="py-3 px-4">الاسم والمعلومات</th>
                        <th class="py-3 px-4">البريد الإلكتروني</th>
                        <th class="py-3 px-4">الهاتف</th>
                        <th class="py-3 px-4">معرّف وسياسة الجهاز</th>
                        <th class="py-3 px-4">الحالة</th>
                        <th class="py-3 px-4">تاريخ التسجيل</th>
                        <th class="py-3 px-4 text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr class="border-b border-[#E2E8F0] hover:bg-[#F8F9FA] transition-colors" id="student-row-{{ $student->id }}">
                            <td class="py-4 px-4 text-center">
                                <input type="checkbox" class="student-row-checkbox w-4 h-4 rounded border-[#CBD5E0] text-[#0047AB] focus:ring-[#0047AB] cursor-pointer"
                                       value="{{ $student->id }}"
                                       data-name="{{ $student->name }}"
                                       data-email="{{ $student->email }}"
                                       onchange="handleRowCheckboxChange()">
                            </td>
                            <td class="py-4 px-4 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#0047AB] to-[#00A896] flex items-center justify-center text-white font-bold text-sm">
                                    {{ mb_substr($student->name, 0, 1) }}
                                </div>
                                <span class="font-semibold text-[#1A202C]">{{ $student->name }}</span>
                            </td>
                            <td class="py-4 px-4 text-[#718096] text-sm">{{ $student->email }}</td>
                            <td class="py-4 px-4 text-[#718096] text-sm">{{ $student->phone ?? '-' }}</td>
                            <td class="py-4 px-4 text-sm">
                                @if($student->allow_multiple_devices)
                                    <div class="flex flex-col gap-1.5 items-start">
                                        <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 text-xs font-semibold px-2.5 py-1 rounded-lg border border-purple-200">
                                            <i class="ph-bold ph-devices text-sm"></i>
                                            أجهزة متعددة مسموحة
                                        </span>
                                        @if($student->active_device_id)
                                            <span class="font-mono text-[11px] text-[#718096] select-all max-w-[180px] truncate" title="{{ $student->active_device_id }}">
                                                آخر جهاز: {{ $student->active_device_id }}
                                            </span>
                                        @endif
                                    </div>
                                @elseif($student->active_device_id)
                                    <div class="flex flex-col gap-1.5 items-start">
                                        <span class="inline-flex items-center gap-1 bg-blue-50 text-[#0047AB] text-xs font-semibold px-2.5 py-1 rounded-lg border border-blue-200">
                                            <i class="ph-bold ph-device-mobile text-sm"></i>
                                            مقيد بجهاز واحد
                                        </span>
                                        <span class="font-mono text-xs text-[#0047AB] bg-[#0047AB]/5 px-2 py-0.5 rounded border border-[#0047AB]/10 select-all max-w-[180px] truncate" title="{{ $student->active_device_id }}">
                                            {{ $student->active_device_id }}
                                        </span>
                                        <form action="{{ route('admin.students.resetDevice', $student->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في إلغاء ارتباط هذا الجهاز؟ سيتمكن الطالب من تسجيل الدخول من جهاز آخر.')">
                                            @csrf
                                            <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 flex items-center gap-1 transition-colors">
                                                <i class="ph-bold ph-device-mobile-slash"></i>
                                                <span>إلغاء ارتباط الجهاز</span>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-600 text-xs font-semibold px-2.5 py-1 rounded-lg border border-gray-200">
                                        <i class="ph-bold ph-device-mobile text-sm"></i>
                                        مقيد بجهاز واحد (لم يرتبط بعد)
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($student->status === 'active')
                                    <x-badge variant="success">نشط</x-badge>
                                @else
                                    <x-badge variant="error">موقوف</x-badge>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-[#718096] text-sm">{{ $student->created_at->format('Y-m-d') }}</td>
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2 justify-center">
                                    {{-- View Profile --}}
                                    <a href="{{ route('admin.students.show', $student->id) }}" class="inline-flex items-center gap-1 bg-[#0047AB]/10 hover:bg-[#0047AB]/20 text-[#0047AB] text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                        <i class="ph-bold ph-eye text-sm"></i>
                                        <span>الملف الشخصي</span>
                                    </a>

                                    {{-- Multi-device quick toggle --}}
                                    <form action="{{ route('admin.students.toggleMultiDevice', $student->id) }}" method="POST">
                                        @csrf
                                        @if($student->allow_multiple_devices)
                                            <button type="submit" title="تقييد الحساب بجهاز واحد فقط" class="inline-flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold px-2.5 py-1.5 rounded-xl border border-gray-300 transition-all">
                                                <i class="ph-bold ph-lock-key text-sm"></i>
                                                <span>تقييد بجهاز</span>
                                            </button>
                                        @else
                                            <button type="submit" title="السماح بالدخول من أجهزة متعددة" class="inline-flex items-center gap-1 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-semibold px-2.5 py-1.5 rounded-xl border border-purple-200 transition-all">
                                                <i class="ph-bold ph-devices text-sm"></i>
                                                <span>سماح متعدد</span>
                                            </button>
                                        @endif
                                    </form>

                                    {{-- Toggle Status --}}
                                    <form action="{{ route('admin.students.toggleStatus', $student->id) }}" method="POST">
                                        @csrf
                                        @if($student->status === 'active')
                                            <button type="submit" class="inline-flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold px-3 py-1.5 rounded-xl border border-amber-200 transition-all">
                                                <i class="ph-bold ph-prohibit-sidebar text-sm"></i>
                                                <span>إيقاف</span>
                                            </button>
                                        @else
                                            <button type="submit" class="inline-flex items-center gap-1 bg-[#2EC4B6]/10 hover:bg-[#2EC4B6]/20 text-[#00A896] text-xs font-semibold px-3 py-1.5 rounded-xl border border-[#2EC4B6]/20 transition-all">
                                                <i class="ph-bold ph-check text-sm"></i>
                                                <span>تنشيط</span>
                                            </button>
                                        @endif
                                    </form>

                                    {{-- Upgrade to Instructor --}}
                                    <form action="{{ route('admin.students.upgrade', $student->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من ترقية هذا الطالب ليصبح محاضراً في المنصة؟')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1.5 rounded-xl border border-purple-200 transition-all">
                                            <i class="ph-bold ph-arrow-up-right text-sm"></i>
                                            <span>ترقية</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-[#718096]">
                                <x-empty-state icon="student" title="لا يوجد طلاب مسجلين" description="لم يتم العثور على أي طلاب في النظام حالياً." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $students->appends(request()->query())->links() }}
        </div>
    </x-card>

    {{-- Floating Action Bar for Selected Students --}}
    <div id="bulk-floating-bar" style="display: none;" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-gradient-to-r from-[#1A202C] to-[#2D3748] text-white px-6 py-3.5 rounded-2xl shadow-2xl border border-white/10 flex items-center gap-4 transition-all duration-300">
        <div class="flex items-center gap-2">
            <span class="w-7 h-7 rounded-full bg-[#0047AB] flex items-center justify-center font-bold text-xs" id="floating-selected-count">0</span>
            <span class="text-sm font-semibold">طلاب محددين</span>
        </div>
        <div class="h-5 w-px bg-white/20"></div>
        <button type="button" onclick="openBulkSubscribeModalWithSelected()" class="inline-flex items-center gap-2 bg-gradient-to-r from-[#0047AB] to-[#0088CC] hover:brightness-110 text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-md">
            <i class="ph-bold ph-keyholder text-sm"></i>
            <span>تفعيل اشتراك في كورسات محددة</span>
        </button>
        <button type="button" onclick="clearSelectedStudents()" class="text-xs text-gray-400 hover:text-white transition-colors">
            إلغاء التحديد
        </button>
    </div>

    {{-- Add Single Student Modal --}}
    <x-modal id="add-student-modal" title="إضافة حساب طالب جديد" icon="user-plus">
        <form action="{{ route('admin.students.store') }}" method="POST" class="space-y-4">
            @csrf
            <x-form-input label="الاسم بالكامل" name="name" :required="true" placeholder="مثال: أحمد محمد" />
            <x-form-input label="البريد الإلكتروني" name="email" type="email" :required="true" placeholder="student@gmail.com" />
            <x-form-input label="رقم الهاتف" name="phone" placeholder="مثال: 01012345678" />
            <x-form-input label="كلمة المرور" name="password" type="password" :required="true" placeholder="8 خانات على الأقل" />

            {{-- Multi-Device Option --}}
            <div class="p-3.5 bg-[#F8F9FA] rounded-xl border border-[#E2E8F0] transition-colors hover:border-[#0047AB]/30">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="allow_multiple_devices" value="1" class="mt-1 w-4 h-4 text-[#0047AB] rounded border-[#CBD5E0] focus:ring-[#0047AB]">
                    <div class="flex-1">
                        <span class="block text-sm font-bold text-[#1A202C] flex items-center gap-1.5">
                            <i class="ph-bold ph-devices text-[#0047AB]"></i>
                            السماح بالدخول من أجهزة متعددة (Multiple Devices)
                        </span>
                        <span class="block text-xs text-[#718096] mt-1 leading-relaxed">
                            تفعيل هذا الخيار يسمح للطالب بفتح حسابه على أكثر من جهاز (هواتف، تابلت، كمبيوتر) في آن واحد دون حظره أو تقييده بجهاز واحد.
                        </span>
                    </div>
                </label>
            </div>

            <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                <x-btn-secondary onclick="closeModal('add-student-modal')">إلغاء</x-btn-secondary>
                <x-btn-primary icon="user-plus" type="submit">إنشاء حساب الطالب</x-btn-primary>
            </div>
        </form>
    </x-modal>

    {{-- Bulk Subscribe Students Modal --}}
    <div id="bulk-subscribe-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.65); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-3xl p-6 relative shadow-2xl my-8">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-[#E2E8F0]">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-purple-600/10 text-purple-600">
                        <i class="ph-bold ph-users-three text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#1A202C]">الاشتراك الجماعي للطلاب في الكورسات (Bulk Enrollment)</h3>
                        <p class="text-xs text-[#718096]">تفعيل اشتراك عدة طلاب في كورس أو عدة كورسات دفعة واحدة بضغطة زر.</p>
                    </div>
                </div>
                <button type="button" onclick="closeBulkSubscribeModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.students.bulkSubscribe') }}" method="POST" id="bulk-subscribe-form" class="space-y-6">
                @csrf

                {{-- Hidden Inputs for Checked Students --}}
                <div id="selected-student-ids-container"></div>

                {{-- 1. Student Selection Mode --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-bold text-[#1A202C]">1. تحديد الطلاب المستهدفين</label>
                        <div class="flex gap-2">
                            <button type="button" id="tab-btn-selected" onclick="switchStudentInputMode('selected')" class="text-xs font-semibold px-3 py-1 rounded-lg border border-purple-300 bg-purple-50 text-purple-700 transition-all">
                                الطلاب المحددين من الجدول (<span id="modal-selected-badge">0</span>)
                            </button>
                            <button type="button" id="tab-btn-emails" onclick="switchStudentInputMode('emails')" class="text-xs font-semibold px-3 py-1 rounded-lg border border-gray-200 bg-gray-50 text-[#718096] transition-all">
                                لصق قائمة إيميلات (Emails)
                            </button>
                        </div>
                    </div>

                    {{-- Selected Students View --}}
                    <div id="selected-students-view" class="p-3.5 bg-[#F8F9FA] rounded-xl border border-[#E2E8F0]">
                        <div id="selected-students-list" class="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
                            <p class="text-xs text-[#718096] py-2" id="no-selected-students-msg">لم يتم تحديد أي طالب من الجدول حتى الآن. يمكنك تحديد الطلاب بالـ Checkbox من جدول الطلاب أو التبديل إلى خيار "لصق قائمة إيميلات".</p>
                        </div>
                    </div>

                    {{-- Paste Emails Textarea View --}}
                    <div id="paste-emails-view" style="display: none;">
                        <textarea name="emails" id="bulk-emails-input" rows="4" placeholder="student1@gmail.com, student2@gmail.com&#10;student3@gmail.com" class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl p-3.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-purple-600/20 focus:border-purple-600 transition-all font-mono text-left dir-ltr"></textarea>
                        <p class="text-xs text-[#718096] mt-1.5 flex items-center gap-1">
                            <i class="ph-bold ph-info text-purple-600"></i>
                            يمكنك نسخ قائمة الإيميلات ولصقها مفصولة بأسطر جديدة، فواصل (,)، أو مسافات مباشرة من Excel/Word.
                        </p>
                    </div>
                </div>

                {{-- 2. Target Courses Multi-Selection --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <label class="block text-sm font-bold text-[#1A202C]">2. اختيار الكورسات المستهدفة (كورس أو أكثر)</label>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="selectAllCourses(true)" class="text-xs font-semibold text-[#0047AB] hover:underline">تحديد الكل</button>
                            <span class="text-gray-300">|</span>
                            <button type="button" onclick="selectAllCourses(false)" class="text-xs font-semibold text-[#718096] hover:underline">إلغاء الكل</button>
                        </div>
                    </div>

                    {{-- Search Course Filter --}}
                    <div class="relative">
                        <i class="ph-bold ph-magnifying-glass absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="course-filter-input" onkeyup="filterCoursesList()" placeholder="ابحث باسم الكورس، التخصص، أو السعر..." class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl pr-10 pl-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB] transition-all">
                    </div>

                    {{-- Courses Checkboxes Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-56 overflow-y-auto p-1" id="courses-checkboxes-container">
                        @foreach($courses as $course)
                            <label class="course-checkbox-item flex items-start gap-2.5 p-3 bg-[#F8F9FA] hover:bg-white border border-[#E2E8F0] rounded-xl cursor-pointer hover:border-[#0047AB] transition-all has-[:checked]:border-[#0047AB] has-[:checked]:bg-[#0047AB]/5" data-title="{{ mb_strtolower($course->title) }} {{ mb_strtolower($course->category_hierarchy ?? '') }}">
                                <input type="checkbox" name="course_ids[]" value="{{ $course->id }}" class="course-checkbox mt-0.5 rounded border-[#CBD5E0] text-[#0047AB] focus:ring-[#0047AB]">
                                <div class="flex-1 min-w-0">
                                    <strong class="block text-xs font-bold text-[#1A202C] truncate">{{ $course->title }}</strong>
                                    <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                        @if($course->category_hierarchy)
                                            <span class="text-[10px] text-[#0047AB] bg-[#0047AB]/10 px-1.5 py-0.5 rounded">{{ $course->category_hierarchy }}</span>
                                        @endif
                                        <span class="text-[10px] font-semibold text-[#00A896]">({{ $course->price }} ج.م)</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Footer Buttons --}}
                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeBulkSubscribeModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl transition-all shadow-md text-sm">
                        <i class="ph-bold ph-check-circle text-lg"></i>
                        <span>تأكيد وتفعيل الاشتراكات الجماعية</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    let selectedStudents = new Map(); // id -> {name, email}
    let studentInputMode = 'selected'; // 'selected' | 'emails'

    function toggleSelectAll(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.student-row-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = masterCheckbox.checked;
            if (masterCheckbox.checked) {
                selectedStudents.set(cb.value, {
                    name: cb.getAttribute('data-name'),
                    email: cb.getAttribute('data-email')
                });
            } else {
                selectedStudents.delete(cb.value);
            }
        });
        updateFloatingBar();
    }

    function handleRowCheckboxChange() {
        const checkboxes = document.querySelectorAll('.student-row-checkbox');
        checkboxes.forEach(cb => {
            if (cb.checked) {
                selectedStudents.set(cb.value, {
                    name: cb.getAttribute('data-name'),
                    email: cb.getAttribute('data-email')
                });
            } else {
                selectedStudents.delete(cb.value);
            }
        });

        // Update master checkbox state
        const masterCheckbox = document.getElementById('select-all-checkbox');
        if (masterCheckbox) {
            masterCheckbox.checked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
        }

        updateFloatingBar();
    }

    function clearSelectedStudents() {
        selectedStudents.clear();
        document.querySelectorAll('.student-row-checkbox').forEach(cb => cb.checked = false);
        const masterCheckbox = document.getElementById('select-all-checkbox');
        if (masterCheckbox) masterCheckbox.checked = false;
        updateFloatingBar();
    }

    function updateFloatingBar() {
        const bar = document.getElementById('bulk-floating-bar');
        const countSpan = document.getElementById('floating-selected-count');
        const count = selectedStudents.size;

        if (countSpan) countSpan.innerText = count;

        if (bar) {
            bar.style.display = count > 0 ? 'flex' : 'none';
        }

        const modalBadge = document.getElementById('modal-selected-badge');
        if (modalBadge) modalBadge.innerText = count;
    }

    function openBulkSubscribeModal() {
        syncModalSelectedStudents();
        document.getElementById('bulk-subscribe-modal').style.display = 'flex';
    }

    function openBulkSubscribeModalWithSelected() {
        switchStudentInputMode('selected');
        openBulkSubscribeModal();
    }

    function closeBulkSubscribeModal() {
        document.getElementById('bulk-subscribe-modal').style.display = 'none';
    }

    function switchStudentInputMode(mode) {
        studentInputMode = mode;
        const btnSelected = document.getElementById('tab-btn-selected');
        const btnEmails = document.getElementById('tab-btn-emails');
        const viewSelected = document.getElementById('selected-students-view');
        const viewEmails = document.getElementById('paste-emails-view');

        if (mode === 'selected') {
            btnSelected.className = 'text-xs font-semibold px-3 py-1 rounded-lg border border-purple-300 bg-purple-50 text-purple-700 transition-all';
            btnEmails.className = 'text-xs font-semibold px-3 py-1 rounded-lg border border-gray-200 bg-gray-50 text-[#718096] transition-all';
            viewSelected.style.display = 'block';
            viewEmails.style.display = 'none';
        } else {
            btnEmails.className = 'text-xs font-semibold px-3 py-1 rounded-lg border border-purple-300 bg-purple-50 text-purple-700 transition-all';
            btnSelected.className = 'text-xs font-semibold px-3 py-1 rounded-lg border border-gray-200 bg-gray-50 text-[#718096] transition-all';
            viewSelected.style.display = 'none';
            viewEmails.style.display = 'block';
        }
    }

    function syncModalSelectedStudents() {
        const container = document.getElementById('selected-students-list');
        const hiddenContainer = document.getElementById('selected-student-ids-container');
        if (!container || !hiddenContainer) return;

        hiddenContainer.innerHTML = '';
        container.innerHTML = '';

        if (selectedStudents.size === 0) {
            container.innerHTML = '<p class="text-xs text-[#718096] py-2" id="no-selected-students-msg">لم يتم تحديد أي طالب من الجدول حتى الآن. يمكنك تحديد الطلاب بالـ Checkbox من جدول الطلاب أو التبديل إلى خيار "لصق قائمة إيميلات".</p>';
            return;
        }

        selectedStudents.forEach((info, id) => {
            // Hidden input
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_ids[]';
            input.value = id;
            hiddenContainer.appendChild(input);

            // Badge chip
            const chip = document.createElement('span');
            chip.className = 'inline-flex items-center gap-1.5 bg-white border border-[#E2E8F0] text-xs font-semibold px-2.5 py-1 rounded-lg shadow-sm text-[#1A202C]';
            chip.innerHTML = `
                <span>${info.name}</span>
                <span class="text-[10px] text-gray-400 font-mono">(${info.email})</span>
                <button type="button" onclick="removeSelectedStudent('${id}')" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i class="ph-bold ph-x"></i>
                </button>
            `;
            container.appendChild(chip);
        });
    }

    function removeSelectedStudent(id) {
        selectedStudents.delete(id);
        const rowCb = document.querySelector(`.student-row-checkbox[value="${id}"]`);
        if (rowCb) rowCb.checked = false;
        handleRowCheckboxChange();
        syncModalSelectedStudents();
    }

    function selectAllCourses(select) {
        document.querySelectorAll('.course-checkbox').forEach(cb => {
            const item = cb.closest('.course-checkbox-item');
            if (item && item.style.display !== 'none') {
                cb.checked = select;
            }
        });
    }

    function filterCoursesList() {
        const query = document.getElementById('course-filter-input').value.toLowerCase().trim();
        document.querySelectorAll('.course-checkbox-item').forEach(item => {
            const title = item.getAttribute('data-title') || '';
            if (!query || title.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Form submission validation
    document.getElementById('bulk-subscribe-form')?.addEventListener('submit', function(e) {
        const courseCheckboxes = document.querySelectorAll('.course-checkbox:checked');
        if (courseCheckboxes.length === 0) {
            e.preventDefault();
            alert('يرجى تحديد كورس واحد على الأقل للاشتراك.');
            return;
        }

        const emailsInput = document.getElementById('bulk-emails-input').value.trim();
        if (selectedStudents.size === 0 && !emailsInput) {
            e.preventDefault();
            alert('يرجى تحديد طلاب من الجدول أو إدخال قائمة إيميلات في الخانة المخصصة.');
            return;
        }
    });
    </script>
    @endpush
@endsection
