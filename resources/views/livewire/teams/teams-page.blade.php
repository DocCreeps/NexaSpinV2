<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink" x-data="{ isPaused: false }" @if($drawing && $slowMode) x-init="let timer = setInterval(() => { if (!isPaused && $wire.autoAdvance) $wire.drawNextStep() }, 1200)" @endif>

    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- HEADER --}}
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>

                <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">
                    ◆ Répartition équitable & remplaçants ◆
                </p>

                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">
                    Tirage par équipes
                </h1>

                <p class="mt-3 max-w-md text-sm text-muted">
                    Répartit les participants en équipes. Les remplaçants sont automatiquement assignés à une équipe spécifique pour être prêts à remplacer.
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

        {{-- GRILLE PRINCIPALE --}}
        <div class="grid items-start gap-6 lg:grid-cols-12 lg:gap-8">

            {{-- COLONNE GAUCHE : RÉSULTAT ÉQUIPES (7 COLS) --}}
            <div class="lg:col-span-7">
                <section class="card-hard flex min-h-[520px] flex-col rounded-2xl border-2 border-ink bg-panel p-5 sm:p-6">
                    <div class="mb-4 flex items-center justify-between border-b-2 border-ink/10 pb-3">
                        <h2 class="font-display text-lg text-ink">Équipes constituées</h2>
                        @if($drawing)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-ink bg-amber-200 px-3 py-1 font-mono text-[10px] font-bold uppercase text-ink animate-pulse">
                            ⏳ Tirage {{ $currentStepIndex }}/{{ count($stepsSequence) }}
                        </span>
                        @endif
                    </div>

                    @if($hasResult)
                    <div class="flex-1 space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach($this->formattedTeams as $team)
                            <div class="flex flex-col justify-between rounded-xl border-2 border-ink bg-wash p-4">
                                <div>
                                    <p class="mb-2 font-display text-base text-ink flex items-center justify-between">
                                        <span>🛡️ Équipe {{ $team['index'] }}</span>
                                        <span class="font-mono text-xs font-semibold rounded-md border border-ink/20 bg-panel px-2 py-0.5 text-subtle">
                                            {{ count($team['members']) }} joueur{{ count($team['members']) > 1 ? 's' : '' }}
                                        </span>
                                    </p>

                                    {{-- Titulaires --}}
                                    <ul class="space-y-1.5 min-h-[40px]">
                                        @forelse($team['members'] as $member)
                                        <li class="rounded-lg border border-ink/15 bg-panel px-3 py-1.5 text-sm font-semibold text-ink shadow-sm transition hover:scale-[1.01]">
                                            👤 {{ $member }}
                                        </li>
                                        @empty
                                        <li class="text-xs text-subtle italic py-2 text-center">En attente...</li>
                                        @endforelse
                                    </ul>
                                </div>

                                {{-- Remplaçants rattachés à cette équipe --}}
                                @if(count($team['substitutes']) > 0 || $drawing)
                                <div class="mt-3 pt-2 border-t border-ink/10">
                                    <p class="mb-1 font-mono text-[9px] uppercase tracking-wider font-bold text-subtle">Remplaçant(s) dédié(s) :</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($team['substitutes'] as $sub)
                                        <span class="rounded-md border border-amber-400 bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-900 shadow-sm">
                                            🔁 {{ $sub }}
                                        </span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        {{-- COMMANDES DE TIRAGE (SUSPENSE) --}}
                        @if($drawing)
                        <div class="mt-4 flex flex-wrap gap-2 pt-3 border-t-2 border-ink/10">
                            @if($autoAdvance)
                            {{-- MODE AUTOMATIQUE --}}
                            <button type="button" @click="isPaused = !isPaused" class="btn-press flex-1 rounded-xl border-2 border-ink bg-amber-300 py-3 font-display text-xs text-ink">
                                <span x-text="isPaused ? '▶ Reprendre' : '⏸ Pause'"></span>
                            </button>

                            <button x-show="isPaused" type="button" wire:click="drawNextStep" class="btn-press rounded-xl border-2 border-ink bg-primary px-4 py-3 font-display text-xs text-white">
                                ⏭ Suivant
                            </button>
                            @else
                            {{-- MODE MANUEL --}}
                            <button type="button" wire:click="drawNextStep" class="btn-press flex-1 rounded-xl border-2 border-ink bg-primary py-3 font-display text-xs text-white">
                                🎲 Placer le joueur suivant
                            </button>
                            @endif

                            <button type="button" wire:click="stop" class="rounded-xl border-2 border-ink bg-wash px-4 py-3 font-display text-xs text-ink">
                                Annuler
                            </button>
                        </div>
                        @elseif(! $drawing && $hasResult)
                        <div class="mt-4 flex gap-2 pt-3 border-t-2 border-ink/10">
                            <button type="button" wire:click="stop" class="w-full rounded-xl border-2 border-ink bg-wash py-2.5 font-display text-xs text-ink transition hover:bg-panel">
                                ↻ Recommencer / Effacer
                            </button>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="m-auto text-center">
                        <p class="mb-2 text-4xl">👥</p>
                        <p class="text-sm font-medium text-muted">Ajoutez vos participants et lancez la répartition</p>
                    </div>
                    @endif
                </section>
            </div>

            {{-- COLONNE DROITE : CONTRÔLES (5 COLS) --}}
            <div class="space-y-6 lg:col-span-5">
                <section class="card-hard overflow-hidden rounded-2xl border-2 border-ink bg-panel p-5">
                    <h2 class="mb-4 font-display text-lg text-ink">Configuration</h2>

                    {{-- SWITCH MODE SUSPENSE --}}
                    <div class="mb-3 flex items-start gap-2 rounded-xl border-2 border-ink bg-wash p-2.5">
                        <button type="button" wire:click="toggleSlowMode" @disabled($drawing) @class([ 'relative mt-0.5 inline-flex h-4 w-7 shrink-0 cursor-pointer rounded-full border border-ink transition-colors disabled:opacity-50' , 'bg-primary'=> $slowMode,
                            'bg-line' => ! $slowMode,
                            ])>
                            <span @class([ 'inline-block h-2.5 w-2.5 rounded-full bg-white transition mt-0.5' , 'translate-x-3'=> $slowMode,
                                'translate-x-0.5' => ! $slowMode,
                                ])></span>
                        </button>
                        <div>
                            <span class="block text-xs font-semibold text-ink">Mode suspense ⏳</span>
                            <span class="block text-[9px] text-muted">Révéler les joueurs un par un.</span>
                        </div>
                    </div>

                    {{-- AVANCEMENT DU SUSPENSE (AUTO OU MANUEL) --}}
                    @if($slowMode)
                    <div class="mb-4 rounded-xl border-2 border-ink bg-wash p-2">
                        <span class="mb-1.5 block font-mono text-[9px] uppercase tracking-widest text-subtle">Déroulement</span>
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

                    {{-- NOMBRE D'ÉQUIPES --}}
                    <label class="mb-1.5 block font-mono text-[10px] uppercase tracking-widest text-subtle">
                        Nombre d’équipes
                    </label>
                    <div class="mb-5 flex items-center gap-2">
                        <button type="button" wire:click="decrementTeamsCount" @disabled($drawing) class="h-10 w-10 shrink-0 rounded-lg border-2 border-ink bg-wash font-bold text-ink transition hover:bg-panel disabled:opacity-40">−</button>
                        <div class="flex-1 rounded-xl border-2 border-ink bg-wash py-2 text-center font-display text-lg text-ink">
                            {{ $teamsCount }}
                        </div>
                        <button type="button" wire:click="incrementTeamsCount" @disabled($drawing) class="h-10 w-10 shrink-0 rounded-lg border-2 border-ink bg-wash font-bold text-ink transition hover:bg-panel disabled:opacity-40">+</button>
                    </div>

                    {{-- LISTE DES PARTICIPANTS --}}
                    <x-draw.participant-form :participants="$participants" :error="$error" />
                </section>

                @if(! $drawing)
                <button type="button" wire:click="start" @disabled(! $this->canDraw())
                    class="btn-press w-full rounded-xl border-2 border-ink bg-primary py-4 font-display text-sm text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                    @if(! $this->canDraw())
                    AJOUTER AU MOINS 4 PARTICIPANTS
                    @else
                    ▶ FORMER LES ÉQUIPES
                    @endif
                </button>
                @endif

                {{-- HISTORIQUE --}}
                <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h3 class="font-display text-base text-ink">Derniers tirages</h3>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('history') }}?filter=teams" class="font-mono text-[10px] uppercase tracking-widest text-info hover:underline">
                                Historique →
                            </a>
                            @if(count($history))
                            <button type="button" wire:click="clearHistory" wire:confirm="Vider l'historique ?" class="font-mono text-[10px] uppercase tracking-widest text-subtle transition hover:text-danger">
                                Vider
                            </button>
                            @endif
                        </div>
                    </div>

                    @if(count($history))
                    <div class="space-y-2">
                        @foreach(array_slice(array_reverse($history), 0, 5) as $entry)
                        <div class="rounded-xl border-2 border-ink bg-wash px-4 py-2.5 text-sm">
                            <span class="font-semibold text-ink">{{ $entry['teams_count'] }} équipes</span>
                            <span class="font-mono text-[11px] text-subtle">
                                · {{ count($entry['participants']) }} participants
                                @if(count($entry['substitutes']))
                                · {{ count($entry['substitutes']) }} remplaçant(s)
                                @endif
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="rounded-xl border-2 border-dashed border-line py-6 text-center">
                        <p class="text-sm font-medium text-muted">Aucun tirage pour l’instant</p>
                    </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</div>
