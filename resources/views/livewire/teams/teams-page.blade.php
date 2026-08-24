<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink">
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- HEADER --}}
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>

                <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">
                    ◆ Répartition instantanée ◆
                </p>

                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">
                    Tirage par équipes
                </h1>

                <p class="mt-3 max-w-md text-sm text-muted">
                    Choisissez le nombre d’équipes : la répartition se fait au hasard, en tailles égales. Les participants en trop deviennent remplaçants.
                </p>
            </div>

            <div class="card-hard self-start rounded-xl border-2 border-ink bg-panel px-5 py-3 text-center min-w-[110px]">
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

        {{-- GRILLE --}}
        <div class="grid items-start gap-6 lg:grid-cols-12 lg:gap-8">

            {{-- RÉSULTAT --}}
            <div class="lg:col-span-7">
                <section class="card-hard flex min-h-[520px] flex-col rounded-2xl border-2 border-ink bg-panel p-6 sm:p-8">
                    @if($hasResult)
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($teams as $index => $members)
                        <div class="rounded-xl border-2 border-ink bg-wash p-4">
                            <p class="mb-2 font-display text-base text-ink">
                                🛡️ Équipe {{ $index + 1 }}
                                <span class="font-mono text-[11px] font-normal text-subtle">({{ count($members) }})</span>
                            </p>
                            <ul class="space-y-1">
                                @foreach($members as $member)
                                <li class="rounded-lg border border-ink/15 bg-panel px-3 py-1.5 text-sm font-semibold text-ink">
                                    {{ $member }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endforeach
                    </div>

                    @if(count($substitutes))
                    <div class="mt-4 rounded-xl border-2 border-dashed border-line bg-wash p-4">
                        <p class="mb-2 font-display text-sm text-ink">
                            🔁 Remplaçants
                            <span class="font-mono text-[11px] font-normal text-subtle">({{ count($substitutes) }})</span>
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($substitutes as $substitute)
                            <span class="rounded-md border border-ink/20 bg-panel px-2.5 py-1 text-xs font-semibold text-ink">
                                {{ $substitute }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @else
                    <div class="m-auto text-center">
                        <p class="mb-2 text-4xl">👥</p>
                        <p class="text-sm font-medium text-muted">Ajoutez vos participants et lancez le tirage</p>
                    </div>
                    @endif
                </section>
            </div>

            {{-- CONTRÔLES --}}
            <div class="space-y-6 lg:col-span-5">
                <section class="card-hard overflow-hidden rounded-2xl border-2 border-ink bg-panel p-5">
                    <h2 class="mb-4 font-display text-lg text-ink">Configuration</h2>

                    <label class="mb-1.5 block font-mono text-[10px] uppercase tracking-widest text-subtle">
                        Nombre d’équipes
                    </label>
                    <div class="mb-5 flex items-center gap-2">
                        <button type="button" wire:click="decrementTeamsCount" class="h-10 w-10 shrink-0 rounded-lg border-2 border-ink bg-wash font-bold text-ink transition hover:bg-panel">−</button>
                        <div class="flex-1 rounded-xl border-2 border-ink bg-wash py-2 text-center font-display text-lg text-ink">
                            {{ $teamsCount }}
                        </div>
                        <button type="button" wire:click="incrementTeamsCount" class="h-10 w-10 shrink-0 rounded-lg border-2 border-ink bg-wash font-bold text-ink transition hover:bg-panel">+</button>
                    </div>

                    <x-draw.participant-form :participants="$participants" :error="$error" />
                </section>

                <button type="button" wire:click="draw" @disabled(! $this->canDraw())
                    class="btn-press w-full rounded-xl border-2 border-ink bg-primary py-4 font-display text-sm text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
                    >
                    @if(! $this->canDraw())
                    AJOUTER AU MOINS 4 PARTICIPANTS
                    @else
                    ▶ FORMER LES ÉQUIPES
                    @endif
                </button>

                {{-- Résumé rapide de l'historique --}}
                <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h3 class="font-display text-base text-ink">Derniers tirages</h3>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('history') }}?filter=teams" class="font-mono text-[10px] uppercase tracking-widest text-info hover:underline">
                                Historique complet →
                            </a>
                            @if(count($history))
                            <button type="button" wire:click="clearHistory" wire:confirm="Vider l'historique de ce mode ?" class="font-mono text-[10px] uppercase tracking-widest text-subtle transition hover:text-danger">
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
                                · {{ count($entry['substitutes']) }} remplaçant{{ count($entry['substitutes']) > 1 ? 's' : '' }}
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
