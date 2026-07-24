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

    {{-- Halo lumineux en arrière-plan (desktop uniquement, hover désactivé sous md:) --}}
    @if($mode->available)
    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r {{ $mode->color }} opacity-0 blur-xl transition-opacity duration-300 md:group-hover:opacity-10 pointer-events-none"></div>
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
        {{-- Icône : partagée entre les deux mises en page, seule sa taille change --}}
        <span class="text-2xl md:text-4xl shrink-0 select-none" style="transform: translateZ(18px)">
            {{ $mode->icon }}
        </span>

        <div class="min-w-0 flex-1 md:flex-none" style="transform: translateZ(18px); backface-visibility: hidden;">
            {{-- Badge "Disponible/Bientôt" : uniquement à partir de md, la rangée mobile n'a pas la place --}}
            <div class="hidden md:flex mb-5 items-center justify-end">
                <span @class([ 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold border' , 'bg-emerald-50 text-emerald-700 border-emerald-200'=> $mode->available,
                    'bg-slate-100 text-slate-500 border-slate-200' => !$mode->available,
                    ])>
                    {{ $mode->available ? 'Disponible' : 'Bientôt' }}
                </span>
            </div>

            <h3 class="truncate md:whitespace-normal mb-0.5 md:mb-3 text-base md:text-xl font-bold text-slate-800 transition-colors duration-300 {{ $hoverClasses['title'] }}">
                {{ $mode->title }}
            </h3>

            <p class="line-clamp-1 md:line-clamp-none text-xs md:text-sm leading-5 md:leading-6 text-slate-500">
                {{ $mode->description }}
            </p>

            {{-- Footer (participants min. + bouton flèche) : uniquement à partir de md --}}
            <div class="hidden md:flex mt-8 items-center justify-between border-t border-slate-100 pt-5">
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

        {{-- Chevron mobile uniquement : tient lieu de flèche/statut sur la rangée compacte --}}
        <svg class="h-4 w-4 shrink-0 text-slate-300 md:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </div>
</{{ $mode->available ? 'a' : 'div' }}>
