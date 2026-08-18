@props(['icon' => null, 'type' => 'button'])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 bg-white border border-[#E2E8F0] text-[#4A5568] font-semibold px-6 py-2.5 rounded-xl hover:bg-[#F8F9FA] hover:border-[#0047AB]/30 hover:text-[#0047AB] focus:ring-2 focus:ring-[#0047AB]/10 transition-all duration-200 text-sm']) }}>
    @if($icon)
        <i class="ph-bold ph-{{ $icon }} text-lg"></i>
    @endif
    {{ $slot }}
</button>
