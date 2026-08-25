<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink" x-data="{
         activeWinner: null,
         modalOpen: false,
         showForm: false,
         isPaused: false
     }" @if($drawing && $slowMode) x-init="let timer = setInterval(() => { if (!isPaused && $wire.autoAdvance) $wire.drawNextStep() }, 1200)" @endif>

    <div class="mx-auto max-w-7xl space-y-4 px-4 py-4 sm:space-y-6 sm:px-6 sm:py-6">

        {{-- HEADER COMPACT --}}
        <header class="flex flex-col gap-2 border-b-4 border-ink pb-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-xs font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>
                <h1 class="mt-1 font-display text-3xl leading-none text-ink sm:text-4xl">Tombola</h1>
            </div>

            <div class="card-hard min-w-[120px] self-start rounded-xl border-2 border-ink bg-panel px-4 py-2 text-center">
                <p class="font-mono text-[9px] uppercase tracking-widest text-subtle">Participants</p>
                <p class="mt-0.5 font-display text-xl text-ink">{{ count($participants) }}</p>
            </div>
        </header>

        {{-- ERREURS --}}
        @if($error)
        <div role="alert" class="rounded-xl border-2 border-ink bg-danger/10 px-4 py-2.5 text-xs font-semibold text-danger">
            ⚠ {{ $error }}
        </div>
        @endif

        {{-- LAYOUT PRINCIPAL --}}
        <div class="grid items-start gap-5 lg:grid-cols-12">

            {{-- 1. COLONNE GAUCHE : GAGNANTS (5 COLS) --}}
            <div class="lg:col-span-5">
                <section class="card-hard flex h-[500px] flex-col rounded-2xl border-2 border-ink bg-panel p-4 sm:p-5">
                    <div class="mb-3 flex items-center justify-between border-b-2 border-ink/10 pb-2.5 shrink-0">
                        <h2 class="font-display text-base text-ink">Gagnants</h2>

                        @if($drawing)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-ink bg-amber-200 px-2.5 py-0.5 font-mono text-[9px] font-bold uppercase text-ink animate-pulse">
                            ⏳ Lot {{ count($winners) + 1 }}/{{ $lotsCount }}
                        </span>
                        @endif
                    </div>

                    @if(count($winners))
                    @php
                    $groupedWinners = [];
                    foreach ($winners as $lotIndex => $winnerName) {
                    $groupedWinners[$winnerName][] = $lotIndex + 1;
                    }
                    @endphp

                    <ul class="flex-1 space-y-2 overflow-y-auto pr-1">
                        @foreach($groupedWinners as $winnerName => $lots)
                        <li class="flex items-center justify-between gap-3 rounded-xl border-2 border-ink bg-wash px-3.5 py-2.5">
                            <span class="font-display text-sm tracking-wide text-ink truncate">{{ $winnerName }}</span>

                            @if(count($lots) === 1)
                            <span class="rounded-lg border border-ink/30 bg-panel px-2 py-0.5 font-mono text-xs font-semibold text-muted shrink-0">
                                Lot #{{ $lots[0] }}
                            </span>
                            @else
                            <button type="button" @click="activeWinner = { name: '{{ addslashes($winnerName) }}', lots: {{ json_encode($lots) }} }; modalOpen = true" class="inline-flex items-center gap-1.5 rounded-lg border-2 border-ink bg-secondary px-2.5 py-0.5 font-mono text-xs font-bold text-ink transition hover:scale-105 active:scale-95 shadow-sm shrink-0">
                                🎁 {{ count($lots) }} lots
                                <span class="text-[9px]">👁️</span>
                            </button>
                            @endif
                        </li>
                        @endforeach
                    </ul>

                    {{-- COMMANDES DE TIRAGE --}}
                    @if($drawing)
                    <div class="mt-3 flex flex-wrap gap-2 pt-2 border-t-2 border-ink/10 shrink-0">
                        @if($autoAdvance)
                        {{-- MODE SUSPENSE AUTOMATIQUE --}}
                        <button type="button" @click="isPaused = !isPaused" class="btn-press flex-1 rounded-xl border-2 border-ink bg-amber-300 py-2.5 font-display text-xs text-ink">
                            <span x-text="isPaused ? '▶ Reprendre' : '⏸ Pause'"></span>
                        </button>

                        <button x-show="isPaused" type="button" wire:click="drawNextStep" class="btn-press rounded-xl border-2 border-ink bg-primary px-3 py-2.5 font-display text-xs text-white">
                            ⏭ +1 Lot
                        </button>
                        @else
                        {{-- MODE SUSPENSE MANUEL (L'utilisateur valide chaque lot) --}}
                        <button type="button" wire:click="drawNextStep" class="btn-press flex-1 rounded-xl border-2 border-ink bg-primary py-2.5 font-display text-xs text-white">
                            🎟️ Tirer le lot suivant
                        </button>
                        @endif

                        <button type="button" wire:click="stop" class="rounded-xl border-2 border-ink bg-wash px-3 py-2.5 font-display text-xs text-ink">
                            Stopper
                        </button>
                    </div>
                    @elseif(! $drawing && count($winners) > 0)
                    <div class="mt-3 flex gap-2 pt-2 border-t-2 border-ink/10 shrink-0">
                        <button type="button" wire:click="newDraw" class="w-full rounded-xl border-2 border-ink bg-wash py-2.5 font-display text-xs text-ink transition hover:bg-panel">
                            ↻ Recommencer
                        </button>
                    </div>
                    @endif

                    @else
                    <div class="m-auto text-center py-8">
                        <p class="mb-2 text-3xl">🎟️</p>
                        <p class="text-xs font-medium text-muted">Lancez la tombola pour afficher les gagnants</p>
                    </div>
                    @endif
                </section>
            </div>

            {{-- 2. COLONNE CENTRALE : TICKETS / ÉDITION --}}
            <div class="lg:col-span-4">
                <section class="card-hard relative flex h-[500px] flex-col rounded-2xl border-2 border-ink bg-panel p-4 sm:p-5 overflow-hidden">

                    @if($drawing || count($winners) > 0)
                    {{-- VUE SUIVI DES TICKETS --}}
                    <div x-show="!showForm" class="flex h-full flex-col justify-between min-h-0">
                        <div class="flex-1 flex flex-col min-h-0">
                            <div class="mb-3 flex items-center justify-between border-b-2 border-ink/10 pb-2.5 shrink-0">
                                <h2 class="font-display text-base text-ink">Suivi des tickets</h2>
                                <span class="font-mono text-[9px] uppercase tracking-widest text-subtle">Restant / Total</span>
                            </div>

                            <ul class="flex-1 space-y-1.5 overflow-y-auto pr-1">
                                @foreach($participants as $idx => $name)
                                @php
                                $initialWeight = $participantWeights[$idx] ?? 1;
                                $remIndex = array_search($name, $remainingPool, true);
                                $currentWeight = ($remIndex !== false) ? ($remainingWeights[$remIndex] ?? 0) : 0;
                                @endphp
                                <li class="flex items-center justify-between rounded-xl border-2 border-ink/20 bg-wash px-3 py-2 text-xs">
                                    <span class="font-semibold text-ink truncate max-w-[130px]">{{ $name }}</span>
                                    <span @class([ 'font-mono font-bold px-2 py-0.5 rounded border text-[10px] shrink-0' , 'bg-emerald-100 text-emerald-900 border-emerald-400'=> $currentWeight === $initialWeight,
                                        'bg-amber-100 text-amber-900 border-amber-400' => $currentWeight > 0 && $currentWeight < $initialWeight, 'bg-rose-100 text-rose-800 border-rose-300 line-through opacity-60'=> $currentWeight === 0,
                                            ])>
                                            {{ $currentWeight }} / {{ $initialWeight }} ticket(s)
                                    </span>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                        @if(! $drawing)
                        <button type="button" @click="showForm = true" class="mt-3 w-full shrink-0 rounded-xl border-2 border-ink bg-wash py-2.5 font-mono text-[10px] uppercase font-bold text-ink transition hover:bg-secondary">
                            ⚙️ Éditer les participants
                        </button>
                        @endif
                    </div>

                    {{-- VUE FORMULAIRE D'ÉDITION --}}
                    <div x-show="showForm" x-cloak class="flex h-full flex-col justify-between min-h-0">
                        <div class="flex-1 flex flex-col min-h-0">
                            <div class="mb-3 flex items-center justify-between border-b-2 border-ink/10 pb-2.5 shrink-0">
                                <h2 class="font-display text-base text-ink">Participants</h2>
                                <span class="font-mono text-[9px] uppercase tracking-widest text-subtle">Édition</span>
                            </div>

                            <div class="flex-1 overflow-y-auto pr-1">
                                <x-draw.participant-form :participants="$participants" :weights="$participantWeights" :locked="$drawing" :error="$error" />
                            </div>
                        </div>

                        <button type="button" @click="showForm = false" class="mt-3 w-full shrink-0 rounded-xl border-2 border-ink bg-wash py-2.5 font-mono text-[10px] uppercase font-bold text-ink transition hover:bg-panel">
                            ← Retour au suivi des tickets
                        </button>
                    </div>
                    @else
                    {{-- MODE INITIAL --}}
                    <div class="flex h-full flex-col justify-between min-h-0">
                        <div class="mb-3 flex items-center justify-between border-b-2 border-ink/10 pb-2.5 shrink-0">
                            <h2 class="font-display text-base text-ink">Participants</h2>
                            <span class="font-mono text-[9px] uppercase tracking-widest text-subtle">Édition</span>
                        </div>

                        <div class="flex-1 overflow-y-auto pr-1">
                            <x-draw.participant-form :participants="$participants" :weights="$participantWeights" :locked="$drawing" :error="$error" />
                        </div>
                    </div>
                    @endif

                </section>
            </div>

            {{-- 3. COLONNE DROITE : OPTIONS ET ACTIONS (3 COLS) --}}
            <div class="lg:col-span-3">
                <section class="card-hard flex h-[500px] flex-col justify-between rounded-2xl border-2 border-ink bg-panel p-4 sm:p-5">
                    <div>
                        <h2 class="mb-3 font-display text-base text-ink border-b-2 border-ink/10 pb-2.5">Options</h2>

                        {{-- SWITCH REMISE --}}
                        <div class="mb-2.5 flex items-start gap-2 rounded-xl border-2 border-ink bg-wash p-2.5">
                            <button type="button" wire:click="toggleAllowDuplicates" @disabled($drawing) @class([ 'relative mt-0.5 inline-flex h-4 w-7 shrink-0 cursor-pointer rounded-full border border-ink transition-colors disabled:opacity-50' , 'bg-primary'=> $allowDuplicates,
                                'bg-line' => ! $allowDuplicates,
                                ])>
                                <span @class([ 'inline-block h-2.5 w-2.5 rounded-full bg-white transition mt-0.5' , 'translate-x-3'=> $allowDuplicates,
                                    'translate-x-0.5' => ! $allowDuplicates,
                                    ])></span>
                            </button>
                            <div>
                                <span class="block text-xs font-semibold text-ink">Avec remise</span>
                                <span class="block text-[9px] text-muted">Conserve les tickets après victoire.</span>
                            </div>
                        </div>

                        {{-- SWITCH MODE SUSPENSE --}}
                        <div class="mb-2.5 flex items-start gap-2 rounded-xl border-2 border-ink bg-wash p-2.5">
                            <button type="button" wire:click="toggleSlowMode" @disabled($drawing) @class([ 'relative mt-0.5 inline-flex h-4 w-7 shrink-0 cursor-pointer rounded-full border border-ink transition-colors disabled:opacity-50' , 'bg-primary'=> $slowMode,
                                'bg-line' => ! $slowMode,
                                ])>
                                <span @class([ 'inline-block h-2.5 w-2.5 rounded-full bg-white transition mt-0.5' , 'translate-x-3'=> $slowMode,
                                    'translate-x-0.5' => ! $slowMode,
                                    ])></span>
                            </button>
                            <div>
                                <span class="block text-xs font-semibold text-ink">Mode suspense ⏳</span>
                                <span class="block text-[9px] text-muted">Révéler les lots un par un.</span>
                            </div>
                        </div>

                        {{-- SÉLECTION DU MODE DE DÉROULEMENT DU SUSPENSE (SI SLOWMODE EST ACTIF) --}}
                        @if($slowMode)
                        <div class="mb-3 rounded-xl border-2 border-ink bg-wash p-2">
                            <span class="mb-1.5 block font-mono text-[9px] uppercase tracking-widest text-subtle">Avancement</span>
                            <div class="inline-flex w-full rounded-lg border-2 border-ink bg-panel p-0.5">
                                <button type="button" wire:click="$set('autoAdvance', true)" @disabled($drawing) @class([ 'flex-1 rounded-md py-1 font-mono text-[9px] uppercase tracking-widest transition' , 'bg-ink text-white'=> $autoAdvance,
                                    'text-muted' => ! $autoAdvance,
                                    ])>
                                    ⚡ Auto
                                </button>
                                <button type="button" wire:click="$set('autoAdvance', false)" @disabled($drawing) @class([ 'flex-1 rounded-md py-1 font-mono text-[9px] uppercase tracking-widest transition' , 'bg-ink text-white'=> ! $autoAdvance,
                                    'text-muted' => $autoAdvance,
                                    ])>
                                    👆 Manuel
                                </button>
                            </div>
                        </div>
                        @endif

                        {{-- INPUT NOMBRE DE LOTS --}}
                        <label for="lotsCount" class="mb-1 block font-mono text-[9px] uppercase tracking-widest text-subtle">
                            Lots à distribuer
                        </label>
                        <div class="mb-4 flex items-center gap-1.5">
                            <button type="button" wire:click="decrementLotsCount" @disabled($drawing || $lotsCount <=1) class="h-8 w-8 shrink-0 rounded-lg border-2 border-ink bg-wash font-bold disabled:opacity-40">−</button>
                            <input id="lotsCount" type="number" wire:model.live="lotsCount" min="1" max="{{ $allowDuplicates ? 100 : count($participants) }}" @disabled($drawing) class="w-full flex-1 rounded-xl border-2 border-ink bg-wash py-1 text-center font-display text-base text-ink focus:outline-none">
                            <button type="button" wire:click="incrementLotsCount" @disabled($drawing) class="h-8 w-8 shrink-0 rounded-lg border-2 border-ink bg-wash font-bold disabled:opacity-40">+</button>
                        </div>
                    </div>

                    {{-- BOUTON DE LANCEMENT DE LA TOMBOLA --}}
                    @if(! $drawing && $winners === [])
                    <button type="button" wire:click="start" @disabled(! $this->canStart()) class="btn-press w-full rounded-xl border-2 border-ink bg-primary py-3 font-display text-xs text-white disabled:pointer-events-none disabled:opacity-50">
                        ▶ LANCER LA TOMBOLA
                    </button>
                    @endif
                </section>
            </div>

        </div>

    </div>

    {{-- MODAL DETAIL DES LOTS --}}
    <div x-show="modalOpen" x-cloak @keydown.escape.window="modalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink/50 backdrop-blur-sm">

        <div @click.outside="modalOpen = false" class="card-hard w-full max-w-sm rounded-2xl border-4 border-ink bg-panel p-5 shadow-2xl">
            <div class="flex items-center justify-between border-b-2 border-ink pb-2.5 mb-3">
                <h3 class="font-display text-lg text-ink" x-text="activeWinner?.name"></h3>
                <button type="button" @click="modalOpen = false" class="rounded-lg border-2 border-ink bg-wash px-2 py-0.5 font-mono text-xs font-bold hover:bg-secondary">✕</button>
            </div>

            <p class="font-mono text-[9px] uppercase tracking-widest text-subtle mb-2.5">Lots remportés :</p>

            <ul class="space-y-1.5 mb-5 max-h-52 overflow-y-auto pr-1">
                <template x-for="lotNum in activeWinner?.lots" :key="lotNum">
                    <li class="flex items-center justify-between rounded-xl border-2 border-ink bg-wash px-3 py-1.5 font-mono text-xs">
                        <span class="font-semibold text-ink">🏆 Lot d'attribution</span>
                        <span class="rounded bg-secondary border border-ink px-2 py-0.5 text-[11px] font-bold" x-text="'#' + lotNum"></span>
                    </li>
                </template>
            </ul>

            <button type="button" @click="modalOpen = false" class="w-full rounded-xl border-2 border-ink bg-ink py-2 font-display text-xs text-white">Fermer</button>
        </div>
    </div>

</div>
