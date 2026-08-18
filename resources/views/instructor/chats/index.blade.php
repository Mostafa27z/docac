@extends('layouts.panel')

@section('title', 'المحادثات والرسائل - Doc Academy')
@section('role_title', 'لوحة المحاضر')
@section('page_title', 'المحادثات والرسائل')

@section('content')
<div class="h-[calc(100vh-160px)] flex flex-col md:flex-row gap-6">

    {{-- Sidebar: Conversations & Quick Actions --}}
    <div class="w-full md:w-80 bg-white border border-[#E2E8F0] rounded-2xl p-4 shadow-sm flex flex-col">
        {{-- Quick Actions --}}
        <div class="flex flex-col gap-2 mb-4">
            <button onclick="openModal('newChatModal')" class="w-full inline-flex items-center justify-center gap-2 bg-[#0047AB] text-white font-semibold px-4 py-2.5 rounded-xl hover:bg-[#0088CC] transition-colors text-sm shadow-sm">
                <i class="ph-bold ph-chat-circle-plus text-lg"></i>
                بدء محادثة جديدة
            </button>
            <button onclick="openModal('broadcastModal')" class="w-full inline-flex items-center justify-center gap-2 bg-[#00A896] text-white font-semibold px-4 py-2.5 rounded-xl hover:bg-[#2EC4B6] transition-colors text-sm shadow-sm">
                <i class="ph-bold ph-megaphone text-lg"></i>
                رسالة جماعية (Broadcasting)
            </button>
        </div>

        <h3 class="text-xs font-semibold text-[#718096] uppercase tracking-wider mb-3 px-1">المحادثات النشطة</h3>

        {{-- Conversations List --}}
        <div class="space-y-2 overflow-y-auto flex-grow">
            @forelse($conversations as $conv)
                <a href="{{ route('instructor.chats.index', ['conversation_id' => $conv->id]) }}"
                   class="block p-3.5 rounded-xl border transition-all duration-150 {{ (isset($selectedConversation) && $selectedConversation->id === $conv->id) ? 'bg-[#0047AB]/5 border-[#0047AB]/30 shadow-sm' : 'bg-white border-[#E2E8F0] hover:bg-[#F8F9FA]' }}">
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-bold text-sm text-[#1A202C]">{{ $conv->student->name }}</span>
                        <span class="text-[10px] text-[#718096]">
                            {{ $conv->last_message_at ? $conv->last_message_at->diffForHumans() : '' }}
                        </span>
                    </div>
                    <div class="text-xs text-[#0047AB] font-medium truncate mb-1">{{ $conv->course->title }}</div>
                    @if($conv->messages->isNotEmpty())
                        <p class="text-xs text-[#718096] truncate">
                            {{ $conv->messages->last()->sender_id === auth()->id() ? 'أنت: ' : '' }}{{ $conv->messages->last()->message_text }}
                        </p>
                    @endif
                </a>
            @empty
                <div class="text-center py-8 text-[#718096] text-xs">
                    لا توجد محادثات نشطة حالياً.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Chat Window --}}
    <div class="flex-grow bg-white border border-[#E2E8F0] rounded-2xl shadow-sm flex flex-col overflow-hidden">
        @if(isset($selectedConversation))
            {{-- Header --}}
            <div class="bg-[#F8F9FA] p-4 border-b border-[#E2E8F0] flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#0047AB] to-[#00A896] flex items-center justify-center text-white font-bold text-sm">
                    {{ mb_substr($selectedConversation->student->name, 0, 1) }}
                </div>
                <div>
                    <h4 class="font-bold text-[#1A202C] text-sm">{{ $selectedConversation->student->name }}</h4>
                    <p class="text-xs text-[#718096]">{{ $selectedConversation->course->title }}</p>
                </div>
            </div>

            {{-- Messages --}}
            <div class="flex-grow p-5 overflow-y-auto space-y-4 bg-[#F8F9FA]/50" id="messages-container">
                @forelse($messages as $msg)
                    <div class="flex flex-col {{ $msg->sender_id === auth()->id() ? 'items-end' : 'items-start' }}">
                        <div class="max-w-[75%] rounded-2xl px-4 py-2.5 shadow-sm text-sm {{ $msg->sender_id === auth()->id() ? 'bg-[#0047AB] text-white rounded-tr-none' : 'bg-white text-[#1A202C] rounded-tl-none border border-[#E2E8F0]' }}">
                            {{ $msg->message_text }}
                        </div>
                        <span class="text-[10px] text-[#718096] mt-1 px-1">{{ $msg->created_at->format('H:i') }}</span>
                    </div>
                @empty
                    <x-empty-state icon="chat-circle-dots" title="أرسل رسالة لبدء المحادثة" />
                @endforelse
            </div>

            {{-- Send --}}
            <div class="p-4 bg-white border-t border-[#E2E8F0]">
                <form action="{{ route('instructor.chats.sendMessage', $selectedConversation->id) }}" method="POST" class="flex gap-2">
                    @csrf
                    <input type="text" name="message_text" required placeholder="اكتب رسالتك هنا..." autocomplete="off"
                           class="flex-grow bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-4 py-2.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB]">
                    <button type="submit" class="bg-[#0047AB] text-white px-5 py-2.5 rounded-xl hover:bg-[#0088CC] transition-colors flex items-center justify-center">
                        <i class="ph-bold ph-paper-plane-tilt text-lg"></i>
                    </button>
                </form>
            </div>
        @else
            <x-empty-state icon="chat-circle" title="مرحباً بك في مركز المحادثات" description="اختر محادثة من القائمة الجانبية للتواصل مع طلابك، أو بدء محادثة جديدة، أو إرسال رسالة جماعية." />
        @endif
    </div>
