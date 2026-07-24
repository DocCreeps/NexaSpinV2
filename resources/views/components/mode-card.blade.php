@props(['mode'])

@php
// 1. On nettoie la chaîne de caractères
$colorString = trim($mode->color ?? '');

// 2. On détecte la couleur dominante présente dans le gradient
$detectedColor = 'default';
if (str_contains($colorString, 'indigo')) {
$detectedColor = 'indigo';
} elseif (str_contains($colorString, 'rose') || str_contains($colorString, 'pink')) {
$detectedColor = 'rose';
} elseif (str_contains($colorString, 'emerald') || str_contains($colorString, 'green')) {
$detectedColor = 'emerald';
} elseif (str_contains($colorString, 'amber') || str_contains($colorString, 'yellow')) {
$detectedColor = 'amber';
}

// 3. On applique les classes correspondantes
$hoverClasses = $mode->available ? match ($detectedColor) {
'indigo' => [
'title' => 'md:group-hover:text-indigo-600',
'button' => 'md:group-hover:bg-indigo-600 md:group-hover:text-white md:group-hover:border-transparent',
],
'rose' => [
'title' => 'md:group-hover:text-rose-600',
'button' => 'md:group-hover:bg-rose-600 md:group-hover:text-white md:group-hover:border-transparent',
],
'emerald' => [
'title' => 'md:group-hover:text-emerald-600',
'button' => 'md:group-hover:bg-emerald-600 md:group-hover:text-white md:group-hover:border-transparent',
],
'amber' => [
'title' => 'md:group-hover:text-amber-600',
'button' => 'md:group-hover:bg-amber-600 md:group-hover:text-white md:group-hover:border-transparent',
],
default => [
'title' => 'md:group-hover:text-slate-600',
'button' => 'md:group-hover:bg-slate-800 md:group-hover:text-white md:group-hover:border-transparent',
],
} : ['title' => '', 'button' => ''];
@endphp

<{{ $mode->available ? 'a' : 'div' }} @if($mode->available)
    href="{{ $mode->route }}"
    x-data="{
    maxTilt: 6,
    perspective: 1000,
    scale: 1.02,
    rotateX: 0,
    rotateY: 0,
    currentScale: 1,
    rect: null,

    getRect() {
    this.rect = this.$el.getBoundingClientRect();
    },

    handleMouseMove(event) {
    if (!this.rect) this.getRect();

    const x = (event.clientX - this.rect.left) / this.rect.width - 0.5;
    const y = (event.clientY - this.rect.top) / this.rect.height - 0.5;

    this.rotateX = -(y * this.maxTilt).toFixed(2);
    this.rotateY = (x * this.maxTilt).toFixed(2);
    this.currentScale = this.scale;
    },

    resetTilt() {
    this.rotateX = 0;
    this.rotateY = 0;
    this.currentScale = 1;
    this.rect = null;
    }
    }"
    @mouseenter="getRect"
    @mousemove="handleMouseMove"
    @mouseleave="resetTilt"
    class="group relative block text-left no-underline focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-500 rounded-2xl"
    style="transform-style: preserve-3d"
    @else
    role="region"
    aria-label="{{ $mode->title }} (non disponible)"
    class="relative block cursor-not-allowed select-none opacity-60"
    @endif
    >

    {{-- Halo lumineux en arrière-plan (desktop uniquement) --}}
    @if($mode->available)
    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r {{ $mode->color }} opacity-0 blur-xl transition-opacity duration-300 md:group-hover:opacity-10 pointer-events-none" aria-hidden="true"></div>
    @endif

    <div @if($mode->available)
        x-ref="card"
        :style="`
        perspective: ${perspective}px;
        transform: rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(${currentScale});
        transform-style: preserve-3d;
        transition: ${rotateX == 0 ? 'transform .45s cubic-bezier(.16, 1, .3, 1)' : 'none'};
        `"
        @endif
        class="relative flex h-full flex-row md:flex-col items-center md:items-stretch gap-3 md:gap-0 rounded-2xl border border-slate-200 bg-white p-3 md:p-6 shadow-sm transition-all duration-300 md:group-hover:border-slate-300 md:group-hover:shadow-xl"
        >
        {{-- Icône décorative (Ignorée par les lecteurs d'écran) --}}
        <span class="text-2xl md:text-4xl shrink-0 select-none" aria-hidden="true" style="transform: translateZ(18px)">
            {{ $mode->icon }}
        </span>

        <div class="min-w-0 flex-1 md:flex-none" style="transform: translateZ(18px); backface-visibility: hidden;">
            {{-- Badge "Disponible/Bientôt" : Visuel Desktop uniquement --}}
            <div class="hidden md:flex mb-5 items-center justify-end" aria-hidden="true">
                <span @class([ 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold border' , 'bg-emerald-50 text-emerald-700 border-emerald-200'=> $mode->available,
                    'bg-slate-100 text-slate-500 border-slate-200' => !$mode->available,
                    ])>
                    {{ $mode->available ? 'Disponible' : 'Bientôt' }}
                </span>
            </div>

            {{-- Titre principal --}}
            <h3 class="truncate md:whitespace-normal text-base md:text-xl font-bold text-slate-800 transition-colors duration-300 {{ $hoverClasses['title'] }}">
                {{ $mode->title }}
            </h3>

            {{-- Info Joueurs + Statut (Mobile) --}}
            <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5 md:hidden">
                @if($mode->minParticipants)
                <span class="flex items-center gap-1 font-medium">
                    <svg class="h-3.5 w-3.5 text-slate-400" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>{{ $mode->minParticipants }}+ joueurs</span>
                </span>
                @endif

                @if(!$mode->available)
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500 border border-slate-200">
                    Bientôt
                </span>
                @endif
            </div>

            {{-- Description : Visuellement masquée en mobile, mais LUE par les lecteurs d'écran (sr-only md:not-sr-only) --}}
            <p class="sr-only md:not-sr-only md:block text-sm leading-6 text-slate-500 md:mt-3">
                {{ $mode->description }}
            </p>

            {{-- Footer : Visuel Desktop uniquement --}}
            <div class="hidden md:flex mt-8 items-center justify-between border-t border-slate-100 pt-5" aria-hidden="true">
                <div>
                    @if($mode->minParticipants)
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>
                            {{ $mode->minParticipants }}+ joueurs
                        </span>
                    </div>
                    @endif
                </div>

                @if($mode->available)
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 transition-all duration-300 {{ $hoverClasses['button'] }}">
                    <svg class="h-4 w-4 transition-transform md:group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
                @else
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8" />
                    </svg>
                </span>
                @endif
            </div>
        </div>

        {{-- Chevron mobile (Ignoré par le lecteur d'écran) --}}
        <svg class="h-4 w-4 shrink-0 text-slate-300 md:hidden" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </div>
</{{ $mode->available ? 'a' : 'div' }}>
