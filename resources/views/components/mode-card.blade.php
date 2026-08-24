@props(['mode', 'category' => null])

@php
$isAvailable = $mode->available;
@endphp

<{{ $isAvailable ? 'a' : 'div' }} @if($isAvailable) href="{{ $mode->route }}" class="card-hard card-hard-hover group relative flex h-full flex-row items-start gap-3 rounded-xl border-2 border-ink bg-panel p-3 no-underline !text-inherit transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 sm:flex-col sm:items-stretch sm:justify-between sm:gap-0 sm:p-5" @else role="region" aria-label="{{ $mode->title }} (non disponible)" class="relative flex h-full cursor-not-allowed select-none flex-row items-start gap-3 rounded-xl border-2 border-ink/30 bg-[repeating-linear-gradient(45deg,var(--color-wash),var(--color-wash)_6px,var(--color-surface)_6px,var(--color-surface)_12px)] p-3 opacity-70 sm:flex-col sm:items-stretch sm:justify-between sm:gap-0 sm:p-5" @endif>

    {{-- Icône + meta mobile --}}
    <div class="flex shrink-0 items-center gap-3 sm:w-full sm:justify-between">
        <span class="flex h-11 w-11 items-center justify-center rounded-lg border-2 border-ink bg-gradient-to-br {{ $mode->color }} text-lg sm:h-10 sm:w-10 sm:text-base">
            {{ $mode->icon }}
        </span>

        {{-- Badge catégorie / SOON --}}
        <span class="hidden font-mono text-[8px] tracking-widest text-faint sm:inline">
            @if($isAvailable && $category)
            {{ Str::upper($category) }}
            @elseif(! $isAvailable)
            SOON
            @endif
        </span>
    </div>

    {{-- Texte --}}
    <div class="min-w-0 flex-1 sm:mt-4">
        <div class="flex items-start justify-between gap-2">
            <h3 class="font-display text-sm leading-snug text-ink sm:text-base">
                {{ $mode->title }}
            </h3>

            @if($isAvailable)
            <span class="mt-0.5 shrink-0 font-display text-primary sm:hidden" aria-hidden="true">▸</span>
            @endif
        </div>

        {{-- Description complète (line-clamp supprimé) --}}
        <p class="mt-1 text-xs leading-relaxed text-muted sm:mt-2 sm:text-sm">
            {{ $mode->description }}
        </p>

        {{-- Meta mobile --}}
        <div class="mt-2 flex items-center gap-2 text-[11px] text-subtle sm:hidden">
            @if(! $isAvailable)
            <span class="font-mono text-[8px] tracking-widest text-faint">SOON</span>
            <span aria-hidden="true">·</span>
            @elseif($category)
            <span class="font-mono text-[8px] tracking-widest text-faint">{{ Str::upper($category) }}</span>
            <span aria-hidden="true">·</span>
            @endif
            <span>{{ $mode->minParticipants ? $mode->minParticipants.'+ pers.' : 'Solo' }}</span>
        </div>
    </div>

    {{-- Footer desktop --}}
    <div class="mt-6 hidden items-center justify-between border-t-2 border-dashed border-line pt-3 text-xs text-subtle sm:flex">
        <span>{{ $mode->minParticipants ? $mode->minParticipants.'+ personnes' : 'Solo' }}</span>

        @if($isAvailable)
        <span class="font-display text-[10px] text-primary">
            PLAY ▸
        </span>
        @endif
    </div>
</{{ $isAvailable ? 'a' : 'div' }}>
