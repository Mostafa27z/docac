@extends('layouts.panel')

@section('title', 'إدارة المدرسين - Doc Academy')
@section('role_title', 'لوحة المشرف العام')
@section('page_title', 'إدارة المدرسين')

@section('content')
    <x-page-header title="إدارة هيئة التدريس" subtitle="عرض ومتابعة بيانات جميع المحاضرين المسجلين بالمنصة." />

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-stat-card icon="chalkboard-teacher" label="إجمالي المدرسين" :value="$instructors->count()" color="primary" />
        <x-stat-card icon="users-three" label="الطلاب المسجلين" :value="\App\Models\User::where('role', 'student')->count()" color="teal" />
        <x-stat-card icon="currency-dollar" label="نسبة العمولة الافتراضية" value="20%" color="ocean" />
    </div>

    {{-- Teachers Table --}}
    <x-card>
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[#E2E8F0]">
            <div class="p-2 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                <i class="ph-bold ph-list-dashes text-lg"></i>
            </div>
            <h3 class="font-bold text-[#1A202C]">قائمة المدرسين</h3>
        </div>

        <x-data-table :headers="['الاسم', 'البريد الإلكتروني', 'الهاتف', 'تاريخ التسجيل']">
            @forelse($instructors as $instructor)
                <tr class="border-b border-[#E2E8F0] hover:bg-[#F8F9FA] transition-colors">
                    <td class="py-4 px-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#0047AB] to-[#00A896] flex items-center justify-center text-white font-bold text-sm">
                            {{ mb_substr($instructor->name, 0, 1) }}
                        </div>
                        <span class="font-semibold text-[#1A202C]">{{ $instructor->name }}</span>
                    </td>
                    <td class="py-4 px-4 text-[#718096]">{{ $instructor->email }}</td>
                    <td class="py-4 px-4 text-[#718096]">{{ $instructor->phone ?? '-' }}</td>
                    <td class="py-4 px-4 text-[#718096]">{{ $instructor->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-8 text-center text-[#718096]">
                        <x-empty-state icon="chalkboard-teacher" title="لا يوجد مدرسين مسجلين حالياً" />
                    </td>
                </tr>
            @endforelse
        </x-data-table>
    </x-card>
@endsection
