@extends('layouts.panel')

@section('title', 'بيانات الاتصال - Doc Academy')
@section('role_title', 'لوحة المشرف العام')
@section('page_title', 'بيانات الاتصال')

@section('content')
    <x-page-header title="بيانات الاتصال للمنصة" subtitle="تعديل روابط ومحاور الدعم الفني وشبكات التواصل الاجتماعي المتاحة للطلاب." />

    <div class="max-w-2xl mx-auto">
        <x-card>
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-[#E2E8F0]">
                <div class="p-2.5 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                    <i class="ph-bold ph-gear-six text-xl"></i>
                </div>
                <h2 class="text-lg font-bold text-[#1A202C]">إعدادات قنوات الدعم والتواصل</h2>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <x-form-input label="رابط الفيسبوك (Facebook URL)" name="facebook_url" type="url" placeholder="https://facebook.com/..." value="{{ $settings['facebook_url'] }}" />
                    <span class="text-[11px] text-[#718096] mt-1 block">رابط الصفحة أو المجموعة الرسمية على فيسبوك.</span>
                </div>

                <div>
                    <x-form-input label="رابط اليوتيوب (YouTube Channel)" name="youtube_url" type="url" placeholder="https://youtube.com/..." value="{{ $settings['youtube_url'] }}" />
                    <span class="text-[11px] text-[#718096] mt-1 block">رابط القناة الطبية الرسمية على يوتيوب.</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-form-input label="رقم الواتساب (WhatsApp)" name="whatsapp_number" placeholder="مثال: 01090214254" value="{{ $settings['whatsapp_number'] }}" />
                        <span class="text-[11px] text-[#718096] mt-1 block">رقم الهاتف لاستلام استفسارات الطلاب.</span>
                    </div>

                    <div>
                        <x-form-input label="رقم التليجرام (Telegram)" name="telegram_number" placeholder="مثال: 01090214254" value="{{ $settings['telegram_number'] }}" />
                        <span class="text-[11px] text-[#718096] mt-1 block">رقم التليجرام المخصص لخدمة العملاء.</span>
                    </div>
                </div>

                <div>
                    <x-form-input label="اسم مستخدم التليجرام (Telegram Username)" name="telegram_username" placeholder="مثال: DocAcademyy" value="{{ $settings['telegram_username'] }}" />
                    <span class="text-[11px] text-[#718096] mt-1 block">مُعرف القناة أو الحساب الرسمي على تليجرام بدون رمز @.</span>
                </div>

                <div class="pt-4 border-t border-[#E2E8F0]">
                    <x-btn-primary icon="floppy-disk" class="w-full" type="submit">حفظ التغييرات</x-btn-primary>
                </div>
            </form>
        </x-card>
    </div>
@endsection
