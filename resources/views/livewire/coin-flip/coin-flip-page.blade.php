<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-amber-50/60 relative overflow-hidden antialiased pb-24 selection:bg-amber-500 selection:text-white" x-data="{
         flipping: false,
         finished: false
     }" x-on:coin-flip.window="flipping = true; finished = false" x-on:coin-flip-finished.window="flipping = false; finished = true" x-on:coin-flip-reset.window="finished = false">

    {{-- EFFETS DE LUMIÈRE D'ARRIÈRE-PLAN --}}
    <div class="absolute top-[-10%] left-[-10%] w-[600px] h-[600px] bg-amber-200/20 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-yellow-200/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-8 relative z-10">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5 bg-white/40 backdrop-blur-md border border-slate-200/50 rounded-3xl p-6 shadow-sm">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-amber-600 transition">
                    ← Retour aux modes
                </a>
                <h1 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-3 mt-2">
                    🪙 Pile ou face
                </h1>
                <p class="text-sm font-medium text-slate-500 mt-1">
                    Lancez la pièce (une fois ou en série), pariez sur le résultat, et suivez l'historique.
                </p>
            </div>

            {{-- MINI COMPTEUR --}}
            <div class="flex gap-3">
                <div class="bg-white border border-slate-200/50 rounded-2xl px-5 py-3 shadow-sm min-w-[110px] text-center">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Tirages
                    </div>
                    <div class="text-2xl font-black text-amber-600 mt-0.5">
                        {{ $this->totalFlips() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ALERTES ERREURS --}}
        @if($error)
        <div class="rounded-2xl bg-red-50 border border-red-200 p-4 text-red-600">
            ⚠️ {{ $error }}
        </div>
        @endif

        {{-- GRILLE PRINCIPALE --}}
        <div class="grid lg:grid-cols-12 gap-8 items-start">

            {{-- COLONNE GAUCHE : LA PIÈCE --}}
            <div class="lg:col-span-7">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/50 p-8 flex flex-col items-center min-h-[420px] justify-center">

                    <div class="relative" wire:key="coin-wrapper">
                        {{-- Halo lumineux derrière la pièce --}}
                        <div class="absolute inset-0 bg-amber-400/10 blur-3xl rounded-full"></div>
                        <x-coin-flip.coin />
                    </div>

                    {{-- RÉSULTAT --}}
                    @if($result)
                    <div x-show="finished" x-cloak class="mt-8 px-8 py-3.5 rounded-full bg-gradient-to-r from-amber-500 to-yellow-500 text-white font-black text-lg shadow-md animate-bounce flex items-center gap-2">
                        🪙 Résultat : {{ $result }}
                    </div>
                    @endif

                    {{-- RETOUR SUR LE PARI --}}
                    @if($lastGuessCorrect !== null)
                    <div x-show="finished" x-cloak @class([
                        'mt-3 px-6 py-2.5 rounded-full font-bold text-sm shadow-sm flex items-center gap-2 border',
                        'bg-emerald-50 text-emerald-600 border-emerald-200' => $lastGuessCorrect,
                        'bg-red-50 text-red-600 border-red-200' => ! $lastGuessCorrect,
                        ])>
                        @if($lastGuessCorrect)
                        ✅ Bien deviné, vous aviez parié sur {{ $guess }} !
                        @else
                        ❌ Raté, vous aviez parié sur {{ $guess }}
                        @endif
                    </div>
                    @endif


                    {{-- BOUTON D'ACTION PRINCIPAL --}}
                    <button wire:click="flip" wire:loading.attr="disabled" wire:target="flip,flipMultiple" :disabled="flipping" class="mt-4 w-full max-w-xs rounded-2xl py-4 font-black text-white shadow-md bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 active:scale-[0.98] transition disabled:opacity-50 disabled:pointer-events-none">
                        <span x-show="!flipping">
                            🪙 Lancer la pièce
                        </span>

                        <span x-show="flipping" x-cloak>
                            🪙 La pièce tourne...
                        </span>
                    </button>

                </div>
            </div>

            {{-- COLONNE DROITE : AUTO + STATISTIQUES + HISTORIQUE --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- TIRAGE AUTOMATIQUE --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/50 p-5">
                    <h2 class="font-black text-lg text-slate-800 mb-1 flex items-center gap-2">
                        <span>🎲</span> Tirage automatique
                    </h2>
                    <p class="text-xs text-slate-400 mb-4">
                        Enchaînez plusieurs tirages d'un coup, sans re-cliquer à chaque fois.
                    </p>

                    <div class="flex items-center gap-3">
                        <input type="number" wire:model="autoFlipCount" min="2" max="100" class="w-24 rounded-xl border border-slate-200 px-3 py-2.5 text-center font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-400/50 focus:border-amber-400">

                        <button wire:click="flipMultiple" wire:loading.attr="disabled" wire:target="flip,flipMultiple" :disabled="flipping" class="flex-1 rounded-xl py-2.5 font-bold text-white shadow-sm bg-slate-800 hover:bg-slate-900 active:scale-[0.98] transition disabled:opacity-50 disabled:pointer-events-none">
                            <span x-show="!flipping">
                                Lancer {{ $autoFlipCount }} tirages
                            </span>
                            <span x-show="flipping" x-cloak>
                                En cours...
                            </span>
                        </button>
                    </div>
                </div>

                {{-- STATISTIQUES --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/50 p-5">
                    <h2 class="font-black text-lg text-slate-800 mb-4 flex items-center gap-2">
                        <span>📊</span> Statistiques de la session
                    </h2>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-amber-50/60 border border-amber-100 rounded-2xl px-4 py-3 text-center">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-amber-500">
                                Face
                            </div>
                            <div class="text-2xl font-black text-amber-600 mt-0.5">
                                {{ $this->faceCount() }}
                            </div>
                        </div>

                        <div class="bg-slate-100/60 border border-slate-200 rounded-2xl px-4 py-3 text-center">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                Pile
                            </div>
                            <div class="text-2xl font-black text-slate-600 mt-0.5">
                                {{ $this->pileCount() }}
                            </div>
                        </div>
                    </div>
                    </div>


                {{-- HISTORIQUE DES TIRAGES --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5 transition-all">
                    {{-- En-tête avec compteur dynamique --}}
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-extrabold text-slate-800 flex items-center gap-2 text-base">
                            <span class="text-lg">📜</span> Historique
                        </h3>

                        @if(count($history))
                        <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 px-2.5 py-0.5 rounded-full">
                            {{ count($history) }} / {{ \App\Livewire\CoinFlip\CoinFlipPage::MAX_HISTORY ?? 200 }} max
                        </span>
                        @endif
                    </div>

                    @if(count($history))
                    {{-- Liste déroulante stylisée --}}
                    <div class="space-y-2 max-h-[300px] overflow-y-auto pr-1.5 custom-scrollbar">
                        @foreach(array_reverse($history, true) as $index => $face)
                        <div @class([ 'flex items-center justify-between rounded-2xl px-4 py-2.5 text-sm font-semibold transition-all duration-200 border' , 'bg-amber-50/60 border-amber-200/50 text-amber-900 hover:bg-amber-100/50'=> $face === 'Face',
                            'bg-slate-50 border-slate-200/60 text-slate-800 hover:bg-slate-100/70' => $face === 'Pile',
                            ])>
                            <div class="flex items-center gap-2.5">
                                {{-- Indicateur visuel coloré --}}
                                <span @class([ 'w-2 h-2 rounded-full' , 'bg-amber-500 shadow-sm shadow-amber-500/50'=> $face === 'Face',
                                    'bg-slate-400 shadow-sm shadow-slate-400/50' => $face === 'Pile',
                                    ])></span>

                                <span class="font-bold tracking-wide">
                                    {{ $face }}
                                </span>
                            </div>

                            <span class="font-mono text-[11px] font-medium text-slate-400 bg-white/80 px-2 py-0.5 rounded-md border border-slate-100">
                                #{{ $index + 1 }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    {{-- État vide (Empty State) --}}
                    <div class="py-8 text-center border-2 border-dashed border-slate-100 rounded-2xl">
                        <p class="text-2xl mb-1">🪙</p>
                        <p class="text-sm font-medium text-slate-500">Aucun tirage pour l'instant</p>
                        <p class="text-xs text-slate-400 mt-0.5">Lancez la pièce pour démarrer l'historique</p>
                    </div>
                    @endif
                </div>


                {{-- RÉINITIALISER --}}
                @if(count($history))
                <button wire:click="resetHistory" class="w-full rounded-2xl border border-slate-200/50 bg-white py-4 font-bold shadow-sm text-slate-700 hover:bg-slate-50 active:scale-[0.98] transition">
                    🔄 Réinitialiser l'historique
                </button>
                @endif

            </div>

        </div>

    </div>
</div>
