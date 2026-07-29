@props(['mode', 'category' => null, 'index' => null])

<{{ $mode->available ? 'a' : 'div' }} @if($mode->available)
    href="{{ $mode->route }}"
    class="group relative flex h-full flex-col justify-between rounded-2xl border border-[#E8E1D3] bg-white p-5 transition-all duration-150 hover:-translate-y-0.5 hover:border-[#D9CFB8] hover:shadow-lg {{ $mode->shadow }} focus:outline-none !text-inherit !no-underline"
    @else
    role="region"
    aria-label="{{ $mode->title }} (non disponible)"
    class="relative flex h-full flex-col justify-between rounded-2xl border border-dashed border-[#E8E1D3] bg-[#F5F0E6]/50 p-5 opacity-60 cursor-not-allowed select-none"
    @endif
    >
    <div>
        <div class="relative flex items-center justify-between pb-3">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br {{ $mode->color }} text-base">
                    {{ $mode->icon }}
                </span>
                @if($index)
                <span class="font-mono text-xs text-[#B0A996]">N-{{ str_pad($index, 3, '0', STR_PAD_LEFT) }}</span>
                @endif
            </div>

            @if($mode->available)
            @if($category)
            <span class="inline-flex items-center gap-1.5 font-mono text-[10px] uppercase tracking-wider text-[#8A8375]">
                <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-br {{ $mode->color }}"></span>
                {{ $category }}
            </span>
            @endif
            @else
            <span class="font-mono text-[10px] uppercase tracking-wider text-[#B0A996]">
                Bientôt
            </span>
            @endif

            <span class="absolute inset-x-0 bottom-0 border-b border-dashed border-[#EEE8DA]"></span>
        </div>

        <h3 class="mt-5 font-display text-base font-bold text-[#2B2620]">
            {{ $mode->title }}
        </h3>

        <p class="mt-1.5 text-xs leading-relaxed text-[#8A8375] line-clamp-2">
            {{ $mode->description }}
        </p>
    </div>

    <div class="mt-6 flex items-center justify-between pt-3 font-mono text-xs text-[#8A8375]">
        <span>{{ $mode->minParticipants ? $mode->minParticipants.'+ personnes' : 'Solo' }}</span>

        @if($mode->available)
        <span class="font-semibold text-[#E8623D] group-hover:translate-x-0.5 transition-all duration-150">
            LANCER &rarr;
        </span>
        @endif
    </div>
</{{ $mode->available ? 'a' : 'div' }}>
