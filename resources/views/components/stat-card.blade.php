@props(['icon', 'label', 'value', 'subtitle' => null, 'color' => 'primary'])

@php
$colorMap = [
    'primary' => 'bg-[#0047AB]/10 text-[#0047AB]',
    'teal' => 'bg-[#00A896]/10 text-[#00A896]',
    'cyan' => 'bg-[#2EC4B6]/10 text-[#2EC4B6]',
    'ocean' => 'bg-[#0088CC]/10 text-[#0088CC]',
];
$iconBg = $colorMap[$color] ?? $colorMap['primary'];
@endphp

<div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow duration-200 relative overflow-hidden group">
    <div class="flex justify-between items-start mb-4">
        <span class="text-[#4A5568] font-semibold text-sm">{{ $label }}</span>
        <div class="p-2.5 rounded-xl {{ $iconBg }}">
            <i class="ph-bold ph-{{ $icon }} text-xl"></i>
        </div>
    </div>
    <div>
        <h3 class="text-3xl font-bold text-[#1A202C] mb-1">{{ $value }}</h3>
        @if($subtitle)
            <div class="text-xs text-[#718096]">{{ $subtitle }}</div>
        @endif
    </div>
    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-[#0047AB] to-[#00A896] opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
</div>
