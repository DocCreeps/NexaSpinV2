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
            <p class="mb-4 text-xs text-faint">
                La taille des poules est calculée automatiquement à partir du nombre de participants, pour rester équilibrée et éviter tout match vide.
            </p>
            @endif

            <div class="mb-6 flex items-center justify-between gap-3 rounded-xl border-2 border-ink/10 bg-wash px-4 py-3">
                <div>
                    <p class="text-sm font-bold text-ink">Tournoi sans score</p>
                    <p class="text-xs text-muted">Remplace la saisie des scores par un simple bouton Victoire / Nul sur chaque match.</p>
                </div>
                <button type="button" wire:click="toggleScoreMode" role="switch" aria-checked="{{ $withScores ? 'false' : 'true' }}" class="btn-press relative inline-flex h-7 w-12 shrink-0 items-center rounded-full border-2 border-ink transition-colors {{ ! $withScores ? 'bg-primary' : 'bg-wash' }}">
                    <span class="inline-block h-4.5 w-4.5 transform rounded-full border-2 border-ink bg-panel transition-transform {{ ! $withScores ? 'translate-x-5' : 'translate-x-0.5' }}"></span>
                </button>
            </div>

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
                                <th class="pb-1.5 text-center">N</th>
                                <th class="pb-1.5 text-center">D</th>
                                <th class="pb-1.5 text-center">Pts</th>
                                <th class="pb-1.5 text-center">J</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pool->standings() as $row)
                            <tr class="border-b border-ink/5 font-semibold text-ink">
                                <td class="py-1 truncate">{{ $row['participant']->name }}</td>
                                <td class="py-1 text-center font-mono">{{ $row['wins'] }}</td>
                                <td class="py-1 text-center font-mono text-faint">{{ $row['draws'] }}</td>
                                <td class="py-1 text-center font-mono text-danger">{{ $row['losses'] }}</td>
                                <td class="py-1 text-center font-mono font-bold">{{ $row['points'] }}</td>
                                <td class="py-1 text-center font-mono text-faint">{{ $row['played'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Matchs --}}
                    <div class="space-y-1.5">
                        @foreach($pool->matches() as $match)
                        @php
                            $isResolved = $match->isResolved();
                            $isDraw = $isResolved && $match->isDraw();
                            $winner = $match->winner();
                            $keyA = "{$pool->name}_{$match->index}_a";
                            $keyB = "{$pool->name}_{$match->index}_b";
                            $savedResult = collect($results)->firstWhere(fn($r) => $r['pool'] === $pool->name && $r['matchIndex'] === $match->index);
                        @endphp

                        @if($withScores)
                        {{-- Ligne compacte : Nom  score VS score  Nom, validation automatique --}}
                        <div class="flex items-center gap-2 rounded-lg border-2 border-ink/10 bg-wash px-2.5 py-2 text-xs" x-data="{ editing: false }">
                            <span class="min-w-0 flex-1 truncate font-bold {{ $isResolved && ! $isDraw && $winner->equals($match->participantA()) ? 'text-ink' : 'text-ink' }}">
                                {{ $isResolved && ! $isDraw && $winner->equals($match->participantA()) ? '🏆 ' : '' }}{{ $match->participantA()->name }}
                            </span>

                            <div class="flex shrink-0 items-center gap-1 font-mono text-[11px] font-bold">
                                @if(! $isResolved)
                                <input type="number" min="0" wire:model.defer="scores.{{ $keyA }}" wire:change="autoRecordIfReady('{{ $pool->name }}', {{ $match->index }})" class="h-7 w-9 rounded-md border border-ink bg-panel text-center focus:outline-none focus:ring-2 focus:ring-info" placeholder="–">
                                <span class="text-faint">:</span>
                                <input type="number" min="0" wire:model.defer="scores.{{ $keyB }}" wire:change="autoRecordIfReady('{{ $pool->name }}', {{ $match->index }})" class="h-7 w-9 rounded-md border border-ink bg-panel text-center focus:outline-none focus:ring-2 focus:ring-info" placeholder="–">
                                @else
                                {{-- Résolu : affichage statique + ✎, ou en édition + ✕. Ni l'un ni
                                     l'autre ne fait d'aller-retour serveur tant que le score n'a
                                     pas réellement changé — un clic accidentel sur ✎ se corrige en
                                     un clic sur ✕, sans rien avoir modifié. --}}
                                <template x-if="! editing">
                                    <div class="flex items-center gap-1">
                                        @if($isDraw)
                                        <span class="text-subtle">{{ $savedResult['score_a'] ?? '–' }}</span>
                                        <span class="text-faint">:</span>
                                        <span class="text-subtle">{{ $savedResult['score_b'] ?? '–' }}</span>
                                        @else
                                        <span class="{{ $winner->equals($match->participantA()) ? 'text-ink' : 'text-subtle' }}">{{ $savedResult['score_a'] ?? '–' }}</span>
                                        <span class="text-faint">:</span>
                                        <span class="{{ $winner->equals($match->participantB()) ? 'text-ink' : 'text-subtle' }}">{{ $savedResult['score_b'] ?? '–' }}</span>
                                        @endif
                                        <button type="button" x-on:click="editing = true" title="Modifier le résultat" class="ml-1 rounded p-1 text-faint transition hover:bg-wash hover:text-ink">
                                            ✎
                                        </button>
                                    </div>
                                </template>
                                <template x-if="editing">
                                    <div class="flex items-center gap-1">
                                        <input type="number" min="0" wire:model.defer="scores.{{ $keyA }}" wire:change="autoRecordIfReady('{{ $pool->name }}', {{ $match->index }})" class="h-7 w-9 rounded-md border border-ink bg-panel text-center focus:outline-none focus:ring-2 focus:ring-info">
                                        <span class="text-faint">:</span>
                                        <input type="number" min="0" wire:model.defer="scores.{{ $keyB }}" wire:change="autoRecordIfReady('{{ $pool->name }}', {{ $match->index }})" class="h-7 w-9 rounded-md border border-ink bg-panel text-center focus:outline-none focus:ring-2 focus:ring-info">
                                        <button type="button" x-on:click="editing = false" title="Annuler" class="ml-1 rounded p-1 text-faint transition hover:bg-wash hover:text-ink">
                                            ✕
                                        </button>
                                    </div>
                                </template>
                                @endif
                            </div>

                            <span class="min-w-0 flex-1 truncate text-right font-bold text-ink">
                                {{ $match->participantB()->name }}{{ $isResolved && ! $isDraw && $winner->equals($match->participantB()) ? ' 🏆' : '' }}
                            </span>
                        </div>
                        @else
                        {{-- Sans scores : boutons Victoire / Nul, toujours en une ligne --}}
                        <div class="flex items-center gap-2 rounded-lg border-2 border-ink/10 bg-wash px-2.5 py-2 text-xs" x-data="{ editing: false }">
                            <span class="min-w-0 flex-1 truncate font-bold text-ink">
                                {{ $isResolved && ! $isDraw && $winner->equals($match->participantA()) ? '🏆 ' : '' }}{{ $match->participantA()->name }}
                            </span>

                            <div class="flex shrink-0 items-center gap-1">
                                @if(! $isResolved)
                                <button type="button" wire:click="recordResult('{{ $pool->name }}', {{ $match->index }}, '{{ addslashes($match->participantA()->name) }}')" class="btn-press rounded border border-ink bg-panel px-1.5 py-0.5 font-mono text-[9px] uppercase text-subtle hover:bg-ink hover:text-white">
                                    Victoire
                                </button>
                                <button type="button" wire:click="recordResult('{{ $pool->name }}', {{ $match->index }}, null, true)" class="btn-press rounded border border-ink bg-panel px-1.5 py-0.5 font-mono text-[9px] uppercase text-subtle hover:bg-ink hover:text-white">
                                    Nul
                                </button>
                                <button type="button" wire:click="recordResult('{{ $pool->name }}', {{ $match->index }}, '{{ addslashes($match->participantB()->name) }}')" class="btn-press rounded border border-ink bg-panel px-1.5 py-0.5 font-mono text-[9px] uppercase text-subtle hover:bg-ink hover:text-white">
                                    Victoire
                                </button>
                                @else
                                <template x-if="! editing">
                                    <div class="flex items-center gap-1">
                                        @if($isDraw)
                                        <span class="rounded-md border border-ink/20 bg-panel px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-widest text-subtle">Nul</span>
                                        @endif
                                        <button type="button" x-on:click="editing = true" title="Modifier le résultat" class="rounded p-1 text-faint transition hover:bg-wash hover:text-ink">
                                            ✎
                                        </button>
                                    </div>
                                </template>
                                <template x-if="editing">
                                    <div class="flex items-center gap-1">
                                        <button type="button" wire:click="recordResult('{{ $pool->name }}', {{ $match->index }}, '{{ addslashes($match->participantA()->name) }}')" class="btn-press rounded border border-ink bg-panel px-1.5 py-0.5 font-mono text-[9px] uppercase text-subtle hover:bg-ink hover:text-white">
                                            Victoire
                                        </button>
                                        <button type="button" wire:click="recordResult('{{ $pool->name }}', {{ $match->index }}, null, true)" class="btn-press rounded border border-ink bg-panel px-1.5 py-0.5 font-mono text-[9px] uppercase text-subtle hover:bg-ink hover:text-white">
                                            Nul
                                        </button>
                                        <button type="button" wire:click="recordResult('{{ $pool->name }}', {{ $match->index }}, '{{ addslashes($match->participantB()->name) }}')" class="btn-press rounded border border-ink bg-panel px-1.5 py-0.5 font-mono text-[9px] uppercase text-subtle hover:bg-ink hover:text-white">
                                            Victoire
                                        </button>
                                        <button type="button" x-on:click="editing = false" title="Annuler" class="rounded p-1 text-faint transition hover:bg-wash hover:text-ink">
                                            ✕
                                        </button>
                                    </div>
                                </template>
                                @endif
                            </div>

                            <span class="min-w-0 flex-1 truncate text-right font-bold text-ink">
                                {{ $match->participantB()->name }}{{ $isResolved && ! $isDraw && $winner->equals($match->participantB()) ? ' 🏆' : '' }}
                            </span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </section>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
