<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-orange-50/60 relative overflow-hidden antialiased pb-24 selection:bg-orange-500 selection:text-white" x-data="{
        busy: false,
        autoMode: @entangle('autoMode').live
    }" x-on:wheel-spin.window="busy = true" x-on:wheel-spin-finished.window="busy = false; $wire.confirmElimination()" x-on:elimination-confirmed.window="
        if(autoMode && !$wire.winner) {
            setTimeout(() => {
                if(autoMode && !busy && !$wire.winner) {
                    $wire.eliminateNext()
                }
            }, 2000)
        }
    ">
    {{-- EFFETS DE LUMIÈRE D'ARRIÈRE-PLAN --}}
    <div class="absolute top-[-10%] left-[-10%] w-[600px] h-[600px] bg-orange-200/20 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-rose-200/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-8 relative z-10">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5 bg-white/40 backdrop-blur-md border border-slate-200/50 rounded-3xl p-6 shadow-sm">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-orange-600 transition">
                    ← Modes de tirage
                </a>
                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                    ⚔️ Roue par élimination
                </h1>
                <p class="text-sm font-medium text-slate-500 mt-1">
                    Chaque tour élimine un joueur jusqu'au dernier survivant.
                </p>
            </div>

            {{-- MINI COMPTEURS --}}
            <div class="flex gap-3">
                {{-- SURVIVANTS --}}
                <div class="bg-white border border-slate-200/50 rounded-2xl px-5 py-3 shadow-sm min-w-[110px] text-center">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Survivants
                    </div>
                    <div class="text-2xl font-black text-emerald-600 mt-0.5">
                        {{ count($participants) }}
                    </div>
                </div>

                {{-- ÉLIMINÉS --}}
                <div class="bg-red-50/50 border border-red-100 rounded-2xl px-5 py-3 shadow-sm min-w-[110px] text-center">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-red-400">
                        Sorties
                    </div>
                    <div class="text-2xl font-black text-red-600 mt-0.5">
                        {{ count($eliminated) }}
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

            {{-- COLONNE GAUCHE : LA ROUE --}}
            <div class="lg:col-span-7">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/50 p-8 flex flex-col items-center min-h-[520px] justify-center">

                    <div class="relative" wire:key="wheel-wrapper">
                        {{-- Halo lumineux derrière la roue --}}
                        <div class="absolute inset-0 bg-orange-400/10 blur-3xl rounded-full"></div>
                        <x-draw.wheel :segments="$this->segments" :show-labels="$this->showLabelsOnWheel()" />
                    </div>

                    {{-- ÉTAT DU JEU (CIBLE / GAGNANT) --}}
                    @if($pendingElimination)
                    <div class="mt-8 px-6 py-3 rounded-full bg-red-50 border border-red-200 text-red-600 font-bold text-sm tracking-wide shadow-sm animate-pulse flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                        </span>
                        🎯 Cible : {{ $pendingElimination }}
                    </div>
                    @elseif($winner)
                    <div class="mt-8 px-8 py-3.5 rounded-full bg-gradient-to-r from-amber-500 to-yellow-500 text-white font-black text-lg shadow-md animate-bounce flex items-center gap-2">
                        🏆 Gagnant : {{ $winner }}
                    </div>
                    @endif

                </div>
            </div>

            {{-- COLONNE DROITE : LE PANNEAU DE CONTRÔLE --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- CARTE UNIQUE DE CONFIGURATION --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200/50 p-5 overflow-hidden">
                    <h2 class="font-black text-lg text-slate-800 mb-4 flex items-center gap-2">
                        <span>👥</span> Configuration du salon
                    </h2>

                    @php
                    $indexedColors = [];
                    foreach($participants as $index => $name) {
                    $indexedColors[$index] = $colors[$name] ?? '#ccc';
                    }
                    @endphp

                    <x-draw.participant-form :participants="$participants" :colors="$indexedColors" :locked="$this->started()" :error="$error" />
                </div>

                {{-- MODE AUTO --}}
                @if(!$winner && count($participants) >= 2)
                <div class="bg-white rounded-3xl border border-slate-200/50 shadow-sm p-5 flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="font-bold text-sm text-slate-800">
                            Mode automatique
                        </span>
                        <span class="text-xs text-slate-400">
                            Enchaîne les éliminations automatiquement
                        </span>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" x-model="autoMode" class="sr-only peer" :disabled="$wire.winner">
                        <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-orange-500 after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                    </label>
                </div>
                @endif

                {{-- BOUTON D'ACTION PRINCIPAL --}}
                @if(!$winner)
                @if($autoMode)
                <button wire:click="$set('autoMode', false)" class="w-full rounded-2xl py-4 font-black text-white shadow-md bg-slate-800 hover:bg-slate-900 active:scale-[0.98] transition">
                    ⏸️ Mettre en pause
                </button>
                @else
                    <button wire:click="handleAction" wire:loading.attr="disabled" wire:target="handleAction,eliminateNext" x-bind:disabled="busy || {{ (!$this->started() && count($participants) < 5) ? 'true' : 'false' }}" @disabled(!$this->started() && count($participants) < 2) class="w-full rounded-2xl py-4 font-black text-white shadow-md bg-gradient-to-r from-red-600 to-orange-500 hover:from-red-700 hover:to-orange-600 disabled:from-slate-300 disabled:to-slate-400 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98] transition disabled:pointer-events-none">
                        <span x-show="!busy">
                            @if(!$this->started() && count($participants) < 5) 👥 Ajoutez au moins 5 participants @elseif($this->started())
                                ❌ Éliminer le prochain
                                @else
                                🚀 Commencer la partie
                                @endif
                        </span>
                        <span x-show="busy" x-cloak>
                            🎡 La roue tourne...
                        </span>
                </button>
                @endif
                @endif


                {{-- HISTORIQUE DES ÉLIMINATIONS --}}
                @if(count($eliminated))
                <div class="bg-white rounded-3xl border border-slate-200/50 shadow-sm p-5">
                    <h3 class="font-black text-slate-800 mb-4 flex items-center gap-2">
                        <span>💀</span> Ordre d'élimination
                    </h3>

                    <div class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
                        @foreach(array_reverse($eliminated, true) as $index => $player)
                        <div class="flex items-center justify-between bg-red-50/40 border border-red-100/50 rounded-xl px-4 py-3 text-sm hover:bg-red-50 transition">
                            <span class="font-semibold line-through text-slate-500">
                                {{ $player }}
                            </span>

                            <span class="font-bold text-xs text-red-500 bg-red-100/50 px-2.5 py-1 rounded-lg">
                                Mort #{{ $index + 1 }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- RECOMMENCER UNE PARTIE --}}
                @if($winner)
                <button wire:click="restart" class="w-full rounded-2xl border border-slate-200/50 bg-white py-4 font-bold shadow-sm text-slate-700 hover:bg-slate-50 active:scale-[0.98] transition">
                    🔄 Recommencer une partie
                </button>
                @endif

            </div>

        </div>

    </div>
</div>
