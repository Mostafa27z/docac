@props(['title', 'subtitle' => null])

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-[#1A202C]">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-sm text-[#718096] mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="flex items-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>
