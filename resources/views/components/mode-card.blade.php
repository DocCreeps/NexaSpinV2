@props(['mode', 'category' => null])

<{{ $mode->available ? 'a' : 'div' }} @if($mode->available)
    href="{{ $mode->route }}"
    class="card-cart group relative flex h-full flex-col justify-between rounded-xl border-2 border-[#17171B] bg-white p-5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E8291C] !text-inherit !no-underline"
    @else
    role="region"
    aria-label="{{ $mode->title }} (non disponible)"
    class="relative flex h-full flex-col justify-between rounded-xl border-2 border-[#17171B]/30 bg-[repeating-linear-gradient(45deg,#F0EEE5,#F0EEE5_6px,#FAFAF8_6px,#FAFAF8_12px)] p-5 opacity-70 cursor-not-allowed select-none"
    @endif
    >
    <div>
        <div class="flex items-center justify-between">
            <span class="flex h-10 w-10 items-center justify-center rounded-lg border-2 border-[#17171B] bg-gradient-to-br {{ $mode->color }} text-base">
                {{ $mode->icon }}
            </span>

            @if($mode->available)
            @if($category)
            <span class="font-mono text-[8px] tracking-widest text-[#A39D8D]">{{ Str::upper($category) }}</span>
            @endif
            @else
            <span class="font-mono text-[8px] tracking-widest text-[#A39D8D]">SOON</span>
            @endif
        </div>

        <h3 class="mt-4 font-display text-base leading-snug text-[#17171B]">
            {{ $mode->title }}
        </h3>

        <p class="mt-2 text-sm leading-relaxed text-[#5A564D] line-clamp-2">
            {{ $mode->description }}
        </p>
    </div>

    <div class="mt-6 flex items-center justify-between border-t-2 border-dashed border-[#E5E1D4] pt-3 text-xs text-[#7A756B]">
        <span>{{ $mode->minParticipants ? $mode->minParticipants.'+ personnes' : 'Solo' }}</span>

        @if($mode->available)
        <span class="font-display text-[10px] text-[#E8291C]">
            PLAY ▸
        </span>
        @endif
    </div>
</{{ $mode->available ? 'a' : 'div' }}>
