@props(['id', 'title' => '', 'icon' => null])

<div id="{{ $id }}" class="hidden fixed inset-0 z-50 overflow-y-auto">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-[#1A202C]/60 backdrop-blur-sm transition-opacity" onclick="closeModal('{{ $id }}')"></div>

    {{-- Modal Panel --}}
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-2xl max-w-lg w-full border border-[#E2E8F0] p-6 shadow-2xl z-10">
            {{-- Header --}}
            <div class="flex items-center justify-between mb-5 pb-4 border-b border-[#E2E8F0]">
                <h3 class="text-lg font-bold text-[#1A202C] flex items-center gap-2.5">
                    @if($icon)
                        <div class="p-2 rounded-xl bg-[#0047AB]/10 text-[#0047AB]">
                            <i class="ph-bold ph-{{ $icon }} text-xl"></i>
                        </div>
                    @endif
                    <span>{{ $title }}</span>
                </h3>
                <button onclick="closeModal('{{ $id }}')" class="p-1.5 rounded-lg text-[#718096] hover:bg-[#F8F9FA] hover:text-[#1A202C] transition-colors">
                    <i class="ph-bold ph-x text-xl"></i>
                </button>
            </div>

            {{-- Body --}}
            {{ $slot }}
        </div>
    </div>
</div>
