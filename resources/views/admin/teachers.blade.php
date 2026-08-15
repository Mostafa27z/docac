@extends('layouts.panel')

@section('title', 'إدارة المدرسين - Doc Academy')
@section('role_title', 'لوحة المشرف العام')



@section('page_title', 'المدرسين')

@section('content')
<!-- Page Header & CTA -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-margin-page gap-stack-md">
    <div>
        <h3 class="text-2xl font-bold text-on-surface">إدارة المدرسين</h3>
        <p class="text-sm text-on-surface-variant mt-1">عرض وتعديل بيانات هيئة التدريس.</p>
    </div>
</div>

<!-- Bento Grid Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-margin-page">
    <div class="bg-white border border-[#E2E8F0] rounded-xl p-stack-lg flex flex-col justify-between shadow-sm">
        <div class="flex justify-between items-start">
            <span class="font-semibold text-sm text-on-surface-variant">إجمالي المدرسين</span>
            <div class="p-2 rounded-lg bg-surface-container-low text-primary">
                <span class="material-symbols-outlined">school</span>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-3xl font-bold text-primary">{{ $instructors->count() }}</span>
        </div>
    </div>
    <div class="bg-white border border-[#E2E8F0] rounded-xl p-stack-lg flex flex-col justify-between shadow-sm">
        <div class="flex justify-between items-start">
            <span class="font-semibold text-sm text-on-surface-variant">الطلاب المسجلين</span>
            <div class="p-2 rounded-lg bg-surface-container-low text-tertiary">
                <span class="material-symbols-outlined">group</span>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-3xl font-bold text-on-surface">{{ \App\Models\User::where('role', 'student')->count() }}</span>
        </div>
    </div>
    <div class="bg-white border border-[#E2E8F0] rounded-xl p-stack-lg flex flex-col justify-between shadow-sm">
        <div class="flex justify-between items-start">
            <span class="font-semibold text-sm text-on-surface-variant">نسبة العمولة الافتراضية</span>
            <div class="p-2 rounded-lg bg-surface-container-low text-tertiary">
                <span class="material-symbols-outlined">payments</span>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-3xl font-bold text-on-surface">20%</span>
        </div>
    </div>
</div>

<!-- Teachers Data Table -->
<div class="bg-white border border-[#E2E8F0] rounded-xl overflow-hidden shadow-sm">
    <div class="p-4 border-b border-[#E2E8F0] flex justify-between items-center bg-[#EDF2F7]">
        <h4 class="font-bold text-on-surface">قائمة المدرسين</h4>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-right border-collapse">
            <thead class="bg-[#EDF2F7] border-b border-[#E2E8F0]">
                <tr>
                    <th class="py-3 px-4 font-semibold text-sm text-on-surface-variant">الاسم</th>
                    <th class="py-3 px-4 font-semibold text-sm text-on-surface-variant">البريد الإلكتروني</th>
                    <th class="py-3 px-4 font-semibold text-sm text-on-surface-variant">الهاتف</th>
                    <th class="py-3 px-4 font-semibold text-sm text-on-surface-variant">تاريخ التسجيل</th>
                </tr>
            </thead>
            <tbody class="text-sm text-on-surface">
                @forelse($instructors as $instructor)
                    <tr class="border-b border-[#E2E8F0] hover:bg-[#F7FAFC] transition-colors">
                        <td class="py-4 px-4 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-primary font-bold">
                                {{ mb_substr($instructor->name, 0, 2) }}
                            </div>
                            <span class="font-medium">{{ $instructor->name }}</span>
                        </td>
                        <td class="py-4 px-4 text-on-surface-variant">{{ $instructor->email }}</td>
                        <td class="py-4 px-4 text-on-surface-variant">{{ $instructor->phone ?? '-' }}</td>
                        <td class="py-4 px-4">{{ $instructor->created_at->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-on-surface-variant">لا يوجد مدرسين مسجلين حالياً.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
