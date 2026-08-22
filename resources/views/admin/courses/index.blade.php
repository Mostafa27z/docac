@extends('layouts.panel')

@section('title', 'إدارة الكورسات والمناهج - Doc Academy')
@section('role_title', 'لوحة المشرف العام')

@section('page_title')
    <div class="flex justify-between items-center w-full">
        <span>إدارة الكورسات والمناهج</span>
        <button onclick="openAddCourseModal()" class="inline-flex items-center gap-1.5 bg-[#0047AB] hover:bg-[#003B91] text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all">
            <i class="ph-bold ph-plus-circle text-sm"></i>
            إنشاء كورس وتعيين محاضر
        </button>
    </div>
@endsection

@section('content')
    <x-page-header title="إدارة الكورسات والمناهج" subtitle="إنشاء الكورسات، تعيين المحاضرين، وتوزيعها على التصنيفات والتخصصات." />

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border-r-4 border-red-500 rounded-xl text-sm text-red-600 font-medium">
            <ul class="list-disc mr-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Courses Table --}}
    <x-card>
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[#E2E8F0]">
            <div class="p-2 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                <i class="ph-bold ph-book-open-text text-lg"></i>
            </div>
            <h3 class="font-bold text-[#1A202C]">قائمة جميع الكورسات بالمنصة</h3>
        </div>

        <x-data-table :headers="['اسم الكورس', 'المحاضر المسؤول', 'التصنيف', 'النوع والسعر', 'الحالة', 'الإجراءات']">
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
                                <span class="block text-sm font-bold text-[#1A202C]">{{ $course->title }}</span>
                                <span class="text-xs text-[#718096]">{{ Str::limit($course->description, 40) }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-4 text-[#718096] text-sm">
                        @if($course->instructor)
                            <span class="font-semibold text-[#0047AB]">{{ $course->instructor->name }}</span>
                        @else
                            <x-badge variant="error">غير معين</x-badge>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-[#718096] text-sm">
                        <div>{{ $course->category->name ?? '-' }}</div>
                        @if($course->subcategory)
                            <span class="inline-block bg-[#F8F9FA] border border-[#E2E8F0] px-2 py-0.5 rounded text-[11px] text-[#00A896] mt-0.5 font-medium">{{ $course->subcategory->name }}</span>
                        @endif
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
                        <div class="flex gap-2 items-center flex-wrap">
                            <a href="{{ route('instructor.courses.manage', $course->id) }}" class="inline-flex items-center gap-1.5 bg-[#0047AB]/10 hover:bg-[#0047AB] text-[#0047AB] hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                <i class="ph-bold ph-gear text-sm"></i>
                                إدارة المنهج
                            </a>
                            <button type="button" onclick="openEditCourseModal({{ json_encode($course) }})" class="inline-flex items-center gap-1.5 bg-[#0088CC]/10 hover:bg-[#0088CC] text-[#0088CC] hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                <i class="ph-bold ph-pencil text-sm"></i>
                                تعديل
                            </button>
                            <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الكورس؟')" class="inline">
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
                        <x-empty-state icon="book-open-text" title="لا توجد كورسات مضافة" description="قم بإنشاء كورس جديد وتعيين المحاضر والتصنيفات." />
                    </td>
                </tr>
            @endforelse
        </x-data-table>
    </x-card>

    {{-- Add Course Modal --}}
    <div id="add-course-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-lg p-6 relative shadow-2xl my-8">
            <h3 class="text-lg font-bold mb-4 text-right text-[#1A202C] flex items-center gap-2 pb-3 border-b border-[#E2E8F0]">
                <i class="ph-bold ph-plus-circle text-[#0047AB]"></i>
                إنشاء كورس وتعيين المحاضر
            </h3>
            <form action="{{ route('admin.courses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <x-form-input label="عنوان الكورس" name="title" :required="true" placeholder="مثال: الأساسيات الشاملة لتخطيط القلب ECG" />
                
                <div>
                    <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">المحاضر المسؤول <span class="text-red-500">*</span></label>
                    <select name="instructor_id" required class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-4 py-2.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB]">
                        <option value="">-- اختر المحاضر --</option>
                        @foreach($instructors as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->name }} ({{ $inst->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">التصنيف الرئيسي</label>
                        <select name="category_id" id="add-category-select" onchange="loadSubcategories(this.value, 'add-subcategory-select')" class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-4 py-2.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB]">
                            <option value="">-- بدون تصنيف --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">التخصص الفرعي</label>
                        <select name="subcategory_id" id="add-subcategory-select" class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-4 py-2.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB]">
                            <option value="">-- اختر الفرعي --</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <x-form-select label="نوع الكورس" name="type" :required="true">
                        <option value="recorded">محاضرات مسجلة فقط</option>
                        <option value="live">جلسات بث مباشر فقط</option>
                        <option value="mixed">هجين (مسجل + بث مباشر)</option>
                    </x-form-select>
                    <x-form-input label="سعر الكورس (ج.م)" name="price" type="number" step="0.01" min="0" value="0" :required="true" />
                </div>

                <x-form-textarea label="وصف الكورس" name="description" :required="true" rows="3" placeholder="أدخل وصفاً تفصيلياً للكورس..." />

                <div>
                    <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">صورة الغلاف (Thumbnail)</label>
                    <input type="file" name="thumbnail_file" accept="image/*" class="w-full text-sm text-[#718096] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#0047AB]/10 file:text-[#0047AB] hover:file:bg-[#0047AB]/20 transition-all">
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeAddCourseModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <x-btn-primary icon="floppy-disk" type="submit">إنشاء الكورس والتوجيه لإضافة المنهج</x-btn-primary>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Course Modal --}}
    <div id="edit-course-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-lg p-6 relative shadow-2xl my-8">
            <h3 class="text-lg font-bold mb-4 text-right text-[#1A202C] flex items-center gap-2 pb-3 border-b border-[#E2E8F0]">
                <i class="ph-bold ph-pencil-simple text-[#0088CC]"></i>
                تعديل بيانات الكورس والمحاضر
            </h3>
            <form id="edit-course-form" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <x-form-input label="عنوان الكورس" name="title" id="edit-title" :required="true" />
                
                <div>
                    <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">المحاضر المسؤول <span class="text-red-500">*</span></label>
                    <select name="instructor_id" id="edit-instructor-id" required class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-4 py-2.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB]">
                        @foreach($instructors as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->name }} ({{ $inst->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">التصنيف الرئيسي</label>
                        <select name="category_id" id="edit-category-select" onchange="loadSubcategories(this.value, 'edit-subcategory-select')" class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-4 py-2.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB]">
                            <option value="">-- بدون تصنيف --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">التخصص الفرعي</label>
                        <select name="subcategory_id" id="edit-subcategory-select" class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-4 py-2.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB]">
                            <option value="">-- اختر الفرعي --</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <x-form-select label="نوع الكورس" name="type" id="edit-type" :required="true">
                        <option value="recorded">محاضرات مسجلة فقط</option>
                        <option value="live">جلسات بث مباشر فقط</option>
                        <option value="mixed">هجين (مسجل + بث مباشر)</option>
                    </x-form-select>
                    <x-form-input label="سعر الكورس (ج.م)" name="price" id="edit-price" type="number" step="0.01" min="0" :required="true" />
                </div>

                <x-form-textarea label="وصف الكورس" name="description" id="edit-description" :required="true" rows="3" />

                <div>
                    <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">صورة الغلاف (Thumbnail)</label>
                    <input type="file" name="thumbnail_file" accept="image/*" class="w-full text-sm text-[#718096] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#0047AB]/10 file:text-[#0047AB] hover:file:bg-[#0047AB]/20 transition-all">
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeEditCourseModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <x-btn-primary icon="floppy-disk" type="submit">حفظ التغييرات</x-btn-primary>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    const categoriesData = @json($categories);

    function loadSubcategories(categoryId, targetSelectId, selectedSubId = null) {
        const select = document.getElementById(targetSelectId);
        select.innerHTML = '<option value="">-- اختر الفرعي --</option>';
        if (!categoryId) return;

        const category = categoriesData.find(c => c.id == categoryId);
        if (category && category.subcategories) {
            category.subcategories.forEach(sub => {
                const opt = document.createElement('option');
                opt.value = sub.id;
                opt.textContent = sub.name;
                if (selectedSubId && sub.id == selectedSubId) {
                    opt.selected = true;
                }
                select.appendChild(opt);
            });
        }
    }

    function openAddCourseModal() {
        document.getElementById('add-course-modal').style.display = 'flex';
    }
    function closeAddCourseModal() {
        document.getElementById('add-course-modal').style.display = 'none';
    }

    function openEditCourseModal(course) {
        document.getElementById('edit-course-form').action = `/admin/courses/${course.id}`;
        document.getElementById('edit-title').value = course.title;
        document.getElementById('edit-instructor-id').value = course.instructor_id;
        document.getElementById('edit-category-select').value = course.category_id || '';
        document.getElementById('edit-type').value = course.type;
        document.getElementById('edit-price').value = course.price;
        document.getElementById('edit-description').value = course.description;

        loadSubcategories(course.category_id, 'edit-subcategory-select', course.subcategory_id);

        document.getElementById('edit-course-modal').style.display = 'flex';
    }
    function closeEditCourseModal() {
        document.getElementById('edit-course-modal').style.display = 'none';
    }
    </script>
    @endpush
@endsection
