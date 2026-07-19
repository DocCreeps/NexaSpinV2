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
}

// 3. On applique les classes correspondantes
$hoverClasses = $mode->available ? match ($detectedColor) {
'indigo' => [
'title' => 'group-hover:text-indigo-600',
'button' => 'group-hover:bg-indigo-600 group-hover:text-white group-hover:border-transparent',
],
'rose' => [
'title' => 'group-hover:text-rose-600',
'button' => 'group-hover:bg-rose-600 group-hover:text-white group-hover:border-transparent',
],
'emerald' => [
'title' => 'group-hover:text-emerald-600',
'button' => 'group-hover:bg-emerald-600 group-hover:text-white group-hover:border-transparent',
],
default => [
'title' => 'group-hover:text-slate-600',
'button' => 'group-hover:bg-slate-800 group-hover:text-white group-hover:border-transparent',
],
} : ['title' => '', 'button' => ''];
@endphp


<{{ $mode->available ? 'a' : 'div' }} @if($mode->available)
    href="{{ $mode->route }}"
    aria-label="{{ $mode->title }}"
    x-data="{
    maxTilt: 6,
    perspective: 1000,
    scale: 1.02,
    rotateX: 0,
    rotateY: 0,
    currentScale: 1,
    rect: null,

    // On ne calcule le rect qu'une seule fois à l'entrée de la souris pour booster les performances
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
    class="group relative block text-left no-underline"
    style="transform-style: preserve-3d"
    @else
    aria-disabled="true"
    class="relative block cursor-not-allowed select-none opacity-60"
    @endif
    >

    {{-- Halo lumineux en arrière-plan --}}
    @if($mode->available)
    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r {{ $mode->color }} opacity-0 blur-xl transition-opacity duration-300 group-hover:opacity-10 pointer-events-none"></div>
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
        {{-- Nettoyage : retrait de "group-hover:-translate-y-1" pour éviter les saccades de transform avec le Tilt --}}
        class="relative flex h-full flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 group-hover:border-slate-300 group-hover:shadow-xl"
        >
        {{-- Contenu supérieur --}}
        <div style="transform: translateZ(18px); backface-visibility: hidden;">
            <div class="mb-5 flex items-start justify-between">
                <span class="text-4xl select-none">
                    {{ $mode->icon }}
                </span>

                <span @class([ 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold border' , 'bg-emerald-50 text-emerald-700 border-emerald-200'=> $mode->available,
                    'bg-slate-100 text-slate-500 border-slate-200' => !$mode->available,
                    ])>
                    {{ $mode->available ? 'Disponible' : 'Bientôt' }}
                </span>
            </div>

            <h3 class="mb-3 text-xl font-bold text-slate-800 transition-colors duration-300 {{ $hoverClasses['title'] }}">
                {{ $mode->title }}
            </h3>

            <p class="text-sm leading-6 text-slate-500">
                {{ $mode->description }}
            </p>
        </div>

        {{-- Footer de la card --}}
        <div class="mt-8 flex items-center justify-between border-t border-slate-100 pt-5" style="transform: translateZ(12px); backface-visibility: hidden;">
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
                <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
</{{ $mode->available ? 'a' : 'div' }}>
