@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm ' . $class]) }}>
    {{ $slot }}
</div>
