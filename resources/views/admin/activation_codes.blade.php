@extends('layouts.panel')

@section('title', 'أكواد التفعيل - Doc Academy')
@section('role_title', 'لوحة المشرف العام')
@section('page_title', 'أكواد التفعيل')

@section('content')
    <x-page-header title="أكواد تفعيل المسارات" subtitle="إنشاء وإدارة وتتبع رموز التفعيل الخاصة بالكورسات الطبية المسجلة بالمنصة.">
        <x-slot name="actions">
            <form action="{{ route('admin.codes.index') }}" method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث بالكود أو اسم الكورس..."
                       class="bg-white border border-[#E2E8F0] rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB] transition-all">
                <x-btn-primary icon="magnifying-glass" type="submit">بحث</x-btn-primary>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.codes.index') }}" class="inline-flex items-center justify-center bg-[#F8F9FA] border border-[#E2E8F0] text-[#4A5568] px-4 py-2 rounded-xl text-sm hover:bg-[#E2E8F0] transition-all">إلغاء</a>
                @endif
            </form>
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Bulk Generation Form --}}
        <x-card class="lg:col-span-1 h-fit">
            <div class="flex items-center gap-3 mb-5">
                <div class="p-2.5 rounded-xl bg-[#00A896]/10 text-[#00A896]">
                    <i class="ph-bold ph-key text-xl"></i>
                </div>
                <h2 class="text-lg font-bold text-[#1A202C]">توليد أكواد جديدة</h2>
            </div>
            <form action="{{ route('admin.codes.generate') }}" method="POST" class="space-y-4">
                @csrf
                <x-form-select label="اختر الكورس" name="course_id" :required="true">
                    <option value="">-- اختر الكورس --</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                    @endforeach
                </x-form-select>
                <x-form-input label="الكمية المطلوبة" name="quantity" type="number" :required="true" value="10" />
                <x-btn-primary icon="key" class="w-full">توليد الأكواد بالجملة</x-btn-primary>
            </form>
        </x-card>

        {{-- Codes Table --}}
        <x-card class="lg:col-span-2">
            <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[#E2E8F0]">
                <div class="p-2 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                    <i class="ph-bold ph-list-dashes text-lg"></i>
                </div>
                <h2 class="text-lg font-bold text-[#1A202C]">كشف الأكواد المولّدة</h2>
            </div>

            <x-data-table :headers="['الكورس', 'رمز التفعيل', 'الحالة', 'المستخدم', 'المولّد بواسطة', 'تاريخ الإنشاء']">
                @forelse($activationCodes as $code)
                    <tr class="border-b border-[#E2E8F0] hover:bg-[#F8F9FA] transition-colors">
                        <td class="py-4 px-4 text-[#1A202C] text-xs font-semibold max-w-[150px] truncate" title="{{ $code->course->title }}">
                            {{ $code->course->title }}
                        </td>
                        <td class="py-4 px-4 font-mono font-bold text-xs text-[#0047AB]">{{ $code->code }}</td>
                        <td class="py-4 px-4">
                            @if($code->is_used)
                                <x-badge variant="error">مُستخدم</x-badge>
                            @else
                                <x-badge variant="success">متاح</x-badge>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-[#1A202C] text-xs">{{ $code->student->name ?? '-' }}</td>
                        <td class="py-4 px-4 text-[#718096] text-xs">{{ $code->creator->name ?? '-' }}</td>
                        <td class="py-4 px-4 text-[#718096] text-xs">{{ $code->created_at->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-[#718096]">
                            <x-empty-state icon="key" title="لا توجد أكواد تفعيل" description="لم يتم العثور على أي أكواد تفعيل حالياً." />
                        </td>
                    </tr>
                @endforelse
            </x-data-table>

            <div class="mt-5">
                {{ $activationCodes->appends(request()->query())->links() }}
            </div>
        </x-card>
    </div>
@endsection
