@props(['label', 'name', 'required' => false, 'options' => []])

<div>
    <label for="{{ $name }}" class="block text-sm font-semibold text-[#4A5568] mb-1.5">{{ $label }}</label>
    <select
        name="{{ $name }}"
        id="{{ $attributes->get('id', $name) }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'w-full bg-[#F8F9FA] border border-[#E2E8F0] rounded-xl px-4 py-2.5 text-[#1A202C] text-sm focus:outline-none focus:ring-2 focus:ring-[#0047AB]/20 focus:border-[#0047AB] transition-all duration-200']) }}
    >
        {{ $slot }}
    </select>
</div>
