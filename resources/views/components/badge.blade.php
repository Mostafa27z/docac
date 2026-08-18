@props(['variant' => 'info'])

@php
$styles = [
    'success' => 'bg-[#2EC4B6]/10 text-[#00A896] border-[#2EC4B6]/20',
    'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
    'error' => 'bg-red-50 text-red-700 border-red-200',
    'info' => 'bg-[#0047AB]/10 text-[#0047AB] border-[#0047AB]/20',
    'neutral' => 'bg-[#F8F9FA] text-[#4A5568] border-[#E2E8F0]',
];
$classes = $styles[$variant] ?? $styles['info'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full border ' . $classes]) }}>
    {{ $slot }}
</span>
