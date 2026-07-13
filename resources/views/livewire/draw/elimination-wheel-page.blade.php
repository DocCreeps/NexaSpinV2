<div class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-orange-50 px-4 py-8" x-data="{ busy: false, autoMode: @entangle('autoMode') }" x-on:wheel-spin.window="busy = true" x-on:wheel-spin-finished.window="busy = false; $wire.confirmElimination()" x-on:elimination-confirmed.window="
         if (autoMode && !$wire.winner) {
             setTimeout(() => {
                 if (autoMode && !busy && !$wire.winner) {
                     $wire.eliminateNext();
                 }
             }, 2000);
         }
     ">

    <div class="max-w-7xl mx-auto space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-orange-600 transition">
                    ← Modes de tirage
                </a>
                <h1 class="mt-3 text-4xl font-black text-slate-900 flex items-center gap-3">
                    ⚔️ Roue par élimination
                </h1>
                <p class="text-slate-500 mt-2">
                    Chaque tour élimine un joueur jusqu'au dernier survivant.
                </p>
            </div>

            <div class="flex gap-3">
                <div class="bg-white rounded-2xl shadow border px-5 py-3 text-center">
                    <div class="text-xs text-slate-400 uppercase">Survivants</div>
                    <div class="text-3xl font-black text-emerald-600">
                        {{ count($participants) }}
                    </div>
                </div>

                <div class="bg-red-50 rounded-2xl border border-red-100 px-5 py-3 text-center">
                    <div class="text-xs text-red-400 uppercase">Sorties</div>
                    <div class="text-3xl font-black text-red-600">
                        {{ count($eliminated) }}
                    </div>
                </div>
            </div>
        </div>

        @if($error)
        <div class="rounded-2xl bg-red-50 border border-red-200 p-4 text-red-600">
            ⚠️ {{ $error }}
        </div>
        @endif

        <div class="grid lg:grid-cols-12 gap-8">

            {{-- PANNEAU DE GAUCHE : LA ROUE --}}
            <div class="lg:col-span-7">
                <div class="bg-white rounded-[2rem] shadow-xl border p-8 flex flex-col items-center">

                    <div class="relative" wire:key="wheel-wrapper">
                        <div class="absolute inset-0 bg-orange-400/20 blur-3xl rounded-full"></div>
                        <x-draw.wheel :segments="$this->segments" :show-labels="$this->showLabelsOnWheel()" />
                    </div>

                    @if($pendingElimination)
                    <div class="mt-6 px-5 py-2 rounded-full bg-red-50 border border-red-200 text-red-600 font-bold animate-pulse">
                        🎯 Cible : {{ $pendingElimination }}
                    </div>
                    @elseif($winner)
                    <div class="mt-6 px-6 py-3 rounded-full bg-yellow-100 border border-yellow-200 text-yellow-700 font-black animate-bounce">
                        🏆 Gagnant : {{ $winner }} !
                    </div>
                    @endif
                </div>
            </div>

            {{-- PANNEAU DE DROITE : CONFIG & ACTIONS --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- FORMULAIRE PARTICIPANTS --}}
                <div class="bg-white rounded-[2rem] shadow-lg border p-6">
                    <h2 class="font-black text-xl mb-4">👥 Configuration</h2>

                    @php
                    $indexedColors = [];
                    foreach ($participants as $index => $name) {
                    $indexedColors[$index] = $colors[$name] ?? '#ccc';
                    }
                    @endphp

                    <x-draw.participant-form :participants="$participants" :colors="$indexedColors" :locked="$this->started()" :error="$error" />
                </div>

                {{-- TOGGLE AUTO MODE --}}
                @if(!$winner && count($participants) >= 2)
                <div class="bg-white rounded-2xl border shadow-sm p-4 flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="font-bold text-sm text-slate-800">Mode automatique</span>
                        <span class="text-xs text-slate-400">Enchaîne les lancers sans s'arrêter</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" x-model="autoMode" class="sr-only peer" :disabled="$wire.winner">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                    </label>
                </div>
                @endif

                {{-- BOUTONS D'ACTION INTERACTIFS --}}
                @if(!$winner)
                @if($autoMode)
                <button wire:click="$set('autoMode', false)" class="w-full rounded-2xl py-4 font-black text-white shadow-lg transition bg-slate-800 hover:bg-slate-900">
                    ⏸️ Mettre en pause le mode auto
                </button>
                @else
                <button wire:click="handleAction" x-bind:disabled="busy" class="w-full rounded-2xl py-4 font-black text-white shadow-lg transition disabled:opacity-50 bg-gradient-to-r from-red-600 to-orange-500">
                    <span x-show="!busy">
                        @if($this->started())
                        ❌ Éliminer le prochain
                        @else
                        🚀 Commencer
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
                <div class="bg-white rounded-[2rem] border shadow p-6">
                    <h3 class="font-black mb-4">💀 Historique</h3>
                    <div class="space-y-2 max-h-[200px] overflow-y-auto">
                        @foreach(array_reverse($eliminated) as $player)
                        <div class="flex items-center gap-3 bg-red-50 rounded-xl p-3 text-sm">
                            <span class="font-bold text-red-500">#{{ count($eliminated) - array_search($player, $eliminated) }}</span>
                            <span class="line-through text-slate-500 font-medium">{{ $player }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($winner)
                <button wire:click="restart" class="w-full rounded-2xl border bg-white py-4 font-bold shadow hover:bg-slate-50 transition">
                    🔄 Recommencer une partie
                </button>
                @endif

            </div>
        </div>
    </div>
</div>
