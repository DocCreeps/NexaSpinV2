<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink" x-data="{
        flipping: false,
        finished: false
    }" x-on:coin-flip.window="flipping = true; finished = false" x-on:coin-flip-finished.window="flipping = false; finished = true; setTimeout(() => $wire.confirmFlip(), 500)" x-on:coin-flip-reset.window="finished = false">
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- HEADER --}}
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>

                <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">
                    ◆ Tirage rapide ◆
                </p>

                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">
                    Pile ou face
                </h1>

                <p class="mt-3 max-w-md text-sm text-muted">
                    Lancez la pièce (une fois ou en série), pariez sur le résultat, suivez l’historique.
                </p>
            </div>

            <div class="card-hard self-start rounded-xl border-2 border-ink bg-panel px-5 py-3 text-center min-w-[110px]">
                <p class="font-mono text-[9px] uppercase tracking-widest text-subtle">Tirages</p>
                <p class="mt-0.5 font-display text-2xl text-ink">
                    {{ $this->totalFlips() }}
                </p>
            </div>
        </header>

        {{-- ERREUR --}}
        @if($error)
        <div role="alert" class="rounded-xl border-2 border-ink bg-danger/10 px-4 py-3 text-sm font-semibold text-danger">
            ⚠ {{ $error }}
        </div>
        @endif

        {{-- GRILLE --}}
        <div class="grid items-start gap-6 lg:grid-cols-12 lg:gap-8">

            {{-- GAUCHE --}}
            <div class="lg:col-span-7">
                <section class="card-hard flex min-h-[420px] flex-col items-center rounded-2xl border-2 border-ink bg-panel p-6 sm:p-8">

                    {{-- Labels --}}
                    <div class="mb-8 w-full max-w-sm">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="font-mono text-[10px] uppercase tracking-widest text-faint">
                                Personnaliser les faces
                            </span>
                            <button type="button" wire:click="resetLabels" class="rounded text-[11px] font-semibold text-subtle transition hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                                Réinitialiser
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="mb-1 block font-mono text-[10px] uppercase tracking-widest text-faint">Pile</label>
                                <input type="text" wire:model.live.debounce.400ms="pileLabel" maxlength="16" placeholder="Pile" class="w-full rounded-xl border-2 border-ink bg-wash px-3 py-2 text-sm font-semibold text-ink placeholder:text-faint focus:outline-none focus:ring-2 focus:ring-info focus:ring-offset-2">
                            </div>
                            <div>
                                <label class="mb-1 block font-mono text-[10px] uppercase tracking-widest text-faint">Face</label>
                                <input type="text" wire:model.live.debounce.400ms="faceLabel" maxlength="16" placeholder="Face" class="w-full rounded-xl border-2 border-ink bg-wash px-3 py-2 text-sm font-semibold text-ink placeholder:text-faint focus:outline-none focus:ring-2 focus:ring-info focus:ring-offset-2">
                            </div>
                        </div>
                    </div>

                    {{-- Pièce --}}
                    <div class="relative" wire:key="coin-wrapper">
                        <x-coin-flip.coin :pile-label="$pileLabel" :face-label="$faceLabel" />
                    </div>

                    {{-- Lancer --}}
                    <div class="mt-8 flex w-full max-w-sm flex-col items-center gap-3">
                        <div class="flex w-full items-center gap-2">
                            <div class="w-24">
                                <input type="number" wire:model.live.debounce.200ms="autoFlipCount" min="1" max="1000" title="Nombre de tirages" class="w-full rounded-xl border-2 border-ink bg-wash px-2 py-3.5 text-center font-display text-sm text-ink focus:outline-none focus:ring-2 focus:ring-info focus:ring-offset-2">
                            </div>

                            <button type="button" wire:click="launch" wire:loading.attr="disabled" wire:target="launch" :disabled="flipping" class="btn-press flex-1 rounded-xl border-2 border-ink bg-primary py-3.5 text-center font-display text-xs text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 sm:text-sm">
                                <span x-show="!flipping">
                                    @if($autoFlipCount > 1)
                                    ▶ LANCER {{ $autoFlipCount }} TIRAGES
                                    @else
                                    ▶ LANCER LA PIÈCE
                                    @endif
                                </span>

                                <span x-show="flipping" x-cloak class="inline-flex items-center justify-center gap-2">
                                    <svg class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    TIRAGE...
                                </span>
                            </button>
                        </div>

                        <p class="text-center font-mono text-[11px] text-faint">
                            @if($autoFlipCount > 1)
                            Mode automatique — les paris sont désactivés
                            @else
                            Nombre > 1 = série automatique
                            @endif
                        </p>
                    </div>
                </section>
            </div>

            {{-- DROITE --}}
            <div class="space-y-6 lg:col-span-5">

                {{-- Paris --}}
                @if($autoFlipCount <= 1) <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
                    <h3 class="mb-1 font-display text-base text-ink">Parier </h3>
                    <p class="mb-4 text-xs text-muted">
                        Choisissez votre prédiction avant de lancer.
                    </p>

                    <div class="grid grid-cols-2 gap-2.5">
                        <button type="button" wire:click="selectBet('pile')" :disabled="flipping" @class([ 'rounded-xl border-2 border-ink py-3 font-display text-xs uppercase tracking-wider transition focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none' , 'bg-ink text-white shadow-hard'=> $bet === 'pile',
                            'bg-panel text-muted' => $bet !== 'pile',
                            ])
                            >
                            {{ $pileLabel }}
                        </button>

                        <button type="button" wire:click="selectBet('face')" :disabled="flipping" @class([ 'rounded-xl border-2 border-ink py-3 font-display text-xs uppercase tracking-wider transition focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none' , 'bg-secondary text-ink shadow-hard'=> $bet === 'face',
                            'bg-panel text-muted' => $bet !== 'face',
                            ])
                            >
                            {{ $faceLabel }}
                        </button>
                    </div>

                    @if(! is_null($lastBetWon))
                    <div x-show="finished" x-cloak @class([ 'mt-3 flex items-center justify-center gap-2 rounded-xl border-2 border-ink px-4 py-2.5 text-sm font-semibold' , 'bg-secondary text-ink'=> $lastBetWon,
                        'bg-danger/10 text-danger' => ! $lastBetWon,
                        ])
                        >
                        {{ $lastBetWon ? '✓ Pari gagné' : '✗ Pari perdu' }}
                    </div>
                    @endif

                    @if($this->betTotal())
                    <div class="mt-3 grid grid-cols-2 gap-3 border-t-2 border-dashed border-line pt-3">
                        <div class="rounded-xl border-2 border-ink bg-wash px-3 py-2.5 text-center">
                            <p class="font-mono text-[10px] uppercase tracking-widest text-subtle">Gagnés</p>
                            <p class="mt-0.5 font-display text-xl text-ink">{{ $this->betWinCount() }}</p>
                        </div>
                        <div class="rounded-xl border-2 border-ink bg-wash px-3 py-2.5 text-center">
                            <p class="font-mono text-[10px] uppercase tracking-widest text-subtle">Perdus</p>
                            <p class="mt-0.5 font-display text-xl text-ink">{{ $this->betLossCount() }}</p>
                        </div>
                    </div>
                    @endif
                    </section>
                    @endif

                    {{-- Stats --}}
                    <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
                        <h2 class="mb-4 font-display text-lg text-ink">Statistiques</h2>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border-2 border-ink bg-secondary/40 px-4 py-3 text-center">
                                <p class="truncate font-mono text-[10px] uppercase tracking-widest text-ink/70" title="{{ $faceLabel }}">
                                    {{ $faceLabel }}
                                </p>
                                <p class="mt-0.5 font-display text-2xl text-ink">{{ $this->faceCount() }}</p>
                            </div>

                            <div class="rounded-xl border-2 border-ink bg-wash px-4 py-3 text-center">
                                <p class="truncate font-mono text-[10px] uppercase tracking-widest text-subtle" title="{{ $pileLabel }}">
                                    {{ $pileLabel }}
                                </p>
                                <p class="mt-0.5 font-display text-2xl text-ink">{{ $this->pileCount() }}</p>
                            </div>
                        </div>
                    </section>

                    {{-- Historique --}}
                    <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-display text-base text-ink">Historique</h3>

                            <div class="flex items-center gap-3">
                                <a href="{{ route('history') }}?filter=coin_flip" class="font-mono text-[10px] uppercase tracking-widest text-info hover:underline">
                                    Historique complet →
                                </a>

                                @if(count($history))
                                <span class="rounded-md border-2 border-ink bg-wash px-2.5 py-0.5 font-mono text-[11px] text-subtle">
                                    {{ count($history) }} / {{ \App\Livewire\CoinFlip\CoinFlipPage::MAX_HISTORY ?? 1000 }}
                                </span>
                                @endif
                            </div>
                        </div>

                        @if(count($history))
                        <div class="custom-scrollbar max-h-[350px] space-y-2 overflow-y-auto pr-1">
                            @foreach(array_reverse($history, true) as $index => $entry)
                            @php $type = $entry['type'] ?? 'single'; @endphp

                            @if($type === 'single')
                            {{-- TIRAGE UNIQUE --}}
                            <div @class([ 'flex items-center justify-between rounded-xl border-2 border-ink px-4 py-2.5 text-sm font-semibold' , 'bg-secondary/30 text-ink'=> ($entry['side'] ?? '') === 'face',
                                'bg-wash text-ink' => ($entry['side'] ?? '') === 'pile',
                                ])>
                                <div class="flex items-center gap-2.5">
                                    <span @class([ 'h-2 w-2 rounded-full' , 'bg-secondary'=> ($entry['side'] ?? '') === 'face',
                                        'bg-ink/40' => ($entry['side'] ?? '') === 'pile',
                                        ])></span>

                                    <div class="flex flex-col">
                                        <span class="font-display tracking-wide">
                                            {{ $entry['side_label'] ?? $this->label($entry['side'] ?? 'pile') }}
                                        </span>

                                        @if(isset($entry['bet']) && $entry['bet'] !== null)
                                        <span class="font-mono text-[10px] font-normal text-muted">
                                            Pari: <span class="font-semibold">{{ $entry['bet_label'] ?? $this->label($entry['bet']) }}</span>
                                        </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if(isset($entry['bet_won']) && $entry['bet_won'] !== null)
                                    <span @class([ 'rounded-md border px-1.5 py-0.5 font-mono text-[10px] font-bold uppercase' , 'border-ink bg-secondary text-ink'=> $entry['bet_won'],
                                        'border-danger/30 bg-danger/10 text-danger' => ! $entry['bet_won'],
                                        ])>
                                        {{ $entry['bet_won'] ? 'Gagné' : 'Perdu' }}
                                    </span>
                                    @endif

                                    <span class="rounded-md border border-ink/20 bg-panel px-2 py-0.5 font-mono text-[11px] text-subtle">
                                        #{{ $index + 1 }}
                                    </span>
                                </div>
                            </div>

                            @else
                            {{-- TIRAGE MULTIPLE (AUTOMATIQUE) --}}
                            <div class="rounded-xl border-2 border-ink bg-panel p-3 text-sm font-semibold">
                                <div class="flex items-center justify-between border-b border-line pb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-md border border-ink bg-wash px-2 py-0.5 font-mono text-[10px] uppercase text-ink">
                                            Serie {{ $entry['count'] }}x
                                        </span>
                                        <span class="font-mono text-[11px] text-muted">
                                            Gagnant: <strong class="text-ink">{{ $entry['winner_label'] }}</strong>
                                        </span>
                                    </div>

                                    <span class="rounded-md border border-ink/20 bg-wash px-2 py-0.5 font-mono text-[11px] text-subtle">
                                        #{{ $index + 1 }}
                                    </span>
                                </div>

                                <div class="mt-2 grid grid-cols-2 gap-2 text-xs font-normal">
                                    <div class="flex items-center justify-between rounded-lg bg-wash px-2.5 py-1.5">
                                        <span class="text-subtle">{{ $entry['pile_label'] }}</span>
                                        <span class="font-mono font-bold text-ink">{{ $entry['pile_count'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-lg bg-secondary/20 px-2.5 py-1.5">
                                        <span class="text-subtle">{{ $entry['face_label'] }}</span>
                                        <span class="font-mono font-bold text-ink">{{ $entry['face_count'] }}</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        @else
                        <div class="rounded-xl border-2 border-dashed border-line py-8 text-center">
                            <p class="text-2xl mb-1">🪙</p>
                            <p class="text-sm font-medium text-muted">Aucun tirage pour l’instant</p>
                            <p class="mt-0.5 text-xs text-faint">Lancez la pièce pour démarrer</p>
                        </div>
                        @endif
                    </section>


                    @if(count($history))
                    <button type="button" wire:click="resetHistory" class="card-hard w-full rounded-xl border-2 border-ink bg-panel py-4 font-display text-sm text-ink transition focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                        RESET HISTORIQUE
                    </button>
                    @endif
            </div>
        </div>
    </div>
</div>
