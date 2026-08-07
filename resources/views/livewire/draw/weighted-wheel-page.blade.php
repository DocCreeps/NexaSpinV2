<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink" x-data="{
        spinning: false,
        finished: false
    }" x-on:wheel-spin.window="spinning = true; finished = false" x-on:wheel-spin-finished.window="spinning = false; finished = true; setTimeout(() => $wire.confirmDraw(), 500)">
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- HEADER --}}
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>

                <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">
                    ◆ Chances modulables ◆
                </p>

                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">
                    Tirage pondéré
                </h1>

                <p class="mt-3 max-w-md text-sm text-muted">
                    Ajustez le poids de chaque participant pour influencer ses chances de gagner.
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

        {{-- GRILLE --}}
        <div class="grid items-start gap-6 lg:grid-cols-12 lg:gap-8">

            {{-- ROUE --}}
            <div class="lg:col-span-7">
                <section class="card-hard flex min-h-[520px] flex-col items-center justify-center rounded-2xl border-2 border-ink bg-panel p-6 sm:p-8">
                    <div class="relative" wire:key="wheel-wrapper">
                        <x-draw.wheel :segments="$this->segments" :show-labels="$this->showLabelsOnWheel()" />
                    </div>

                    @if($result)
                    <div x-show="finished" x-cloak class="mt-8 flex items-center gap-2 rounded-xl border-2 border-ink bg-secondary px-6 py-3 font-display text-lg text-ink shadow-hard">
                        🏆 Gagnant : {{ $result }}
                    </div>
                    @endif
                </section>
            </div>

            {{-- CONTRÔLES --}}
            <div class="space-y-6 lg:col-span-5">
                <section class="card-hard overflow-hidden rounded-2xl border-2 border-ink bg-panel p-5">
                    <h2 class="mb-4 font-display text-lg text-ink">
                        Configuration
                    </h2>

                    <x-draw.participant-form :participants="$participants" :weights="$participantWeights" :colors="collect($this->segments)->pluck('color')->toArray()" :error="$error" />
                </section>

                <button type="button" wire:click="draw" wire:loading.attr="disabled" wire:target="draw" :disabled="spinning || {{ $this->canDraw() ? 'false' : 'true' }}" @disabled(! $this->canDraw())
                    class="btn-press w-full rounded-xl border-2 border-ink bg-primary py-4 font-display text-sm text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
                    >
                    <span x-show="!spinning">
                        @if(! $this->canDraw())
                        AJOUTER AU MOINS 3 PARTICIPANTS
                        @else
                        ▶ LANCER LE TIRAGE
                        @endif
                    </span>

                    <span x-show="spinning" x-cloak>
                        LA ROUE TOURNE...
                    </span>
                </button>

                {{-- Résumé rapide de l'historique --}}
                <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h3 class="font-display text-base text-ink">Derniers tirages</h3>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('history') }}?filter=weighted" class="font-mono text-[10px] uppercase tracking-widest text-info hover:underline">
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
                        <div class="flex items-center justify-between rounded-xl border-2 border-ink bg-wash px-4 py-2.5 text-sm font-semibold text-ink">
                            <span class="truncate">🏆 {{ $entry['winner'] }}</span>
                            <span class="shrink-0 rounded-md border border-ink/20 bg-panel px-2 py-0.5 font-mono text-[11px] text-subtle">
                                poids {{ $entry['weights'][$entry['winner']] ?? '?' }}
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
