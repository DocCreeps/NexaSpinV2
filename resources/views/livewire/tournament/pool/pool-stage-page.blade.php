<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink">
    <div class="mx-auto max-w-[1600px] space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- HEADER --}}
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>

                <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">
                    ◆ Round-robin ◆
                </p>

                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">
                    Phase de poules
                </h1>

                <p class="mt-3 max-w-md text-sm text-muted">
                    Répartition équilibrée en poules : tous les participants d’une poule s’affrontent une fois chacun, sans aucun match vide.
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

        {{-- TERMINÉ --}}
        @if($isComplete)
        <div class="card-hard flex items-center justify-between rounded-2xl border-2 border-ink bg-secondary px-6 py-4 shadow-lg">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl border-2 border-ink bg-panel text-2xl">
                    🏁
                </div>
                <div>
                    <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-ink/70">Phase de poules terminée</p>
                    <p class="font-display text-xl font-black text-ink">Tous les matchs ont été joués</p>
                </div>
            </div>
            <button type="button" wire:click="restart" wire:confirm="Recommencer une nouvelle phase de poules ?" class="btn-press rounded-xl border-2 border-ink bg-panel px-4 py-2 font-display text-xs text-ink transition hover:bg-ink hover:text-white">
                NOUVELLE PHASE
            </button>
        </div>
        @endif

        @if(! $started)
        {{-- ÉTAPE 1 : SAISIE DES PARTICIPANTS --}}
        <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5 sm:p-6">
            <h2 class="mb-4 font-display text-lg text-ink">Participants (min. 4, nombre libre)</h2>

            <form wire:submit.prevent="addParticipant" class="mb-5 flex gap-2">
                <label for="pool-participant-input" class="sr-only">Nom du participant</label>
                <input type="text" id="pool-participant-input" wire:model="participant" placeholder="Nom du participant..." class="w-full rounded-xl border-2 border-ink bg-wash px-4 py-2.5 text-sm text-ink placeholder:text-faint focus:outline-none focus:ring-2 focus:ring-info focus:ring-offset-2">

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

            @if(count($participants) >= 4)
            <p class="mb-6 text-xs text-faint">
                La taille des poules est calculée automatiquement à partir du nombre de participants, pour rester équilibrée et éviter tout match vide.
            </p>
            @endif

            <button type="button" wire:click="start" @disabled(! $this->canStart()) class="btn-press w-full rounded-xl border-2 border-ink bg-primary py-3.5 font-display text-sm text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                @if(! $this->canStart())
                AJOUTER AU MOINS 4 PARTICIPANTS
                @else
                ▶ GÉNÉRER LES POULES
                @endif
            </button>
        </section>
        @else
        {{-- ÉTAPE 2 : POULES --}}
        <div class="space-y-6">

            {{-- Barre d'outils --}}
            <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border-2 border-ink bg-wash px-4 py-3">
                <div class="flex items-center gap-3">
                    <span class="relative flex h-3 w-3">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-info opacity-75"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-info"></span>
                    </span>
                    <span class="font-mono text-xs font-bold uppercase tracking-wider text-ink">
                        {{ count($this->stage->pools()) }} {{ Str::plural('poule', count($this->stage->pools())) }} · {{ $this->stage->totalMatches() }} matchs
                    </span>
                </div>
                <div class="flex items-center gap-4 text-xs font-semibold text-muted">
                    <span class="hidden sm:inline">↔ Saisissez les scores et validez</span>
                    <button type="button" wire:click="restart" wire:confirm="Recommencer une nouvelle phase de poules ?" class="font-mono text-[11px] uppercase underline transition hover:text-danger">
                        Réinitialiser
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 xl:grid-cols-3">
                @foreach($this->stage->pools() as $pool)
                <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-4">
                    <div class="mb-4 flex items-center justify-between border-b-2 border-ink pb-2">
                        <h2 class="font-display text-lg text-ink">{{ $pool->name }}</h2>
                        <span class="rounded border border-ink/20 bg-wash px-2 py-0.5 font-mono text-[9px] font-bold text-subtle">
                            {{ $pool->size() }} joueurs · {{ count($pool->matches()) }} matchs
                        </span>
                    </div>

                    {{-- Classement --}}
                    <table class="mb-4 w-full text-xs">
                        <thead>
                            <tr class="border-b border-ink/10 text-left font-mono text-[9px] uppercase tracking-widest text-subtle">
                                <th class="pb-1.5">Joueur</th>
                                <th class="pb-1.5 text-center">V</th>
                                <th class="pb-1.5 text-center">J</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pool->standings() as $row)
                            <tr class="border-b border-ink/5 font-semibold text-ink">
                                <td class="py-1 truncate">{{ $row['participant']->name }}</td>
                                <td class="py-1 text-center font-mono">{{ $row['wins'] }}</td>
                                <td class="py-1 text-center font-mono text-faint">{{ $row['played'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Matchs --}}
                    <div class="space-y-2">
                        @foreach($pool->matches() as $match)
                        @php
                            $isResolved = $match->isResolved();
                            $winner = $match->winner();
                            $keyA = "{$pool->name}_{$match->index}_a";
                            $keyB = "{$pool->name}_{$match->index}_b";
                            $savedResult = collect($results)->firstWhere(fn($r) => $r['pool'] === $pool->name && $r['matchIndex'] === $match->index);
                        @endphp
                        <div class="rounded-lg border-2 border-ink/10 bg-wash p-2">
                            <div class="flex items-center justify-between gap-2 text-xs font-bold">
                                <span class="{{ $isResolved && $winner->equals($match->participantA()) ? 'text-success' : 'text-ink' }} truncate">
                                    {{ $match->participantA()->name }}
                                </span>
                                @if($isResolved && isset($savedResult['score_a']))
                                <span class="font-mono">{{ $savedResult['score_a'] }} – {{ $savedResult['score_b'] }}</span>
                                @else
                                <span class="text-faint">vs</span>
                                @endif
                                <span class="{{ $isResolved && $winner->equals($match->participantB()) ? 'text-success' : 'text-ink' }} truncate text-right">
                                    {{ $match->participantB()->name }}
                                </span>
                            </div>

                            @if(! $isResolved)
                            <div class="mt-2 flex items-center gap-1.5">
                                <input type="number" min="0" wire:model.defer="scores.{{ $keyA }}" class="h-7 w-10 rounded-md border border-ink bg-panel text-center font-mono text-xs font-bold text-ink focus:outline-none focus:ring-2 focus:ring-info" placeholder="0">
                                <input type="number" min="0" wire:model.defer="scores.{{ $keyB }}" class="h-7 w-10 rounded-md border border-ink bg-panel text-center font-mono text-xs font-bold text-ink focus:outline-none focus:ring-2 focus:ring-info" placeholder="0">
                                <button type="button" wire:click="recordResult('{{ $pool->name }}', {{ $match->index }})" class="btn-press flex-1 rounded-lg border-2 border-ink bg-primary py-1.5 font-mono text-[10px] font-bold uppercase tracking-wider text-white hover:bg-info">
                                    ✓ Valider
                                </button>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </section>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
