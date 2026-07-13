@php
$modes = [
[
'icon' => '🎡',
'title' => 'Roue classique',
'description' => 'Un seul tour suffit pour désigner instantanément un gagnant parmi tous les participants.',
'route' => route('draw.wheel'),
'available' => true,
'color' => 'from-indigo-500 to-purple-600',
'shadow' => 'shadow-indigo-500/10 hover:shadow-indigo-500/20',
],
[
'icon' => '⚔️',
'title' => 'Roue par élimination',
'description' => 'Les participants s’affrontent tour après tour jusqu’au dernier survivant.',
'route' => route('draw.wheel-elimination'),
'available' => true,
'color' => 'from-red-500 to-orange-500',
'shadow' => 'shadow-red-500/10 hover:shadow-red-500/20',
],
[
'icon' => '🎯',
'title' => 'Tirage pondéré',
'description' => 'Attribuez des probabilités différentes selon un système de poids personnalisé.',
'route' => null,
'available' => false,
'color' => 'from-slate-400 to-slate-500',
'shadow' => 'shadow-gray-500/5',
],
];
@endphp

<x-layouts.app>
    <div class="min-h-screen bg-slate-50/50 relative overflow-hidden antialiased selection:bg-indigo-500 selection:text-white">

        <!-- Orbes de lumière en arrière-plan (Effet moderne et immersif) -->
        <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-indigo-200/30 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute top-[20%] right-[-10%] w-[400px] h-[400px] bg-purple-200/20 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="relative max-w-6xl mx-auto px-6 py-16 sm:py-24">

            {{-- Hero Section --}}
            <div class="text-center max-w-3xl mx-auto space-y-6">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-[2rem] bg-white shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-slate-100 text-5xl transform transition hover:rotate-12 duration-300 select-none">
                    🎲
                </div>

                <h1 class="text-5xl md:text-7xl font-black tracking-tight text-slate-900">
                    Nexa<span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Spin</span>
                </h1>

                <p class="text-lg md:text-xl text-slate-600 font-medium max-w-xl mx-auto leading-relaxed">
                    Créez des tirages aléatoires, choisissez votre mode, et laissez la roue décider. 🚀
                </p>
            </div>

            {{-- Séparateur élégant --}}
            <div class="mt-20 mb-12 flex items-center gap-4">
                <div class="h-px flex-1 bg-gradient-to-r from-transparent to-slate-200"></div>
                <span class="text-xs font-bold uppercase tracking-widest text-slate-400 select-none">
                    Choisissez votre expérience
                </span>
                <div class="h-px flex-1 bg-gradient-to-l from-transparent to-slate-200"></div>
            </div>

            {{-- Grid des Cartes --}}
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3 items-stretch">
                @foreach($modes as $mode)

                <div class="group relative overflow-hidden rounded-[2rem] bg-white border border-slate-200/80 p-8 shadow-sm transition-all duration-300 {{ $mode['shadow'] }} {{ $mode['available'] ? 'hover:-translate-y-2 hover:border-slate-300' : 'bg-slate-50/50 opacity-75' }} flex flex-col justify-between">

                    {{-- Glow Background dynamique au survol --}}
                    <div class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b {{ $mode['color'] }} opacity-[0.04] group-hover:opacity-[0.08] transition-opacity duration-300 pointer-events-none"></div>

                    <div class="relative z-10 space-y-6">
                        {{-- Top Header (Badge + Icône) --}}
                        <div class="flex justify-between items-center">
                            <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 text-4xl shadow-sm transition-transform duration-300 {{ $mode['available'] ? 'group-hover:scale-110 group-hover:rotate-3' : '' }} select-none">
                                {{ $mode['icon'] }}
                            </div>

                            @if($mode['available'])
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Disponible
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500 border border-slate-200/60">
                                🔒 Bientôt
                            </span>
                            @endif
                        </div>

                        {{-- Titre & Description --}}
                        <div class="space-y-2">
                            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">
                                {{ $mode['title'] }}
                            </h2>
                            <p class="text-sm leading-6 text-slate-500 font-medium">
                                {{ $mode['description'] }}
                            </p>
                        </div>
                    </div>

                    {{-- Footer Action (Boutons ajustés) --}}
                    <div class="relative z-10 mt-8">
                        @if($mode['available'])
                        <a href="{{ $mode['route'] }}" class="relative inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r {{ $mode['color'] }} px-5 py-3.5 font-bold text-white shadow-md shadow-indigo-500/10 transition-all duration-300 hover:shadow-lg active:scale-[0.98] group/btn">
                            <span>🚀 Commencer</span>
                            <svg class="w-4 h-4 transition-transform duration-300 group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                        @else
                        <div class="flex items-center justify-center gap-2 rounded-2xl border border-dashed border-slate-200 px-5 py-3.5 font-semibold text-slate-400 bg-slate-100/50 select-none">
                            Indisponible
                        </div>
                        @endif
                    </div>

                </div>
                @endforeach
            </div>

            {{-- Footer Info --}}
            <div class="mt-16 rounded-3xl bg-white/60 backdrop-blur-md border border-slate-200/60 p-6 text-center shadow-sm max-w-4xl mx-auto">
                <p class="text-sm text-slate-500 font-medium leading-relaxed">
                    🎉 Organisez vos concours, jeux, décisions aléatoires et tirages au sort en quelques secondes.
                </p>
            </div>

        </div>
    </div>
</x-layouts.app>
