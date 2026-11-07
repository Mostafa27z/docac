@extends('layouts.panel')

@section('title', 'إعدادات المنصة والتطبيق - Doc Academy')
@section('role_title', 'لوحة المشرف العام')
@section('page_title', 'إعدادات المنصة والتطبيق')

@section('content')
    <x-page-header 
        title="إعدادات المنصة وميزات التطبيق" 
        subtitle="التحكم في إعدادات التطبيق وميزات الـ API الموجهة للطلاب، وتحديث قنوات التواصل والدعم الفني." 
    />

    <div class="max-w-4xl mx-auto space-y-8">

        {{-- 1. App Settings & Feature Flags Management Card --}}
        <x-card>
            <div class="flex items-center justify-between pb-5 border-b border-[#E2E8F0] mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-2xl bg-[#0047AB]/10 text-[#0047AB]">
                        <i class="ph-bold ph-sliders text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-[#1A202C]">إعدادات التطبيق وميزات الـ API (App Settings)</h2>
                        <p class="text-xs text-[#718096] mt-0.5">التحكم في حالة مفتاح (enable) والإعدادات العامة للتطبيق في الواجهة الأمامية والموبايل.</p>
                    </div>
                </div>

                {{-- Status Badge --}}
                @if($appSetting->enable)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        مُفعّل (Enable = True)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        مُعطّل (Enable = False)
                    </span>
                @endif
            </div>

            {{-- Feature Status & Quick Toggle Action --}}
            <div class="p-5 rounded-2xl border transition-all duration-200 {{ $appSetting->enable ? 'bg-emerald-50/70 border-emerald-200' : 'bg-slate-50 border-slate-200' }} mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold {{ $appSetting->enable ? 'text-emerald-950' : 'text-slate-900' }}">
                                ميزة الـ Enable للتطبيق (Feature Flag)
                            </span>
                            <span class="text-[11px] px-2 py-0.5 rounded font-mono font-medium {{ $appSetting->enable ? 'bg-emerald-200/70 text-emerald-900' : 'bg-slate-200 text-slate-700' }}">
                                enable: {{ $appSetting->enable ? 'true' : 'false' }}
                            </span>
                        </div>
                        <p class="text-xs {{ $appSetting->enable ? 'text-emerald-800' : 'text-slate-600' }} leading-relaxed">
                            @if($appSetting->enable)
                                الميزة مفعلة حالياً. تطبيق الموبايل والواجهة الأمامية يستقبلان <code class="font-mono font-bold">"enable": true</code> عند استدعاء endpoint الإعدادات.
                            @else
                                الميزة معطلة حالياً (الوضع الافتراضي). تطبيق الموبايل والواجهة الأمامية يستقبلان <code class="font-mono font-bold">"enable": false</code> عند استدعاء endpoint الإعدادات.
                            @endif
                        </p>
                    </div>

                    {{-- One-Click Instant Toggle Button --}}
                    <form action="{{ route('admin.settings.appToggle') }}" method="POST" class="flex-shrink-0">
                        @csrf
                        @if($appSetting->enable)
                            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-rose-600 hover:bg-rose-700 text-white shadow-sm hover:shadow transition-all duration-150 cursor-pointer">
                                <i class="ph-bold ph-power text-base"></i>
                                <span>تعطيل الميزة (Disable)</span>
                            </button>
                        @else
                            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm hover:shadow transition-all duration-150 cursor-pointer">
                                <i class="ph-bold ph-power text-base"></i>
                                <span>تفعيل الميزة (Enable)</span>
                            </button>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Live API Endpoint Info & Future Extensibility --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center gap-2 text-slate-700 font-bold mb-2">
                        <i class="ph-bold ph-plugs text-sm text-[#0047AB]"></i>
                        <span>الرابط البرمجي (API Endpoint)</span>
                    </div>
                    <p class="text-slate-600 mb-2 font-mono text-[11px] bg-white p-2 rounded border border-slate-200 select-all">
                        GET /api/v1/settings
                    </p>
                    <p class="text-slate-500 leading-normal">
                        الرابط عام للواجهة الأمامية وتطبيق الطلاب لجلب الإعدادات عند تشغيل التطبيق.
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200">
                    <div class="flex items-center gap-2 text-[#0047AB] font-bold mb-2">
                        <i class="ph-bold ph-code text-sm"></i>
                        <span>معاينة الـ JSON المرجع للتطبيق:</span>
                    </div>
                    <pre class="bg-white p-2 rounded border border-blue-100 font-mono text-[11px] text-slate-800 overflow-x-auto leading-relaxed">{
  "success": true,
  "data": {
    "enable": {{ $appSetting->enable ? 'true' : 'false' }}
  }
}</pre>
                </div>
            </div>
        </x-card>

        {{-- 2. Contact & Social Channels Settings Card --}}
        <x-card>
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-[#E2E8F0]">
                <div class="p-2.5 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                    <i class="ph-bold ph-phone-call text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-[#1A202C]">قنوات الدعم والتواصل الرسمي</h2>
                    <p class="text-xs text-[#718096] mt-0.5">تعديل روابط ومحاور الدعم الفني وشبكات التواصل المتاحة للطلاب داخل التطبيق.</p>
                </div>
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
                    <x-btn-primary icon="floppy-disk" class="w-full" type="submit">حفظ بيانات التواصل</x-btn-primary>
                </div>
            </form>
        </x-card>

    </div>
@endsection
