@props(['mode'])

<div @if($mode->available)
    x-data="{
    maxTilt: 6,
    perspective: 1000,
    scale: 1.01,
    rotateX: 0,
    rotateY: 0,
    currentScale: 1,

    handleMouseMove(e) {
    const card = this.$refs.card;
    const rect = this.$el.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const normalizedX = (x / rect.width) - 0.5;
    const normalizedY = (y / rect.height) - 0.5;

    this.rotateX = -(normalizedY * this.maxTilt).toFixed(2);
    this.rotateY = (normalizedX * this.maxTilt).toFixed(2);
    this.currentScale = this.scale;
    },

    resetTilt() {
    this.rotateX = 0;
    this.rotateY = 0;
    this.currentScale = 1;
    }
    }"
    @mousemove="handleMouseMove"
    @mouseleave="resetTilt"
    class="relative group cursor-pointer"
    style="backface-visibility: hidden; transform-style: preserve-3d;"
    @else
    class="relative opacity-50 cursor-not-allowed select-none"
    @endif
    >
    @if($mode->available)
    <div class="absolute inset-0 bg-gradient-to-r {{ $mode->color }} rounded-2xl blur-lg opacity-0 group-hover:opacity-[0.04] transition-opacity duration-300 pointer-events-none" style="transform: translateZ(0);"></div>
    @endif

    <div @if($mode->available)
        x-ref="card"
        :style="`
        perspective: ${perspective}px;
        transform: rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(${currentScale}) translateZ(0);
        transition: ${rotateX === 0 ? 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)' : 'none'};
        transform-style: preserve-3d;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        `"
        @endif
        class="relative h-full flex flex-col justify-between p-6 bg-white border border-slate-100 rounded-2xl shadow-sm group-hover:shadow-md group-hover:border-slate-200/80 transition-shadow duration-300 pointer-events-none"
        >
        <div class="pointer-events-auto" @if($mode->available) style="transform: translateZ(15px); backface-visibility: hidden;" @endif>
            <div class="flex items-center justify-between mb-4">
                <span class="text-3xl filter drop-shadow-sm">{{ $mode->icon }}</span>

                @if($mode->available)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                    Disponible
                </span>
                @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-50 text-slate-400 border border-slate-100">
                    Bientôt
                </span>
                @endif
            </div>

            <h3 class="text-lg font-bold text-slate-800 mb-2 transition-colors duration-300
                @if($mode->available)
                    @if($mode->title === 'Roue classique') group-hover:text-indigo-600
                    @elseif($mode->title === 'Roue par élimination') group-hover:text-rose-600
                    @elseif($mode->title === 'Tirage pondéré') group-hover:text-emerald-600
                    @endif
                @endif">
                {{ $mode->title }}
            </h3>

            <p class="text-sm text-slate-500 leading-relaxed">
                {{ $mode->description }}
            </p>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100/80 flex items-center justify-between pointer-events-auto" @if($mode->available) style="transform: translateZ(10px); backface-visibility: hidden;" @endif>
            <div>
                @if($mode->minParticipants)
                <span class="text-xs text-slate-400 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Min. {{ $mode->minParticipants }} joueurs
                </span>
                @else
                <span class="text-xs text-slate-300">—</span>
                @endif
            </div>

            @if($mode->available)
            <a href="{{ $mode->route }}" class="inline-flex items-center justify-center p-2 rounded-lg bg-slate-50 text-slate-400 border border-slate-100 transition-all duration-300
                    @if($mode->title === 'Roue classique') group-hover:bg-indigo-600 group-hover:text-white group-hover:border-transparent
                    @elseif($mode->title === 'Roue par élimination') group-hover:bg-rose-600 group-hover:text-white group-hover:border-transparent
                    @elseif($mode->title === 'Tirage pondéré') group-hover:bg-emerald-600 group-hover:text-white group-hover:border-transparent
                    @endif">
                <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
            @else
            <button disabled class="inline-flex items-center justify-center p-2 rounded-lg bg-slate-50 text-slate-300 border border-slate-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </button>
            @endif
        </div>
    </div>
</div>