</div>

{{-- Modal: Start New Chat --}}
<x-modal id="newChatModal" title="بدء محادثة جديدة" icon="chat-circle-plus">
    <form action="{{ route('instructor.chats.start') }}" method="POST" class="space-y-4">
        @csrf
        <x-form-select label="اختر الكورس" name="course_id" :required="true">
            <option value="">-- اختر الكورس --</option>
            @foreach($courses as $course)
                <option value="{{ $course->id }}">{{ $course->title }}</option>
            @endforeach
        </x-form-select>

        <x-form-select label="اختر الطالب المشترك" name="student_id" :required="true">
            <option value="">-- اختر الطالب --</option>
            @foreach($enrolledStudents as $student)
                <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
            @endforeach
        </x-form-select>

        <div class="flex justify-end gap-2 pt-4 border-t border-[#E2E8F0]">
            <x-btn-secondary onclick="closeModal('newChatModal')">إلغاء</x-btn-secondary>
            <x-btn-primary icon="chat-circle-plus" type="submit">بدء المحادثة</x-btn-primary>
        </div>
    </form>
</x-modal>

{{-- Modal: Broadcast --}}
<x-modal id="broadcastModal" title="إرسال رسالة جماعية" icon="megaphone">
    <form action="{{ route('instructor.chats.broadcast') }}" method="POST" class="space-y-4">
        @csrf
        <x-form-select label="المستلمين (الكورس)" name="course_id" :required="true">
            <option value="all">كل الطلاب المشتركين في جميع كورساتي</option>
            @foreach($courses as $course)
                <option value="{{ $course->id }}">{{ $course->title }}</option>
            @endforeach
        </x-form-select>

        <x-form-textarea label="الرسالة الجماعية" name="message_text" :required="true" placeholder="اكتب الرسالة التي تريد إرسالها إلى جميع الطلاب المحددين..." />

        <div class="flex justify-end gap-2 pt-4 border-t border-[#E2E8F0]">
            <x-btn-secondary onclick="closeModal('broadcastModal')">إلغاء</x-btn-secondary>
            <x-btn-primary icon="megaphone" type="submit">إرسال جماعي</x-btn-primary>
        </div>
    </form>
</x-modal>

@push('scripts')
<script>
    window.addEventListener('load', function() {
        const c = document.getElementById('messages-container');
        if (c) c.scrollTop = c.scrollHeight;
    });
</script>
@endpush
@endsection
