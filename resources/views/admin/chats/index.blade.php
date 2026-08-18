@extends('layouts.panel')

@section('title', 'مراقبة المحادثات - Doc Academy')
@section('role_title', 'لوحة المشرف العام')
@section('page_title', 'مراقبة المحادثات')

@section('content')
<div class="h-[calc(100vh-160px)] flex flex-col md:flex-row gap-6">

    {{-- Sidebar: Conversations list & search --}}
    <div class="w-full md:w-96 bg-white border border-[#E2E8F0] rounded-2xl p-4 shadow-sm flex flex-col">
        {{-- Search --}}
        <form action="{{ route('admin.chats.index') }}" method="GET" class="mb-4">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث باسم الطالب، المدرس، أو الكورس..."
                       class="w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB] transition-all">
                <i class="ph-bold ph-magnifying-glass absolute left-3 top-3 text-[#718096] text-sm"></i>
            </div>
        </form>

        <h3 class="text-xs font-semibold text-[#718096] uppercase tracking-wider mb-3 px-1">المحادثات في المنصة</h3>

        {{-- Conversations List --}}
        <div class="space-y-2 overflow-y-auto flex-grow">
            @forelse($conversations as $conv)
                <a href="{{ route('admin.chats.index', ['conversation_id' => $conv->id, 'search' => request('search')]) }}"
                   class="block p-3.5 rounded-xl border transition-all duration-150 {{ (isset($selectedConversation) && $selectedConversation->id === $conv->id) ? 'bg-[#0047AB]/5 border-[#0047AB]/30 shadow-sm' : 'bg-white border-[#E2E8F0] hover:bg-[#F8F9FA]' }}">
                    <div class="flex justify-between items-start mb-1.5">
                        <span class="font-bold text-xs text-[#0047AB]">{{ $conv->course->title }}</span>
                        <span class="text-[10px] text-[#718096]">
                            {{ $conv->last_message_at ? $conv->last_message_at->diffForHumans() : '' }}
                        </span>
                    </div>
                    <div class="text-xs text-[#1A202C] mb-1 flex items-center justify-between font-medium">
                        <span>{{ $conv->instructor->name }}</span>
                        <i class="ph-bold ph-arrow-left text-[10px] text-[#718096]"></i>
                        <span>{{ $conv->student->name }}</span>
                    </div>
                    @if($conv->messages->isNotEmpty())
                        <p class="text-xs text-[#718096] truncate mt-1">
                            {{ $conv->messages->last()->sender_id === $conv->instructor_id ? 'المحاضر: ' : 'الطالب: ' }}{{ $conv->messages->last()->message_text }}
                        </p>
                    @endif
                </a>
            @empty
                <div class="text-center py-8 text-[#718096] text-xs">
                    لا توجد محادثات مطابقة لخيارات البحث.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Right Area: Message History --}}
    <div class="flex-grow bg-white border border-[#E2E8F0] rounded-2xl shadow-sm flex flex-col overflow-hidden">
        @if(isset($selectedConversation))
            {{-- Header --}}
            <div class="bg-[#F8F9FA] p-5 border-b border-[#E2E8F0]">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-[#1A202C] text-sm mb-1">{{ $selectedConversation->course->title }}</h4>
                        <p class="text-xs text-[#718096] flex items-center gap-2">
                            <span><i class="ph-bold ph-chalkboard-teacher text-xs"></i> {{ $selectedConversation->instructor->name }}</span>
                            <i class="ph-bold ph-arrow-left text-[10px]"></i>
                            <span><i class="ph-bold ph-student text-xs"></i> {{ $selectedConversation->student->name }}</span>
                        </p>
                    </div>
                    <x-badge variant="error">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>
                        مراقبة نشطة
                    </x-badge>
                </div>
            </div>

            {{-- Messages --}}
            <div class="flex-grow p-5 overflow-y-auto space-y-4 bg-[#F8F9FA]/50" id="admin-messages-container">
                @forelse($messages as $msg)
                    <div class="flex flex-col {{ $msg->sender_id === $selectedConversation->instructor_id ? 'items-start' : 'items-end' }}">
                        <span class="text-[10px] font-bold mb-1 px-1 {{ $msg->sender_id === $selectedConversation->instructor_id ? 'text-[#0047AB]' : 'text-[#00A896]' }}">
                            {{ $msg->sender->name }} ({{ $msg->sender_id === $selectedConversation->instructor_id ? 'المحاضر' : 'الطالب' }})
                        </span>
                        <div class="max-w-[75%] rounded-2xl px-4 py-2.5 shadow-sm text-sm {{ $msg->sender_id === $selectedConversation->instructor_id ? 'bg-[#0047AB]/10 text-[#1A202C] rounded-tr-none' : 'bg-white text-[#1A202C] rounded-tl-none border border-[#E2E8F0]' }}">
                            {{ $msg->message_text }}
                        </div>
                        <span class="text-[10px] text-[#718096] mt-1 px-1">{{ $msg->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                @empty
                    <x-empty-state icon="chat-circle-dots" title="لا توجد رسائل في هذه المحادثة حتى الآن" />
                @endforelse
            </div>
        @else
            <x-empty-state icon="eye" title="منظومة مراقبة المحادثات" description="يرجى اختيار محادثة من القائمة الجانبية لعرض وتتبع سجل المحادثات المتبادلة بين المحاضرين والطلاب." />
        @endif
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('load', function() {
        const c = document.getElementById('admin-messages-container');
        if (c) c.scrollTop = c.scrollHeight;
    });
</script>
@endpush
@endsection
