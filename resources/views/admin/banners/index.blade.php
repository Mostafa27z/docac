@extends('layouts.panel')

@section('title', 'إدارة البنرات الإعلانية - Doc Academy')
@section('role_title', 'لوحة المشرف العام')

@section('page_title')
    <div class="flex justify-between items-center w-full">
        <span>إدارة البنرات الإعلانية</span>
        <button onclick="openAddBannerModal()" class="inline-flex items-center gap-1.5 bg-[#0047AB] hover:bg-[#003B91] text-white text-xs font-semibold px-4 py-2 rounded-xl transition-all">
            <i class="ph-bold ph-plus-circle text-sm"></i>
            إضافة بنر إعلاني جديد
        </button>
    </div>
@endsection

@section('content')
    <x-page-header title="البنرات والإعلانات" subtitle="إدارة البنرات الترويجية والإعلانات التي تظهر للطلاب في الصفحة الرئيسية بالمنصة والتطبيق." />

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border-r-4 border-red-500 rounded-xl text-sm text-red-600 font-medium">
            <ul class="list-disc mr-4 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Banners Grid / Table --}}
    <x-card>
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[#E2E8F0]">
            <div class="p-2 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                <i class="ph-bold ph-[#0047AB] ph-image text-lg"></i>
            </div>
            <h3 class="font-bold text-[#1A202C]">قائمة البنرات المسجلة</h3>
        </div>

        <x-data-table :headers="['معاينة الصورة', 'العنوان والوصف', 'الترتيب', 'الحالة', 'تاريخ الإضافة', 'الإجراءات']">
            @forelse($banners as $banner)
                <tr class="border-b border-[#E2E8F0] hover:bg-[#F8F9FA] transition-colors">
                    <td class="py-4 px-4">
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-24 h-14 rounded-xl object-cover border border-[#E2E8F0] shadow-sm" onerror="this.src='/logo.jfif'" />
                    </td>
                    <td class="py-4 px-4">
                        <span class="block font-bold text-[#1A202C] text-sm mb-0.5">{{ $banner->title }}</span>
                        <span class="text-xs text-[#718096] block max-w-xs truncate">{{ $banner->description ?? 'لا يوجد وصف' }}</span>
                    </td>
                    <td class="py-4 px-4 font-mono text-sm font-bold text-[#0047AB]">
                        {{ $banner->sort_order }}
                    </td>
                    <td class="py-4 px-4">
                        @if($banner->is_active)
                            <x-badge variant="success">نشط</x-badge>
                        @else
                            <x-badge variant="neutral">موقوف</x-badge>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-[#718096] text-sm">
                        {{ $banner->created_at->format('Y-m-d') }}
                    </td>
                    <td class="py-4 px-4">
                        <div class="flex items-center gap-2 flex-wrap">
                            <form action="{{ route('admin.banners.toggle', $banner->id) }}" method="POST" class="inline">
                                @csrf
                                @if($banner->is_active)
                                    <button type="submit" class="inline-flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold px-3 py-1.5 rounded-xl border border-amber-200 transition-all">
                                        <i class="ph-bold ph-eye-slash text-sm"></i>
                                        إخفاء
                                    </button>
                                @else
                                    <button type="submit" class="inline-flex items-center gap-1 bg-[#2EC4B6]/10 hover:bg-[#2EC4B6]/20 text-[#00A896] text-xs font-semibold px-3 py-1.5 rounded-xl border border-[#2EC4B6]/20 transition-all">
                                        <i class="ph-bold ph-eye text-sm"></i>
                                        إظهار
                                    </button>
                                @endif
                            </form>
                            <button type="button" onclick="openEditBannerModal({{ json_encode($banner) }})" class="inline-flex items-center gap-1 bg-[#0088CC]/10 hover:bg-[#0088CC] text-[#0088CC] hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
                                <i class="ph-bold ph-pencil text-sm"></i>
                                تعديل
                            </button>
                            <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا البنر؟')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 bg-red-600/10 hover:bg-red-600 text-red-600 hover:text-white text-xs font-semibold px-3 py-1.5 rounded-xl transition-all">
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
                        <x-empty-state icon="image" title="لا توجد بنرات إعلانية" description="قم بإضافة بنرات جديدة لتظهر في تطبيق الطلاب." />
                    </td>
                </tr>
            @endforelse
        </x-data-table>
    </x-card>

    {{-- Add Banner Modal --}}
    <div id="add-banner-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-lg p-6 relative shadow-2xl my-8">
            <h3 class="text-lg font-bold mb-4 text-right text-[#1A202C] flex items-center gap-2 pb-3 border-b border-[#E2E8F0]">
                <i class="ph-bold ph-plus-circle text-[#0047AB]"></i>
                إضافة بنر إعلاني جديد
            </h3>
            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <x-form-input label="عنوان البنر" name="title" :required="true" placeholder="مثال: خصم 20% على دورات الباطنة" />
                <x-form-textarea label="وصف البنر (اختياري)" name="description" rows="3" placeholder="أدخل تفاصيل الإعلان..." />
                
                <div class="grid grid-cols-2 gap-4">
                    <x-form-input label="ترتيب الظهور" name="sort_order" type="number" min="0" value="0" />
                    <div class="flex items-center gap-2 pt-6">
                        <input type="checkbox" name="is_active" id="add-is-active" value="1" checked class="w-4 h-4 rounded text-[#0047AB] focus:ring-[#0047AB]">
                        <label for="add-is-active" class="text-sm font-semibold text-[#1A202C]">تفعيل البنر فوراً</label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">صورة البنر <span class="text-red-500">*</span></label>
                    <input type="file" name="image_file" accept="image/*" required class="w-full text-sm text-[#718096] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#0047AB]/10 file:text-[#0047AB] hover:file:bg-[#0047AB]/20 transition-all">
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeAddBannerModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <x-btn-primary icon="floppy-disk" type="submit">حفظ البنر</x-btn-primary>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Banner Modal --}}
    <div id="edit-banner-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(26,32,44,0.6); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl w-11/12 max-w-lg p-6 relative shadow-2xl my-8">
            <h3 class="text-lg font-bold mb-4 text-right text-[#1A202C] flex items-center gap-2 pb-3 border-b border-[#E2E8F0]">
                <i class="ph-bold ph-pencil-simple text-[#0088CC]"></i>
                تعديل بيانات البنر
            </h3>
            <form id="edit-banner-form" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <x-form-input label="عنوان البنر" name="title" id="edit-title" :required="true" />
                <x-form-textarea label="وصف البنر (اختياري)" name="description" id="edit-description" rows="3" />
                
                <div class="grid grid-cols-2 gap-4">
                    <x-form-input label="ترتيب الظهور" name="sort_order" id="edit-sort-order" type="number" min="0" />
                    <div class="flex items-center gap-2 pt-6">
                        <input type="checkbox" name="is_active" id="edit-is-active" value="1" class="w-4 h-4 rounded text-[#0047AB] focus:ring-[#0047AB]">
                        <label for="edit-is-active" class="text-sm font-semibold text-[#1A202C]">تفعيل البنر</label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">صورة جديدة (اختياري)</label>
                    <input type="file" name="image_file" accept="image/*" class="w-full text-sm text-[#718096] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#0047AB]/10 file:text-[#0047AB] hover:file:bg-[#0047AB]/20 transition-all">
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t border-[#E2E8F0]">
                    <button type="button" onclick="closeEditBannerModal()" class="bg-[#F8F9FA] hover:bg-[#E2E8F0] text-[#4A5568] font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">إلغاء</button>
                    <x-btn-primary icon="floppy-disk" type="submit">حفظ التغييرات</x-btn-primary>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openAddBannerModal() {
        document.getElementById('add-banner-modal').style.display = 'flex';
    }
    function closeAddBannerModal() {
        document.getElementById('add-banner-modal').style.display = 'none';
    }

    function openEditBannerModal(banner) {
        document.getElementById('edit-banner-form').action = `/admin/banners/${banner.id}`;
        document.getElementById('edit-title').value = banner.title;
        document.getElementById('edit-description').value = banner.description || '';
        document.getElementById('edit-sort-order').value = banner.sort_order;
        document.getElementById('edit-is-active').checked = banner.is_active == 1;
        document.getElementById('edit-banner-modal').style.display = 'flex';
    }
    function closeEditBannerModal() {
        document.getElementById('edit-banner-modal').style.display = 'none';
    }
    </script>
    @endpush
@endsection
