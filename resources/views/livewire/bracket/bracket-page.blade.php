<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink">
    <div class="mx-auto max-w-[1600px] space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- HEADER --}}
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>

                <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">
                    ◆ Tournoi libre ◆
                </p>

                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">
                    Bracket
                </h1>

                <p class="mt-3 max-w-md text-sm text-muted">
                    Générez l’arbre du tournoi puis saisissez le score ou le résultat de chaque match au fil de la compétition.
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
            <button type="button" wire:click="restart" wire:confirm="Recommencer un nouveau bracket ? Le tournoi en cours sera perdu." class="btn-press rounded-xl border-2 border-ink bg-panel px-4 py-2 font-display text-xs text-ink transition hover:bg-ink hover:text-white">
                NOUVEAU TOURNOI
            </button>
        </div>
        @endif

        @if(! $started)
        {{-- ÉTAPE 1 : SAISIE DES PARTICIPANTS --}}
        <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5 sm:p-6">
            <h2 class="mb-4 font-display text-lg text-ink">Participants (min. 4, nombre libre)</h2>

            <form wire:submit.prevent="addParticipant" class="mb-5 flex gap-2">
                <label for="bracket-participant-input" class="sr-only">Nom du participant</label>
                <input type="text" id="bracket-participant-input" wire:model="participant" placeholder="Nom du participant..." class="w-full rounded-xl border-2 border-ink bg-wash px-4 py-2.5 text-sm text-ink placeholder:text-faint focus:outline-none focus:ring-2 focus:ring-info focus:ring-offset-2">

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
                ▶ GÉNÉRER LE BRACKET
                @endif
            </button>
        </section>
        @else
        {{-- ÉTAPE 2 : ARBRE DU TOURNOI AVEC SCORES --}}
        <div class="space-y-6">

            {{-- Barre d'outils --}}
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border-2 border-ink bg-wash px-4 py-3">
                <div class="flex items-center gap-3">
                    <span class="relative flex h-3 w-3">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-info opacity-75"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-info"></span>
                    </span>
                    <span class="font-mono text-xs font-bold uppercase tracking-wider text-ink">
                        Tableau d'élimination directe
                    </span>
                </div>
                <div class="flex items-center gap-4 text-xs font-semibold text-muted">
                    <span class="hidden sm:inline">↔ Saisissez les scores et validez</span>
                    <button type="button" wire:click="restart" wire:confirm="Recommencer un nouveau bracket ?" class="font-mono text-[11px] uppercase underline transition hover:text-danger">
                        Réinitialiser
                    </button>
                </div>
            </div>

            {{-- Zone Scrollable --}}
            <div class="overflow-x-auto pb-8 pt-2 scrollbar-thin scrollbar-thumb-ink">
                <div class="inline-flex min-w-full snap-x gap-12 px-4 py-4">

                    @foreach($this->bracket->rounds() as $roundNumber => $matches)
                    <section class="relative flex w-[300px] shrink-0 snap-start flex-col">

                        {{-- En-tête du Round --}}
                        <div class="mb-6 flex items-center justify-between rounded-lg border-2 border-ink bg-panel px-3 py-2 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                            <h2 class="font-display text-xs font-bold uppercase tracking-wider text-ink">
                                @if($loop->last)
                                🏆 Finale
                                @elseif($loop->remaining === 1)
                                ½ Finales
                                @elseif($loop->remaining === 2)
                                ¼ Finales
                                @else
                                Round {{ $roundNumber }}
                                @endif
                            </h2>
                            <span class="rounded border border-ink/20 bg-wash px-2 py-0.5 font-mono text-[9px] font-bold text-subtle">
                                {{ count($matches) }} {{ Str::plural('match', count($matches)) }}
                            </span>
                        </div>

                        {{-- Colonne des Matchs --}}
                        <div class="flex flex-1 flex-col justify-around gap-10">
                            @foreach($matches as $match)
                            @php
                            $a = $match->participantA();
                            $b = $match->participantB();
                            $winner = $match->winner();
                            $isResolved = $match->isResolved();
                            $isPlayable = $match->isPlayable();
                            $isEven = $loop->index % 2 === 0;

                            // Recherche du score enregistré dans les résultats
                            $savedResult = collect($results)->firstWhere(fn($r) => $r['round'] === $match->round && $r['position'] === $match->position);
                            @endphp

                            <div class="group relative flex flex-col justify-center">

                                {{-- Connecteurs d'arbre CSS --}}
                                @if(! $loop->parent->last)
                                <div class="pointer-events-none absolute -right-6 top-1/2 h-0.5 w-6 bg-ink transition-colors group-hover:bg-info"></div>
                                <div class="pointer-events-none absolute -right-6 h-[calc(50%+1.25rem)] w-0.5 bg-ink transition-colors group-hover:bg-info {{ $isEven ? 'top-1/2' : 'bottom-1/2' }}"></div>

                                @if($isEven)
                                <div class="pointer-events-none absolute -right-12 top-[calc(100%+1.25rem)] h-0.5 w-6 bg-ink"></div>
                                @endif
                                @endif

                                {{-- Carte Match --}}
                                <div class="card-hard relative overflow-hidden rounded-xl border-2 border-ink bg-panel transition-all hover:scale-[1.02] hover:shadow-md">

                                    {{-- En-tête carte --}}
                                    <div class="flex items-center justify-between border-b border-ink/10 bg-wash px-3 py-1 font-mono text-[9px]">
                                        <span class="font-bold text-subtle">MATCH #{{ $match->position + 1 }}</span>
                                        @if($match->isBye())
                                        <span class="rounded bg-faint/10 px-1.5 font-semibold text-faint uppercase">BYE</span>
                                        @elseif($isResolved)
                                        <span class="font-bold text-success uppercase">✓ Terminé</span>
                                        @elseif($isPlayable)
                                        <span class="animate-pulse font-bold text-info uppercase">● En cours</span>
                                        @else
                                        <span class="text-faint uppercase">En attente</span>
                                        @endif
                                    </div>

                                    {{-- Liste des Joueurs + Scores --}}
                                    <div class="space-y-2 p-2.5">

                                        {{-- Participant A --}}
                                        <div class="flex items-center justify-between gap-2 rounded-lg border-2 px-2.5 py-1.5 text-xs font-bold transition-all {{ $winner && $a && $winner->equals($a) ? 'border-ink bg-secondary text-ink shadow-sm' : ($a ? 'border-line bg-wash text-ink' : 'border-dashed border-line text-faint') }}">
                                            <span class="truncate">{{ $a?->name ?? 'À déterminer' }}</span>

                                            <div class="flex shrink-0 items-center gap-1.5">
                                                @if($isPlayable && ! $isResolved)
                                                <input type="number" min="0" wire:model.defer="scores.{{ $match->round }}_{{ $match->position }}_a" class="h-7 w-10 rounded-md border border-ink bg-panel text-center font-mono text-xs font-bold text-ink focus:outline-none focus:ring-2 focus:ring-info" placeholder="0">

                                                <button type="button" wire:click="recordResult({{ $match->round }}, {{ $match->position }}, '{{ addslashes($a?->name ?? '') }}')" title="Déclarer gagnant directement" class="btn-press rounded border border-ink bg-panel px-1.5 py-0.5 font-mono text-[9px] uppercase text-subtle hover:bg-ink hover:text-white">
                                                    Gagne
                                                </button>
                                                @elseif($isResolved && isset($savedResult['score_a']))
                                                <span class="rounded border border-ink/20 bg-panel px-2 py-0.5 font-mono text-xs font-black">{{ $savedResult['score_a'] }}</span>
                                                @elseif($winner && $a && $winner->equals($a))
                                                <span class="text-sm">🏆</span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Participant B --}}
                                        <div class="flex items-center justify-between gap-2 rounded-lg border-2 px-2.5 py-1.5 text-xs font-bold transition-all {{ $winner && $b && $winner->equals($b) ? 'border-ink bg-secondary text-ink shadow-sm' : ($b ? 'border-line bg-wash text-ink' : 'border-dashed border-line text-faint') }}">
                                            <span class="truncate">{{ $b?->name ?? 'À déterminer' }}</span>

                                            <div class="flex shrink-0 items-center gap-1.5">
                                                @if($isPlayable && ! $isResolved)
                                                <input type="number" min="0" wire:model.defer="scores.{{ $match->round }}_{{ $match->position }}_b" class="h-7 w-10 rounded-md border border-ink bg-panel text-center font-mono text-xs font-bold text-ink focus:outline-none focus:ring-2 focus:ring-info" placeholder="0">

                                                <button type="button" wire:click="recordResult({{ $match->round }}, {{ $match->position }}, '{{ addslashes($b?->name ?? '') }}')" title="Déclarer gagnant directement" class="btn-press rounded border border-ink bg-panel px-1.5 py-0.5 font-mono text-[9px] uppercase text-subtle hover:bg-ink hover:text-white">
                                                    Gagne
                                                </button>
                                                @elseif($isResolved && isset($savedResult['score_b']))
                                                <span class="rounded border border-ink/20 bg-panel px-2 py-0.5 font-mono text-xs font-black">{{ $savedResult['score_b'] }}</span>
                                                @elseif($winner && $b && $winner->equals($b))
                                                <span class="text-sm">🏆</span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Bouton de validation par score --}}
                                        @if($isPlayable && ! $isResolved)
                                        <div class="pt-1">
                                            <button type="button" wire:click="recordResult({{ $match->round }}, {{ $match->position }})" class="btn-press w-full rounded-lg border-2 border-ink bg-primary py-1.5 font-mono text-[10px] font-bold uppercase tracking-wider text-white hover:bg-info">
                                                ✓ Valider le score
                                            </button>
                                        </div>
                                        @endif

                                    </div>

                                </div>
                            </div>
                            @endforeach
                        </div>
                    </section>
                    @endforeach

                </div>
            </div>
        </div>
        @endif
    </div>
</div>
