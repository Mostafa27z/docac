@extends('layouts.panel')

@section('title', 'ملف الطالب - ' . $user->name)
@section('role_title', 'لوحة المشرف العام')
@section('page_title')
    <div class="flex justify-between items-center w-full">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.students.index') }}" class="p-2 rounded-xl bg-white border border-[#E2E8F0] hover:bg-gray-50 text-[#4A5568] transition-all">
                <i class="ph-bold ph-arrow-right text-lg"></i>
            </a>
            <span>ملف الطالب: {{ $user->name }}</span>
        </div>
    </div>
@endsection

@section('content')
    <x-page-header title="تفاصيل الطالب: {{ $user->name }}" subtitle="متابعة بيانات الطالب، التقدم في الكورسات، وإدارة الاشتراكات.">
        <x-slot name="actions">
            <a href="{{ route('admin.students.index') }}" class="inline-flex items-center gap-2 bg-white border border-[#E2E8F0] text-[#4A5568] px-4 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-all">
                <i class="ph-bold ph-caret-right"></i>
                <span>العودة لقائمة الطلاب</span>
            </a>
        </x-slot>
    </x-page-header>

    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border-r-4 border-emerald-500 rounded-xl text-sm text-emerald-700 font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border-r-4 border-red-500 rounded-xl text-sm text-red-600 font-medium">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border-r-4 border-red-500 rounded-xl text-sm text-red-600 font-medium">
            <ul class="list-disc mr-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Top Overview Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{-- Card 1: Info --}}
        <x-card>
            <div class="flex items-center gap-4 mb-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#0047AB] to-[#00A896] flex items-center justify-center text-white font-bold text-xl shadow-md">
                    {{ mb_substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h3 class="font-bold text-[#1A202C] text-lg">{{ $user->name }}</h3>
                    <p class="text-xs text-[#718096]">تاريخ التسجيل: {{ $user->created_at->format('Y-m-d (h:i A)') }}</p>
                </div>
            </div>
            <div class="space-y-3 pt-3 border-t border-[#E2E8F0] text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-[#718096] flex items-center gap-1.5"><i class="ph-bold ph-envelope text-[#0047AB]"></i> البريد الإلكتروني:</span>
                    <span class="font-semibold text-[#1A202C] select-all">{{ $user->email }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[#718096] flex items-center gap-1.5"><i class="ph-bold ph-phone text-[#0047AB]"></i> رقم الهاتف:</span>
                    <span class="font-semibold text-[#1A202C]">{{ $user->phone ?? 'غير محدد' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[#718096] flex items-center gap-1.5"><i class="ph-bold ph-user-gear text-[#0047AB]"></i> حالة الحساب:</span>
                    @if($user->status === 'active')
                        <x-badge variant="success">نشط</x-badge>
                    @else
                        <x-badge variant="error">موقوف</x-badge>
                    @endif
                </div>
            </div>
        </x-card>

        {{-- Card 2: Security & Device --}}
        <x-card>
            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-[#E2E8F0]">
                <div class="p-2 rounded-xl bg-purple-50 text-purple-600">
                    <i class="ph-bold ph-shield-check text-xl"></i>
                </div>
                <h3 class="font-bold text-[#1A202C]">سياسة الجهاز والأمان</h3>
            </div>
            <div class="space-y-4 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-[#718096]">نمط الأجهزة:</span>
                    @if($user->allow_multiple_devices)
                        <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 text-xs font-semibold px-2.5 py-1 rounded-lg border border-purple-200">
                            <i class="ph-bold ph-devices text-sm"></i>
                            أجهزة متعددة مسموحة
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 bg-blue-50 text-[#0047AB] text-xs font-semibold px-2.5 py-1 rounded-lg border border-blue-200">
                            <i class="ph-bold ph-device-mobile text-sm"></i>
                            مقيد بجهاز واحد
                        </span>
                    @endif
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[#718096]">معرّف الجهاز (Device ID):</span>
                    <span class="font-mono text-xs text-[#0047AB] bg-[#0047AB]/5 px-2 py-1 rounded border border-[#0047AB]/10 select-all max-w-[150px] truncate" title="{{ $user->active_device_id ?? 'غير مرتبط' }}">
                        {{ $user->active_device_id ?? 'لا يوجد' }}
                    </span>
                </div>
                <div class="pt-2 flex flex-wrap gap-2">
                    @if($user->active_device_id)
                        <form action="{{ route('admin.students.resetDevice', $user->id) }}" method="POST" onsubmit="return confirm('إلغاء ارتباط الجهاز الحالي؟')">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 text-xs font-bold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-xl border border-red-200 transition-all">
                                <i class="ph-bold ph-device-mobile-slash"></i>
                                <span>إلغاء ارتباط الجهاز</span>
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('admin.students.toggleMultiDevice', $user->id) }}" method="POST">
                        @csrf
                        @if($user->allow_multiple_devices)
                            <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-xl border border-gray-300 transition-all">
                                <i class="ph-bold ph-lock-key"></i>
                                <span>تقييد بجهاز واحد</span>
                            </button>
                        @else
                            <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-xl border border-purple-200 transition-all">
                                <i class="ph-bold ph-devices"></i>
                                <span>سماح بأجهزة متعددة</span>
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </x-card>

        {{-- Card 3: Account Actions --}}
        <x-card>
            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-[#E2E8F0]">
                <div class="p-2 rounded-xl bg-blue-50 text-[#0047AB]">
                    <i class="ph-bold ph-[#0047AB] ph-sliders text-xl"></i>
                </div>
                <h3 class="font-bold text-[#1A202C]">إجراءات الحساب</h3>
            </div>
            <div class="space-y-3">
                <form action="{{ route('admin.students.toggleStatus', $user->id) }}" method="POST">
                    @csrf
                    @if($user->status === 'active')
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold text-sm px-4 py-2.5 rounded-xl border border-amber-200 transition-all">
                            <i class="ph-bold ph-prohibit-sidebar text-base"></i>
                            <span>إيقاف حساب الطالب</span>
                        </button>
                    @else
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-sm px-4 py-2.5 rounded-xl border border-emerald-200 transition-all">
                            <i class="ph-bold ph-check-circle text-base"></i>
                            <span>تنشيط حساب الطالب</span>
                        </button>
                    @endif
                </form>

                <form action="{{ route('admin.students.upgrade', $user->id) }}" method="POST" onsubmit="return confirm('ترقية هذا الطالب إلى محاضر بالمنصة؟')">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-purple-50 hover:bg-purple-100 text-purple-700 font-semibold text-sm px-4 py-2.5 rounded-xl border border-purple-200 transition-all">
                        <i class="ph-bold ph-user-switch text-base"></i>
                        <span>ترقية إلى محاضر</span>
                    </button>
                </form>
            </div>
        </x-card>
    </div>

    {{-- Add New Course Enrollment Form Card --}}
    <x-card class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2.5 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                <i class="ph-bold ph-plus-circle text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-[#1A202C]">إضافة الطالب لكورس جديد</h3>
                <p class="text-xs text-[#718096]">يمكنك تسجيل الطالب يدوياً وتفعيل اشتراكه مباشرة في أي كورس متاحة بالمنصة.</p>
            </div>
        </div>

        @if($availableCourses->count() > 0)
            <form action="{{ route('admin.students.profileSubscribe', $user->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                @csrf
                <div class="md:col-span-2">
                    <x-form-select label="اختر الكورس المراد إضافته" name="course_id" :required="true">
                        <option value="">-- اختر كورس من القائمة --</option>
                        @foreach($availableCourses as $c)
                            <option value="{{ $c->id }}">{{ $c->title }}@if($c->category_hierarchy) [{{ $c->category_hierarchy }}]@endif ({{ number_format($c->price, 2) }} ج.م)</option>
                        @endforeach

                    </x-form-select>
                </div>
                <div>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-[#0047AB] hover:bg-[#003B91] text-white font-semibold px-5 py-2.5 rounded-xl transition-all duration-200 text-sm shadow-sm">
                        <i class="ph-bold ph-student text-lg"></i>
                        <span>إضافة وتفعيل الاشتراك</span>
                    </button>
                </div>
            </form>
        @else
            <div class="p-4 bg-gray-50 border border-[#E2E8F0] rounded-xl text-xs text-[#718096] text-center">
                الطالب مشترك بالفعل في جميع الكورسات المتاحة حالياً على المنصة.
            </div>
        @endif
    </x-card>

    {{-- Enrolled Courses Table Card --}}
    <x-card>
        <div class="flex items-center justify-between mb-5 pb-4 border-b border-[#E2E8F0]">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <i class="ph-bold ph-books text-xl"></i>
                </div>
                <h3 class="font-bold text-[#1A202C]">الكورسات المشترك بها ونسبة التقدم</h3>
            </div>
            <span class="bg-emerald-50 text-emerald-700 font-bold text-xs px-3 py-1 rounded-full border border-emerald-200">
                إجمالي الاشتراكات: {{ $user->enrollments->count() }}
            </span>
        </div>

        <x-data-table :headers="['الكورس والمسار', 'تاريخ الاشتراك', 'نسبة التقدم', 'المدفوع / السعر', 'حالة الاشتراك', 'الإجراءات']">
            @forelse($user->enrollments as $enrollment)
                @php
                    $progress = floatval($enrollment->progress_percentage ?? 0);
                @endphp
                <tr class="border-b border-[#E2E8F0] hover:bg-[#F8F9FA] transition-colors">
                    {{-- Course Title --}}
                    <td class="py-4 px-4">
                        <div class="font-bold text-[#1A202C] text-sm">{{ $enrollment->course->title ?? 'كورس محذوف' }}</div>
                        @if($enrollment->course && $enrollment->course->category)
                            <span class="text-xs text-[#718096]">{{ $enrollment->course->category->name }}</span>
                        @endif
                    </td>

                    {{-- Enrolled Date --}}
                    <td class="py-4 px-4 text-[#718096] text-sm">
                        {{ $enrollment->enrolled_at ? $enrollment->enrolled_at->format('Y-m-d') : '-' }}
                    </td>

                    {{-- Progress Bar --}}
                    <td class="py-4 px-4 w-56">
                        <div class="flex items-center justify-between mb-1.5 text-xs font-semibold">
                            <span class="text-[#4A5568]">{{ number_format($progress, 1) }}%</span>
                            @if($progress >= 100)
                                <span class="text-emerald-600 flex items-center gap-0.5"><i class="ph-bold ph-check-circle"></i> مكتمل</span>
                            @elseif($progress > 0)
                                <span class="text-blue-600">جاري التعلم</span>
                            @else
                                <span class="text-gray-400">لم يبدأ</span>
                            @endif
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full transition-all duration-300 {{ $progress >= 100 ? 'bg-emerald-500' : ($progress > 0 ? 'bg-gradient-to-r from-[#0047AB] to-[#00A896]' : 'bg-gray-300') }}"
                                 style="width: {{ min($progress, 100) }}%"></div>
                        </div>
                    </td>

                    {{-- Payment Details --}}
                    <td class="py-4 px-4 text-sm font-medium text-[#1A202C]">
                        <div>{{ number_format($enrollment->paid_amount ?? 0, 2) }} / {{ number_format($enrollment->total_price ?? 0, 2) }} ج.م</div>
                        <span class="text-[11px] text-[#718096]">{{ $enrollment->payment_status ?? 'fully_paid' }}</span>
                    </td>

                    {{-- Enrollment Status --}}
                    <td class="py-4 px-4">
                        @if($enrollment->status === 'active')
                            <x-badge variant="success">نشط</x-badge>
                        @else
                            <x-badge variant="warning">{{ $enrollment->status }}</x-badge>
                        @endif
                    </td>

                    {{-- Actions: Unsubscribe --}}
                    <td class="py-4 px-4">
                        <form action="{{ route('admin.students.unsubscribe', [$user->id, $enrollment->course_id]) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من إلغاء اشتراك الطالب {{ $user->name }} من كورس {{ $enrollment->course->title ?? '' }}؟ لن يستطيع الوصول إلى محتوى هذا الكورس.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold px-3 py-1.5 rounded-xl border border-red-200 transition-all">
                                <i class="ph-bold ph-trash text-sm"></i>
                                <span>إلغاء الاشتراك</span>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-[#718096]">
                        <x-empty-state icon="books" title="لا يوجد اشتراكات حالياً" description="الطالب غير مشترك في أي كورس بالمنصة بعد." />
                    </td>
                </tr>
            @endforelse
        </x-data-table>
    </x-card>
@endsection
