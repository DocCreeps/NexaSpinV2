@props(['mode'])

<div class="group relative overflow-hidden rounded-3xl border p-5 lg:p-6 flex flex-col justify-between transition-all duration-300 {{ $mode->shadow }} {{ $mode->available ? 'bg-white border-slate-200/80 hover:-translate-y-1 hover:border-slate-300' : 'bg-slate-50/80 border-slate-200/50 opacity-75' }}">
    {{-- Glow Background --}}
    <div class="absolute inset-x-0 top-0 h-28 bg-gradient-to-b {{ $mode->color }} opacity-[0.03] group-hover:opacity-[0.06] transition-opacity duration-300 pointer-events-none"></div>

    <div class="relative z-10 space-y-4">
        {{-- Top Header --}}
        <div class="flex justify-between items-center">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-2xl shadow-sm transition-transform duration-300 {{ $mode->available ? 'group-hover:scale-110 group-hover:rotate-3' : '' }} select-none">
                {{ $mode->icon }}
            </div>

            @if($mode->available)
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 border border-emerald-100">
                <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span>
                Disponible
            </span>
            @else
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-500 border border-slate-200/60">
                🔒 Bientôt
            </span>
            @endif
        </div>

        {{-- Titre & Description --}}
        <div class="space-y-1">
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">
                {{ $mode->title }}
            </h2>
            <p class="text-xs lg:text-sm leading-relaxed text-slate-500 font-medium line-clamp-3">
                {{ $mode->description }}
            </p>
        </div>
    </div>

    {{-- Footer Action --}}
    <div class="relative z-10 mt-6">
        @if($mode->available)
        <a href="{{ $mode->route }}" class="group/btn relative w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r {{ $mode->color }} px-4 py-2.5 text-sm font-bold text-white shadow-sm transition-all duration-300 hover:shadow-md active:scale-[0.98]">
            <span>🚀 Commencer</span>
            <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover/btn:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </a>
        @else
        <div class="w-full flex items-center justify-center gap-2 rounded-xl border border-dashed border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-400 bg-slate-100/50 select-none">
            Indisponible
        </div>
        @endif
    </div>
</div>
