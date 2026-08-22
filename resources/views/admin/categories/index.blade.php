@extends('layouts.panel')

@section('title', 'إدارة التصنيفات والتخصصات - Doc Academy')
@section('role_title', 'لوحة المشرف العام')

@section('page_title')
    <div class="flex justify-between items-center w-full">
        <span>إدارة التصنيفات والتخصصات</span>
        <button onclick="openAddCategoryModal()" class="inline-flex items-center gap-1.5 bg-[#0047AB] hover:bg-[#003B91] text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all">
            <i class="ph-bold ph-plus-circle text-sm"></i>
            إضافة تصنيف رئيسي جديد
        </button>
    </div>
@endsection

@section('content')
    <x-page-header title="التصنيفات والتخصصات الدراسية" subtitle="إدارة أقسام الكورسات الرئيسية والفرعية لتسهيل تصفح الطلاب." />

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border-r-4 border-red-500 rounded-xl text-sm text-red-600 font-medium">
            <ul class="list-disc mr-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Categories List --}}
        <div class="lg:col-span-3 space-y-6">
            @forelse($categories as $category)
                <x-card>
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-4 mb-4 border-b border-[#E2E8F0]">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-[#0047AB]/10 text-[#0047AB] flex items-center justify-center font-bold text-lg">
                                <i class="ph-bold ph-folder"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-[#1A202C]">{{ $category->name }}</h3>
                                <p class="text-xs text-[#718096] mt-0.5">
                                    عدد الكورسات: <span class="font-bold text-[#0047AB]">{{ $category->courses_count }}</span> | 
                                    الرمز: <code class="bg-[#F8F9FA] px-1.5 py-0.5 rounded text-xs">{{ $category->slug }}</code>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="openAddSubcategoryModal({{ $category->id }}, '{{ addslashes($category->name) }}')" class="inline-flex items-center gap-1.5 bg-[#00A896]/10 hover:bg-[#00A896] text-[#00A896] hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                <i class="ph-bold ph-plus text-sm"></i>
                                إضافة فرعي
                            </button>
                            <button onclick="openEditCategoryModal({{ $category->id }}, '{{ addslashes($category->name) }}')" class="inline-flex items-center gap-1.5 bg-[#0088CC]/10 hover:bg-[#0088CC] text-[#0088CC] hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                <i class="ph-bold ph-pencil text-sm"></i>
                                تعديل
                            </button>
                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا التصنيف وجميع تخصصاته الفرعية؟')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1.5 bg-red-600/10 hover:bg-red-600 text-red-600 hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                    <i class="ph-bold ph-trash text-sm"></i>
                                    حذف
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Subcategories Section --}}
                    <div>
                        <h4 class="text-xs font-bold text-[#718096] uppercase tracking-wider mb-3">التصنيفات الفرعية والتخصصات:</h4>
                        <div class="flex flex-wrap gap-2">
                            @forelse($category->subcategories as $sub)
                                <div class="inline-flex items-center gap-2 bg-[#F8F9FA] border border-[#E2E8F0] px-3 py-1.5 rounded-xl text-xs font-medium text-[#1A202C]">
                                    <span>{{ $sub->name }}</span>
                                    <form action="{{ route('admin.subcategories.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('حذف هذا التصنيف الفرعي؟')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 transition-colors">
                                            <i class="ph-bold ph-x text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <span class="text-xs text-[#718096] italic">لا توجد تخصصات فرعية مضافة حتى الآن.</span>
                            @endforelse
                        </div>
                    </div>
                </x-card>
            @empty
                <x-card class="text-center py-12">
                    <x-empty-state icon="folder-open" title="لا توجد تصنيفات مضافة" description="قم بإضافة التصنيفات الرئيسية لتقسيم الكورسات عليها." />
                </x-card>
            @endforelse
        </div>
    </div>

    {{-- Add Category Modal --}}
    <div id="add-category-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-md p-6 relative shadow-2xl">
            <h3 class="text-lg font-bold mb-4 text-right text-[#1A202C] flex items-center gap-2 pb-3 border-b border-[#E2E8F0]">
                <i class="ph-bold ph-folder-plus text-[#0047AB]"></i>
                إضافة تصنيف رئيسي جديد
            </h3>
            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <x-form-input label="اسم التصنيف" name="name" :required="true" placeholder="مثال: طب الباطنة العامة" />
                <div>
                    <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">صورة أيقونة التصنيف (اختياري)</label>
                    <input type="file" name="image" accept="image/*" class="w-full text-sm text-[#718096] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#0047AB]/10 file:text-[#0047AB] hover:file:bg-[#0047AB]/20 transition-all">
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeAddCategoryModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <x-btn-primary icon="floppy-disk" type="submit">حفظ التصنيف</x-btn-primary>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Category Modal --}}
    <div id="edit-category-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-md p-6 relative shadow-2xl">
            <h3 class="text-lg font-bold mb-4 text-right text-[#1A202C] flex items-center gap-2 pb-3 border-b border-[#E2E8F0]">
                <i class="ph-bold ph-pencil-simple text-[#0088CC]"></i>
                تعديل التصنيف
            </h3>
            <form id="edit-category-form" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <x-form-input label="اسم التصنيف" name="name" id="edit-category-name" :required="true" />
                <div>
                    <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">صورة أيقونة جديدة (اختياري)</label>
                    <input type="file" name="image" accept="image/*" class="w-full text-sm text-[#718096] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#0047AB]/10 file:text-[#0047AB] hover:file:bg-[#0047AB]/20 transition-all">
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeEditCategoryModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <x-btn-primary icon="floppy-disk" type="submit">حفظ التغييرات</x-btn-primary>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Subcategory Modal --}}
    <div id="add-subcategory-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-md p-6 relative shadow-2xl">
            <h3 class="text-lg font-bold mb-1 text-right text-[#1A202C] flex items-center gap-2">
                <i class="ph-bold ph-git-branch text-[#00A896]"></i>
                إضافة تصنيف فرعي
            </h3>
            <p class="text-xs text-[#718096] mb-4">التصنيف الرئيسي: <strong id="parent-category-name" class="text-[#0047AB]"></strong></p>
            <form action="{{ route('admin.subcategories.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="category_id" id="parent-category-id" value="">
                <x-form-input label="اسم التخصص الفرعي" name="name" :required="true" placeholder="مثال: قراءة رسم القلب ECG" />

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeAddSubcategoryModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <x-btn-primary icon="plus" type="submit">إضافة التخصص الفرعي</x-btn-primary>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openAddCategoryModal() {
        document.getElementById('add-category-modal').style.display = 'flex';
    }
    function closeAddCategoryModal() {
        document.getElementById('add-category-modal').style.display = 'none';
    }

    function openEditCategoryModal(id, name) {
        document.getElementById('edit-category-form').action = `/admin/categories/${id}`;
        document.getElementById('edit-category-name').value = name;
        document.getElementById('edit-category-modal').style.display = 'flex';
    }
    function closeEditCategoryModal() {
        document.getElementById('edit-category-modal').style.display = 'none';
    }

    function openAddSubcategoryModal(categoryId, categoryName) {
        document.getElementById('parent-category-id').value = categoryId;
        document.getElementById('parent-category-name').innerText = categoryName;
        document.getElementById('add-subcategory-modal').style.display = 'flex';
    }
    function closeAddSubcategoryModal() {
        document.getElementById('add-subcategory-modal').style.display = 'none';
    }
    </script>
    @endpush
@endsection
