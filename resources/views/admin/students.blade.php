@extends('layouts.panel')

@section('title', 'إدارة الطلاب - Doc Academy')
@section('role_title', 'لوحة المشرف العام')
@section('page_title', 'إدارة الطلاب')

@section('content')
    <x-page-header title="إدارة الطلاب" subtitle="عرض بيانات الطلاب المسجلين بالمنصة وإلغاء ارتباط الأجهزة وتعديل حالات الحسابات.">
        <x-slot name="actions">
            {{-- Optional search inline --}}
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
        <div class="flex items-center gap-3 mb-5">
            <div class="p-2.5 rounded-xl bg-purple-600/10 text-purple-600">
                <i class="ph-bold ph-keyholder text-xl"></i>
            </div>
            <h2 class="text-lg font-bold text-[#1A202C]">تسجيل واشتراك طالب يدوي في كورس</h2>
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
                        <option value="{{ $course->id }}">{{ $course->title }} ({{ $course->price }} ج.م)</option>
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
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[#E2E8F0]">
            <div class="p-2 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                <i class="ph-bold ph-users text-lg"></i>
            </div>
            <h3 class="font-bold text-[#1A202C]">قائمة الطلاب المسجلين</h3>
        </div>

        <x-data-table :headers="['الاسم والمعلومات', 'البريد الإلكتروني', 'الهاتف', 'معرّف الجهاز (Device ID)', 'الحالة', 'تاريخ التسجيل', 'الإجراءات']">
            @forelse($students as $student)
                <tr class="border-b border-[#E2E8F0] hover:bg-[#F8F9FA] transition-colors">
                    <td class="py-4 px-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#0047AB] to-[#00A896] flex items-center justify-center text-white font-bold text-sm">
                            {{ mb_substr($student->name, 0, 1) }}
                        </div>
                        <span class="font-semibold text-[#1A202C]">{{ $student->name }}</span>
                    </td>
                    <td class="py-4 px-4 text-[#718096] text-sm">{{ $student->email }}</td>
                    <td class="py-4 px-4 text-[#718096] text-sm">{{ $student->phone ?? '-' }}</td>
                    <td class="py-4 px-4 text-sm">
                        @if($student->active_device_id)
                            <div class="flex flex-col gap-1.5 items-start">
                                <span class="font-mono text-xs text-[#0047AB] bg-[#0047AB]/5 px-2.5 py-1 rounded-lg border border-[#0047AB]/10 select-all max-w-[200px] truncate" title="{{ $student->active_device_id }}">
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
                            <x-badge variant="neutral">لا يوجد جهاز مرتبط</x-badge>
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
                        <div class="flex items-center gap-2">
                            <form action="{{ route('admin.students.toggleStatus', $student->id) }}" method="POST">
                                @csrf
                                @if($student->status === 'active')
                                    <button type="submit" class="inline-flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold px-3 py-1.5 rounded-xl border border-amber-200 transition-all">
                                        <i class="ph-bold ph-prohibit-sidebar text-sm"></i>
                                        <span>إيقاف الحساب</span>
                                    </button>
                                @else
                                    <button type="submit" class="inline-flex items-center gap-1 bg-[#2EC4B6]/10 hover:bg-[#2EC4B6]/20 text-[#00A896] text-xs font-semibold px-3 py-1.5 rounded-xl border border-[#2EC4B6]/20 transition-all">
                                        <i class="ph-bold ph-check text-sm"></i>
                                        <span>تنشيط الحساب</span>
                                    </button>
                                @endif
                            </form>
                            <form action="{{ route('admin.students.upgrade', $student->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من ترقية هذا الطالب ليصبح محاضراً في المنصة؟')">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1.5 rounded-xl border border-purple-200 transition-all">
                                    <i class="ph-bold ph-arrow-up-right text-sm"></i>
                                    <span>ترقية لمدرس</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-[#718096]">
                        <x-empty-state icon="student" title="لا يوجد طلاب مسجلين" description="لم يتم العثور على أي طلاب في النظام حالياً." />
                    </td>
                </tr>
            @endforelse
        </x-data-table>

        <div class="mt-5">
            {{ $students->appends(request()->query())->links() }}
        </div>
    </x-card>
@endsection
