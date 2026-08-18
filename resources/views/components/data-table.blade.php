@props(['headers' => []])

<div class="overflow-x-auto">
    <table class="w-full border-collapse">
        <thead>
            <tr class="border-b-2 border-[#E2E8F0] text-right">
                @foreach($headers as $header)
                    <th class="py-3.5 px-4 text-[#4A5568] font-semibold text-xs uppercase tracking-wider">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="text-sm text-[#1A202C]">
            {{ $slot }}
        </tbody>
    </table>
</div>
