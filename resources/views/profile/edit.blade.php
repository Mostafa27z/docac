@extends('layouts.panel')

@section('title', 'الملف الشخصي - Doc Academy')
@section('role_title', auth()->user()->role === 'admin' ? 'لوحة المشرف العام' : 'لوحة المحاضر')

@section('page_title', 'الملف الشخصي وإعدادات الحساب')

@section('content')
    <x-page-header title="الملف الشخصي" subtitle="تعديل بياناتك الشخصية ومعلومات الاتصال وكلمة المرور." />

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
        {{-- Left / Top User Info Card --}}
        <div class="lg:col-span-1 space-y-6">
            <x-card class="text-center">
                <div class="relative w-28 h-28 mx-auto mb-4">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-28 h-28 rounded-full object-cover border-4 border-[#0047AB]/20 shadow-md">
                    @else
                        <div class="w-28 h-28 rounded-full bg-[#0047AB] text-white flex items-center justify-center text-3xl font-bold border-4 border-[#0047AB]/20 shadow-md">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </div>

                <h3 class="font-bold text-[#1A202C] text-lg mb-1">{{ $user->name }}</h3>
                <p class="text-xs text-[#718096] mb-3">{{ $user->email }}</p>

                <div class="inline-block">
                    @if($user->role === 'admin')
                        <x-badge variant="primary">مشرف عام (Admin)</x-badge>
                    @elseif($user->role === 'instructor')
                        <x-badge variant="success">محاضر (Instructor)</x-badge>
                    @else
                        <x-badge variant="neutral">{{ $user->role }}</x-badge>
                    @endif
                </div>

                <div class="mt-6 pt-6 border-t border-[#E2E8F0] space-y-3 text-right text-xs text-[#4A5568]">
                    <div class="flex justify-between items-center">
                        <span class="text-[#718096]">رقم الهاتف:</span>
                        <span class="font-semibold text-[#1A202C]">{{ $user->phone ?? 'غير محدد' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[#718096]">تاريخ الإنشاء:</span>
                        <span class="font-semibold text-[#1A202C]">{{ $user->created_at->format('Y-m-d') }}</span>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Right / Edit Forms --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Edit Personal Details Form --}}
            <x-card>
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[#E2E8F0]">
                    <div class="p-2 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                        <i class="ph-bold ph-user-gear text-lg"></i>
                    </div>
                    <h3 class="font-bold text-[#1A202C]">تعديل البيانات الشخصية</h3>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <x-form-input label="الاسم بالكامل" name="name" :required="true" value="{{ old('name', $user->name) }}" />
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form-input label="البريد الإلكتروني" name="email" type="email" :required="true" value="{{ old('email', $user->email) }}" />
                        <x-form-input label="رقم الهاتف" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="01xxxxxxxxx" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#4A5568] mb-1.5">الصورة الشخصية (Avatar)</label>
                        <input type="file" name="avatar_file" accept="image/*" class="w-full text-sm text-[#718096] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#0047AB]/10 file:text-[#0047AB] hover:file:bg-[#0047AB]/20 transition-all">
                    </div>

                    <div class="flex justify-end pt-4 border-t border-[#E2E8F0]">
                        <x-btn-primary icon="floppy-disk" type="submit">حفظ البيانات الشخصية</x-btn-primary>
                    </div>
                </form>
            </x-card>

            {{-- Change Password Form --}}
            <x-card>
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-[#E2E8F0]">
                    <div class="p-2 rounded-xl bg-amber-50 text-amber-600">
                        <i class="ph-bold ph-lock-key text-lg"></i>
                    </div>
                    <h3 class="font-bold text-[#1A202C]">تغيير كلمة المرور</h3>
                </div>

                <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <x-form-input label="كلمة المرور الحالية" name="current_password" type="password" :required="true" />
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form-input label="كلمة المرور الجديدة" name="password" type="password" :required="true" />
                        <x-form-input label="تأكيد كلمة المرور الجديدة" name="password_confirmation" type="password" :required="true" />
                    </div>

                    <div class="flex justify-end pt-4 border-t border-[#E2E8F0]">
                        <x-btn-primary icon="key" type="submit">تحديث كلمة المرور</x-btn-primary>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
