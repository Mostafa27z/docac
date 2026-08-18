@props(['icon' => 'clipboard-text', 'title', 'description' => ''])

<div class="flex flex-col items-center justify-center text-center py-16 px-6">
    <div class="w-20 h-20 rounded-2xl bg-[#0047AB]/5 flex items-center justify-center mb-5">
        <i class="ph-light ph-{{ $icon }} text-4xl text-[#0047AB]/40"></i>
    </div>
    <h3 class="text-lg font-bold text-[#1A202C] mb-2">{{ $title }}</h3>
    @if($description)
        <p class="text-sm text-[#718096] max-w-md leading-relaxed">{{ $description }}</p>
    @endif
    @if($slot->isNotEmpty())
        <div class="mt-5">
            {{ $slot }}
        </div>
    @endif
</div>
