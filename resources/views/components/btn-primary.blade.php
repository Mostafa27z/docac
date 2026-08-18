@props(['icon' => null, 'type' => 'submit'])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 bg-[#0047AB] text-white font-semibold px-6 py-2.5 rounded-xl hover:bg-[#0088CC] focus:ring-2 focus:ring-[#0047AB]/30 transition-all duration-200 text-sm shadow-sm']) }}>
    @if($icon)
        <i class="ph-bold ph-{{ $icon }} text-lg"></i>
    @endif
    {{ $slot }}
</button>
