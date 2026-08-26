<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink">
    <div class="mx-auto max-w-[1600px] space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- HEADER --}}
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>

                <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">
                    ◆ Upper / Lower ◆
                </p>

                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">
                    Double élimination
                </h1>

                <p class="mt-3 max-w-md text-sm text-muted">
                    Chaque joueur a deux chances : une défaite en tableau principal renvoie au tableau de repêchage plutôt que d’éliminer directement.
                </p>
            </div>

            <div class="card-hard min-w-[110px] self-start rounded-xl border-2 border-ink bg-panel px-5 py-3 text-center">
                <p class="font-mono text-[9px] uppercase tracking-widest text-subtle">Participants</p>
                <p class="mt-0.5 font-display text-2xl text-ink">
                    {{ count($participants) }}
                </p>
            </div>
        </header>

        {{-- ERREUR --}}
        @if($error)
        <div role="alert" class="rounded-xl border-2 border-ink bg-danger/10 px-4 py-3 text-sm font-semibold text-danger">
            ⚠ {{ $error }}
        </div>
        @endif

        {{-- CHAMPION --}}
        @if($champion)
        <div class="card-hard flex items-center justify-between rounded-2xl border-2 border-ink bg-secondary px-6 py-4 shadow-lg">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl border-2 border-ink bg-panel text-2xl">
                    🏆
                </div>
                <div>
                    <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-ink/70">Vainqueur Final</p>
                    <p class="font-display text-3xl font-black text-ink">{{ $champion }}</p>
                </div>
            </div>
            <button type="button" wire:click="restart" wire:confirm="Recommencer un nouveau tournoi ? Le tournoi en cours sera perdu." class="btn-press rounded-xl border-2 border-ink bg-panel px-4 py-2 font-display text-xs text-ink transition hover:bg-ink hover:text-white">
                NOUVEAU TOURNOI
            </button>
        </div>
        @endif

        @if(! $started)
        {{-- ÉTAPE 1 : SAISIE DES PARTICIPANTS --}}
        <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5 sm:p-6">
            <h2 class="mb-4 font-display text-lg text-ink">Participants (min. 4, nombre libre)</h2>

            <form wire:submit.prevent="addParticipant" class="mb-5 flex gap-2">
                <label for="de-bracket-participant-input" class="sr-only">Nom du participant</label>
                <input type="text" id="de-bracket-participant-input" wire:model="participant" placeholder="Nom du participant..." class="w-full rounded-xl border-2 border-ink bg-wash px-4 py-2.5 text-sm text-ink placeholder:text-faint focus:outline-none focus:ring-2 focus:ring-info focus:ring-offset-2">

                <button type="submit" class="btn-press shrink-0 rounded-xl border-2 border-ink bg-primary px-5 py-2.5 text-sm font-display text-white focus:outline-none focus:ring-2 focus:ring-info focus:ring-offset-2">
                    AJOUTER
                </button>
            </form>

            @if(count($participants))
            <ul class="mb-6 flex flex-wrap gap-2">
                @foreach($participants as $index => $name)
                <li class="inline-flex items-center gap-2 rounded-xl border-2 border-ink bg-wash px-3 py-1.5 text-sm font-semibold text-ink">
                    {{ $name }}
                    <button type="button" wire:click="removeParticipant({{ $index }})" class="text-subtle transition hover:text-danger" aria-label="Retirer {{ $name }}">
                        ✕
                    </button>
                </li>
                @endforeach
            </ul>
            @else
            <div class="mb-6 rounded-xl border-2 border-dashed border-line py-6 text-center">
                <p class="text-sm font-medium text-muted">Aucun participant pour l’instant</p>
            </div>
            @endif

            <button type="button" wire:click="start" @disabled(! $this->canStart()) class="btn-press w-full rounded-xl border-2 border-ink bg-primary py-3.5 font-display text-sm text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                @if(! $this->canStart())
                AJOUTER AU MOINS 4 PARTICIPANTS
                @else
                ▶ GÉNÉRER LE TOURNOI
                @endif
            </button>
        </section>
        @else
        {{-- ÉTAPE 2 : UPPER / LOWER / GRANDE FINALE --}}
        <div class="space-y-10">

            {{-- Barre d'outils --}}
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border-2 border-ink bg-wash px-4 py-3">
                <div class="flex items-center gap-3">
                    <span class="relative flex h-3 w-3">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-info opacity-75"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-info"></span>
                    </span>
                    <span class="font-mono text-xs font-bold uppercase tracking-wider text-ink">
                        Double élimination
                    </span>
                </div>
                <div class="flex items-center gap-4 text-xs font-semibold text-muted">
                    <span class="hidden sm:inline">↔ Saisissez les scores et validez</span>
                    <button type="button" wire:click="restart" wire:confirm="Recommencer un nouveau tournoi ?" class="font-mono text-[11px] uppercase underline transition hover:text-danger">
                        Réinitialiser
                    </button>
                </div>
            </div>

            {{-- UPPER BRACKET --}}
            <div>
                <h2 class="mb-3 font-display text-base uppercase tracking-wide text-ink">🥇 Tableau principal (Upper)</h2>
                <div class="overflow-x-auto pb-6 pt-2 scrollbar-thin scrollbar-thumb-ink">
                    <div class="inline-flex min-w-full gap-12 px-4 py-2">
                        @foreach($this->bracket->upperRounds() as $roundNumber => $matches)
                        <section class="flex min-w-[240px] flex-1 flex-col">
                            <div class="mb-4 flex items-center justify-between border-b-2 border-ink pb-2">
                                <h3 class="font-mono text-[10px] font-bold uppercase tracking-widest text-subtle">
                                    @if($roundNumber === $this->bracket->upperRoundCount())
                                    Finale UB
                                    @else
                                    UB Round {{ $roundNumber }}
                                    @endif
                                </h3>
                            </div>
                            <div class="flex flex-1 flex-col justify-around gap-6">
                                @foreach($matches as $match)
                                @include('livewire.tournament.bracket.partials.match-card', [
                                    'match' => $match,
                                    'section' => 'upper',
                                    'round' => $roundNumber,
                                    'position' => $match->position,
                                    'label' => 'UB R'.$roundNumber.' #'.($match->position + 1),
                                ])
                                @endforeach
                            </div>
                        </section>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- LOWER BRACKET --}}
            <div>
                <h2 class="mb-3 font-display text-base uppercase tracking-wide text-ink">🥈 Tableau de repêchage (Lower)</h2>
                <div class="overflow-x-auto pb-6 pt-2 scrollbar-thin scrollbar-thumb-ink">
                    <div class="inline-flex min-w-full gap-12 px-4 py-2">
                        @foreach($this->bracket->lowerRounds() as $roundNumber => $matches)
                        @php
                            $visibleMatches = collect($matches)->filter(
                                fn($m) => $this->bracket->lowerMatchSourceCount($roundNumber, $m->position) > 0
                            );
                        @endphp
                        @if($visibleMatches->isNotEmpty())
                        <section class="flex min-w-[240px] flex-1 flex-col">
                            <div class="mb-4 flex items-center justify-between border-b-2 border-ink pb-2">
                                <h3 class="font-mono text-[10px] font-bold uppercase tracking-widest text-subtle">
                                    @if($roundNumber === $this->bracket->lowerRoundCount())
                                    Finale LB
                                    @else
                                    LB Round {{ $roundNumber }}
                                    @endif
                                </h3>
                            </div>
                            <div class="flex flex-1 flex-col justify-around gap-6">
                                @foreach($visibleMatches as $match)
                                @include('livewire.tournament.bracket.partials.match-card', [
                                    'match' => $match,
                                    'section' => 'lower',
                                    'round' => $roundNumber,
                                    'position' => $match->position,
                                    'label' => 'LB R'.$roundNumber.' #'.($match->position + 1),
                                ])
                                @endforeach
                            </div>
                        </section>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- GRANDE FINALE --}}
            <div>
                <h2 class="mb-3 font-display text-base uppercase tracking-wide text-ink">🏆 Grande finale</h2>
                <div class="grid max-w-md grid-cols-1 gap-6 sm:grid-cols-2">
                    @if($this->bracket->grandFinal())
                    @include('livewire.tournament.bracket.partials.match-card', [
                        'match' => $this->bracket->grandFinal(),
                        'section' => 'grand_final',
                        'round' => null,
                        'position' => null,
                        'label' => 'Grande finale',
                    ])
                    @endif

                    @if($this->bracket->grandFinalReset() && ($this->bracket->grandFinalReset()->isPlayable() || $this->bracket->grandFinalReset()->isResolved()))
                    @include('livewire.tournament.bracket.partials.match-card', [
                        'match' => $this->bracket->grandFinalReset(),
                        'section' => 'grand_final_reset',
                        'round' => null,
                        'position' => null,
                        'label' => 'Match de reset',
                    ])
                    @endif
                </div>
                @if($this->bracket->grandFinal() && ! $this->bracket->grandFinal()->isPlayable() && ! $this->bracket->grandFinal()->isResolved())
                <p class="mt-3 text-xs font-medium text-muted">La grande finale s’activera dès que les deux tableaux auront désigné leur finaliste.</p>
                @endif
            </div>

        </div>
        @endif
    </div>
</div>
