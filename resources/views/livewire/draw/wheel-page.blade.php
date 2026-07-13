<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50/50 relative overflow-hidden antialiased pb-24 selection:bg-indigo-500 selection:text-white">

    <!-- Orbes de lumière pour la profondeur -->
    <div class="absolute top-[-10%] left-[-10%] w-[600px] h-[600px] bg-indigo-200/20 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-purple-200/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-8 relative z-10">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-5 bg-white/40 backdrop-blur-md border border-slate-200/50 rounded-3xl p-6 shadow-sm">
            <div class="space-y-1">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-indigo-600 transition group">
                    <span class="transition-transform group-hover:-translate-x-1">←</span> Retour aux modes
                </a>
                <h1 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-2">
                    <span>🎡</span> Roue classique
                </h1>
                <p class="text-sm font-medium text-slate-500">
                    Un seul tour de roue pour choisir votre gagnant.
                </p>
            </div>

            <!-- Badge Compteur de participants ultra-propre -->
            <div class="inline-flex items-center gap-3 bg-slate-900 text-white rounded-2xl px-5 py-3 self-start sm:self-auto shadow-md shadow-slate-950/10">
                <span class="text-xl">👥</span>
                <div class="text-left leading-none">
                    <div class="text-lg font-black">{{ count($participants) }}</div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">participants</div>
                </div>
            </div>
        </div>

        {{-- CONTENU PRINCIPAL --}}
        <div class="grid lg:grid-cols-4 gap-8 items-start">

            {{-- COLONNE GAUCHE : PARTICIPANTS & BOUTON ACTION --}}
            <div class="lg:col-span-1 space-y-4">
                <div class="rounded-3xl bg-white border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                    <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                        <h2 class="font-bold text-slate-900 flex items-center gap-2">
                            <span>📝</span> Gestion des inscrits
                        </h2>
                    </div>

                    <div class="p-5 border-b border-slate-100">
                        <x-draw.participant-form :participants="$participants" :colors="collect($this->segments)->pluck('color')->all()" :error="$error" />
                    </div>

                    <!-- Liste défilante stylisée avec barre de défilement discrète -->
                    <div class="max-h-[340px] overflow-y-auto divide-y divide-slate-50 pr-1">
                        @forelse($this->segments as $segment)
                        <div class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50/80 transition group">
                            <span class="w-3 h-3 rounded-full shrink-0 shadow-sm transition-transform group-hover:scale-110" style="background: {{ $segment['color'] }}"></span>
                            <span class="truncate text-sm font-medium text-slate-700">
                                {{ $segment['name'] }}
                            </span>
                        </div>
                        @empty
                        <div class="p-8 text-center text-sm font-medium text-slate-400">
                            Aucun participant pour le moment.
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- BOUTON DE TIRAGE PLACÉ ICI (Beaucoup plus ergonomique sur desktop comme mobile) --}}
                <button wire:click="draw" x-data="{ spinning: false }" x-on:wheel-spin.window="spinning = true" x-on:wheel-spin-finished.window="spinning = false" x-bind:disabled="spinning || {{ count($participants) === 0 ? 'true' : 'false' }}" class="w-full relative inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 py-4 text-base font-bold text-white shadow-xl shadow-indigo-600/10 transition-all duration-300 hover:shadow-indigo-600/20 hover:scale-[1.01] active:scale-[0.99] disabled:from-slate-200 disabled:to-slate-300 disabled:text-slate-400 disabled:shadow-none disabled:pointer-events-none group">
                    <div class="absolute inset-0 rounded-2xl bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>

                    <span x-show="!spinning" class="flex items-center gap-2">
                        ⚡ Lancer le tirage
                    </span>

                    <span x-show="spinning" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        La roue tourne...
                    </span>
                </button>
            </div>

            {{-- COLONNE DROITE : L'ECRAN DE LA ROUE --}}
            <div class="lg:col-span-3">
                <div class="rounded-[2rem] bg-white border border-slate-200/80 shadow-sm p-8 flex flex-col justify-between min-h-[500px]" x-data="{ finished: false }" x-on:wheel-spin.window="finished = false" x-on:wheel-spin-finished.window="finished = true">

                    {{-- Zone de la roue centrée --}}
                    <div class="flex-1 flex items-center justify-center py-4">
                        <x-draw.wheel :segments="$this->segments" :show-labels="$this->showLabelsOnWheel()" />
                    </div>

                    {{-- ENCART GAGNANT ANIMÉ --}}
                    @if($result)
                    <div x-show="finished" x-cloak x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0" class="mt-6 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-6 text-center text-white shadow-xl shadow-emerald-500/20 relative overflow-hidden group">
                        <!-- Effet lumineux interactif en fond -->
                        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                        <div class="text-5xl transform transition group-hover:scale-110 duration-300">🏆</div>
                        <p class="mt-2 uppercase text-xs font-bold tracking-widest text-emerald-100/90 drop-shadow-sm">
                            Gagnant
                        </p>
                        <p class="mt-1 text-4xl font-black tracking-tight truncate drop-shadow-md">
                            {{ $result }}
                        </p>
                        <p class="mt-2 text-sm font-medium text-emerald-50/80">
                            🎉 Félicitations !
                        </p>
                    </div>
                    @endif

                </div>
            </div>

        </div>

    </div>
</div>
