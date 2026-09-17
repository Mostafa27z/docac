@extends('layouts.panel')

@section('title', 'الكورسات المخصصة لك - Doc Academy')
@section('role_title', 'لوحة المحاضر')
@section('page_title', 'إدارة المناهج والدروس')

@section('content')
    <x-page-header title="الكورسات المسندة إليك" subtitle="إدارة المناهج، المحاضرات، الفيديوهات والامتحانات للكورسات المسندة إليك من الإدارة." />

    {{-- Filter Card --}}
    <x-card class="mb-6">
        <form method="GET" action="{{ route('instructor.courses.index') }}" class="space-y-4">
            <div class="flex items-center gap-2 mb-3 pb-2 border-b border-[#E2E8F0]">
                <i class="ph-bold ph-funnel text-[#00A896] text-lg"></i>
                <h4 class="font-bold text-sm text-[#1A202C]">تصفية الكورسات حسب التصنيف والتخصص</h4>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-[#4A5568] mb-1">التصنيف الرئيسي</label>
                    <select name="category_id" id="filter-category-select" onchange="onFilterCategoryChange(this.value)" class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-3 py-2 text-[#1A202C] text-xs focus:outline-none focus:ring-2 focus:ring-[#00A896]/20 focus:border-[#00A896]">
                        <option value="">-- كل التصنيفات --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#4A5568] mb-1">التخصص الفرعي</label>
                    <select name="subcategory_id" id="filter-subcategory-select" onchange="onFilterSubcategoryChange(this.value)" class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-3 py-2 text-[#1A202C] text-xs focus:outline-none focus:ring-2 focus:ring-[#00A896]/20 focus:border-[#00A896]">
                        <option value="">-- كل التخصصات الفرعية --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#4A5568] mb-1">التخصص الفرعي الدقيق</label>
                    <select name="child_subcategory_id" id="filter-child-subcategory-select" class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-3 py-2 text-[#1A202C] text-xs focus:outline-none focus:ring-2 focus:ring-[#00A896]/20 focus:border-[#00A896]">
                        <option value="">-- كل الفرعي الدقيق --</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-2 justify-end pt-2">
                @if(request()->hasAny(['category_id', 'subcategory_id', 'child_subcategory_id']))
                    <a href="{{ route('instructor.courses.index') }}" class="inline-flex items-center gap-1 bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#718096] text-xs font-semibold px-4 py-2 rounded-xl transition-all">
                        <i class="ph-bold ph-x text-sm"></i>
                        إلغاء التصفية
                    </a>
                @endif
                <button type="submit" class="inline-flex items-center gap-1.5 bg-[#00A896] hover:bg-[#00887A] text-white text-xs font-semibold px-5 py-2 rounded-xl transition-all shadow-sm">
                    <i class="ph-bold ph-magnifying-glass text-sm"></i>
                    تطبيق التصفية
                </button>
            </div>
        </form>
    </x-card>

    {{-- Courses List --}}
    <x-card>
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[#E2E8F0]">
            <div class="p-2 rounded-xl bg-[#00A896]/10 text-[#00A896]">
                <i class="ph-bold ph-list-dashes text-lg"></i>
            </div>
            <h2 class="text-lg font-bold text-[#1A202C]">قائمة الكورسات المتاحة لإدارتك</h2>
        </div>

        <x-data-table :headers="['اسم الكورس', 'التصنيف والتخصصات', 'النوع والسعر', 'الحالة', 'الإجراءات']">
            @forelse($courses as $course)
                <tr class="border-b border-[#E2E8F0] hover:bg-[#F8F9FA] transition-colors">
                    <td class="py-4 px-4 font-semibold text-[#1A202C]">
                        <div class="flex items-center gap-3">
                            @if($course->thumbnail)
                                <img src="{{ $course->thumbnail_url }}" class="w-10 h-10 rounded-xl object-cover border border-[#E2E8F0]" onerror="this.src='/logo.jfif'" />
                            @else
                                <div class="w-10 h-10 rounded-xl bg-[#0047AB]/10 text-[#0047AB] flex items-center justify-center font-bold text-xs">
                                    <i class="ph-bold ph-book-bookmark text-lg"></i>
                                </div>
                            @endif
                            <div>
                                <span class="block font-bold text-[#1A202C] text-sm">{{ $course->title }}</span>
                                <span class="text-xs text-[#718096]">{{ Str::limit($course->description, 40) }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-4 text-[#718096] text-sm">
                        <div class="font-medium text-[#1A202C]">{{ $course->category->name ?? '-' }}</div>
                        <div class="flex flex-wrap gap-1 mt-0.5">
                            @if($course->subcategory)
                                <span class="inline-block bg-[#00A896]/10 text-[#00A896] border border-[#00A896]/20 px-2 py-0.5 rounded text-[11px] font-medium">{{ $course->subcategory->name }}</span>
                            @endif
                            @if($course->childSubcategory)
                                <span class="inline-block bg-[#0088CC]/10 text-[#0088CC] border border-[#0088CC]/20 px-2 py-0.5 rounded text-[11px] font-medium">{{ $course->childSubcategory->name }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="py-4 px-4 text-sm">
                        <div class="font-bold text-[#1A202C]">{{ $course->price }} ج.م</div>
                        <span class="text-xs text-[#718096]">
                            @if($course->type === 'recorded') مسجل @elseif($course->type === 'live') بث مباشر @else هجين @endif
                        </span>
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
                                إدارة المنهج والدروس
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-[#718096] text-sm">لا توجد كورسات مسندة إليك حالياً. يرجى التواصل مع الإدارة.</td>
                </tr>
            @endforelse
        </x-data-table>
    </x-card>

    @push('scripts')
    <script>
    const categoriesData = @json($categories);

    function populateSubcategories(catId, targetSubSelectId, selectedSubId = null) {
        const subSelect = document.getElementById(targetSubSelectId);
        subSelect.innerHTML = '<option value="">-- اختر الفرعي --</option>';
        if (!catId) return;

        const category = categoriesData.find(c => c.id == catId);
        if (category && category.subcategories) {
            category.subcategories.forEach(sub => {
                const opt = document.createElement('option');
                opt.value = sub.id;
                opt.textContent = sub.name;
                if (selectedSubId && sub.id == selectedSubId) {
                    opt.selected = true;
                }
                subSelect.appendChild(opt);
            });
        }
    }

    function populateChildSubcategories(catId, subId, targetChildSelectId, selectedChildId = null) {
        const childSelect = document.getElementById(targetChildSelectId);
        childSelect.innerHTML = '<option value="">-- اختر الفرعي الدقيق --</option>';
        if (!catId || !subId) return;

        const category = categoriesData.find(c => c.id == catId);
        if (category && category.subcategories) {
            const subcategory = category.subcategories.find(s => s.id == subId);
            if (subcategory) {
                const children = subcategory.child_subcategories || subcategory.childSubcategories || [];
                children.forEach(child => {
                    const opt = document.createElement('option');
                    opt.value = child.id;
                    opt.textContent = child.name;
                    if (selectedChildId && child.id == selectedChildId) {
                        opt.selected = true;
                    }
                    childSelect.appendChild(opt);
                });
            }
        }
    }

    function onFilterCategoryChange(catId) {
        populateSubcategories(catId, 'filter-subcategory-select');
        populateChildSubcategories(null, null, 'filter-child-subcategory-select');
    }

    function onFilterSubcategoryChange(subId) {
        const catId = document.getElementById('filter-category-select').value;
        populateChildSubcategories(catId, subId, 'filter-child-subcategory-select');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const selectedCat = "{{ request('category_id') }}";
        const selectedSub = "{{ request('subcategory_id') }}";
        const selectedChild = "{{ request('child_subcategory_id') }}";

        if (selectedCat) {
            populateSubcategories(selectedCat, 'filter-subcategory-select', selectedSub);
            if (selectedSub) {
                populateChildSubcategories(selectedCat, selectedSub, 'filter-child-subcategory-select', selectedChild);
            }
        }
    });
    </script>
    @endpush
@endsection
