@extends('layouts.panel')

@section('title', 'إدارة المدرسين - Doc Academy')
@section('role_title', 'لوحة المشرف العام')

@section('page_title')
    <div class="flex justify-between items-center w-full">
        <span>إدارة المدرسين</span>
        <button onclick="openAddTeacherModal()" class="inline-flex items-center gap-1.5 bg-[#0047AB] hover:bg-[#003B91] text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all">
            <i class="ph-bold ph-user-plus text-sm"></i>
            إضافة مدرس جديد
        </button>
    </div>
@endsection

@section('content')
    <x-page-header title="إدارة هيئة التدريس" subtitle="عرض ومتابعة وتعديل بيانات جميع المحاضرين المسجلين بالمنصة." />

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

        <x-data-table :headers="['الاسم', 'البريد الإلكتروني', 'الهاتف', 'الحالة', 'تاريخ التسجيل', 'الإجراءات']">
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
                    <td class="py-4 px-4">
                        @if($instructor->status === 'active')
                            <x-badge variant="success">نشط</x-badge>
                        @else
                            <x-badge variant="danger">موقوف</x-badge>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-[#718096]">{{ $instructor->created_at->format('Y-m-d') }}</td>
                    <td class="py-4 px-4">
                        <div class="flex gap-2">
                            <button type="button" onclick="openEditTeacherModal({{ $instructor->id }}, '{{ addslashes($instructor->name) }}', '{{ addslashes($instructor->email) }}', '{{ addslashes($instructor->phone ?? '') }}', '{{ $instructor->status }}')" class="inline-flex items-center gap-1.5 bg-[#0088CC]/10 hover:bg-[#0088CC] text-[#0088CC] hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                <i class="ph-bold ph-pencil text-sm"></i>
                                تعديل
                            </button>
                            <form action="{{ route('admin.teachers.destroy', $instructor->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف حساب هذا المحاضر؟ لا يمكن التراجع عن هذا الإجراء.')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1.5 bg-red-600/10 hover:bg-red-600 text-red-600 hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                    <i class="ph-bold ph-trash text-sm"></i>
                                    حذف
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-[#718096]">
                        <x-empty-state icon="chalkboard-teacher" title="لا يوجد مدرسين مسجلين حالياً" />
                    </td>
                </tr>
            @endforelse
        </x-data-table>
    </x-card>

    {{-- Add Teacher Modal --}}
    <div id="add-teacher-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-md p-6 relative shadow-2xl">
            <h3 class="text-lg font-bold mb-4 text-right text-[#1A202C] flex items-center gap-2 pb-3 border-b border-[#E2E8F0]">
                <i class="ph-bold ph-user-plus text-[#0047AB]"></i>
                إضافة مدرس جديد
            </h3>
            <form action="{{ route('admin.teachers.store') }}" method="POST" class="space-y-4">
                @csrf
                <x-form-input label="الاسم بالكامل" name="name" :required="true" placeholder="مثال: د. أحمد علي" />
                <x-form-input label="البريد الإلكتروني" name="email" type="email" :required="true" placeholder="مثال: ahmed@lms.com" />
                <x-form-input label="رقم الهاتف" name="phone" placeholder="مثال: 01000000000" />
                <x-form-input label="كلمة المرور" name="password" type="password" :required="true" placeholder="8 خانات على الأقل" />

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeAddTeacherModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <x-btn-primary icon="user-plus" type="submit">إنشاء الحساب</x-btn-primary>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Teacher Modal --}}
    <div id="edit-teacher-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-md p-6 relative shadow-2xl">
            <h3 class="text-lg font-bold mb-4 text-right text-[#1A202C] flex items-center gap-2 pb-3 border-b border-[#E2E8F0]">
                <i class="ph-bold ph-pencil-simple text-[#0088CC]"></i>
                تعديل بيانات المدرس
            </h3>
            <form id="edit-teacher-form" action="" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <x-form-input label="الاسم بالكامل" name="name" id="edit-name" :required="true" />
                <x-form-input label="البريد الإلكتروني" name="email" id="edit-email" type="email" :required="true" />
                <x-form-input label="رقم الهاتف" name="phone" id="edit-phone" />
                
                <div>
                    <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">حالة الحساب</label>
                    <select name="status" id="edit-status" class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-4 py-2.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB] transition-all">
                        <option value="active">نشط</option>
                        <option value="suspended">موقوف / معطل</option>
                    </select>
                </div>

                <x-form-input label="تعيين كلمة مرور جديدة (اتركه فارغاً للاحتفاظ بالحالية)" name="password" type="password" placeholder="8 خانات على الأقل" />

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeEditTeacherModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <x-btn-primary icon="floppy-disk" type="submit">حفظ التغييرات</x-btn-primary>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openAddTeacherModal() {
        document.getElementById('add-teacher-modal').style.display = 'flex';
    }
    function closeAddTeacherModal() {
        document.getElementById('add-teacher-modal').style.display = 'none';
    }

    function openEditTeacherModal(id, name, email, phone, status) {
        document.getElementById('edit-teacher-form').action = `/admin/teachers/${id}`;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-email').value = email;
        document.getElementById('edit-phone').value = phone;
        document.getElementById('edit-status').value = status;
        document.getElementById('edit-teacher-modal').style.display = 'flex';
    }
    function closeEditTeacherModal() {
        document.getElementById('edit-teacher-modal').style.display = 'none';
    }
    </script>
    @endpush
@endsection
