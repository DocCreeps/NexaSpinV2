<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink" x-data="{
        busy: false,
        autoMode: @entangle('autoMode').live
    }" x-on:wheel-spin.window="busy = true" x-on:wheel-spin-finished.window="busy = false; $wire.confirmElimination()" x-on:tournament-finished.window="setTimeout(() => $wire.confirmTournamentHistory(), 500)" x-on:elimination-confirmed.window="
        if (autoMode && !$wire.winner) {
            setTimeout(() => {
                if (autoMode && !busy && !$wire.winner) {
                    $wire.eliminateNext()
                }
            }, 2000)
        }
    ">
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- HEADER --}}
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>

                <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">
                    ◆ Élimination progressive ◆
                </p>

                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">
                    Roue par élimination
                </h1>

                <p class="mt-3 max-w-md text-sm text-muted">
                    Chaque tour élimine un joueur jusqu’au dernier survivant.
                </p>
            </div>

            <div class="flex gap-3 self-start">
                <div class="card-hard min-w-[110px] rounded-xl border-2 border-ink bg-panel px-5 py-3 text-center">
                    <p class="font-mono text-[9px] uppercase tracking-widest text-subtle">Survivants</p>
                    <p class="mt-0.5 font-display text-2xl text-ink">
                        {{ count($participants) }}
                    </p>
                </div>

                <div class="card-hard min-w-[110px] rounded-xl border-2 border-ink bg-danger/10 px-5 py-3 text-center">
                    <p class="font-mono text-[9px] uppercase tracking-widest text-danger/70">Sorties</p>
                    <p class="mt-0.5 font-display text-2xl text-danger">
                        {{ count($eliminated) }}
                    </p>
                </div>
            </div>
        </header>

        {{-- ERREUR --}}
        @if($error)
        <div role="alert" class="rounded-xl border-2 border-ink bg-danger/10 px-4 py-3 text-sm font-semibold text-danger">
            ⚠ {{ $error }}
        </div>
        @endif

        {{-- POLL pendant processing --}}
        @if($processing)
        <div wire:poll.3s="$refresh" x-data></div>
        @endif

        {{-- BLOCAGE --}}
        @if($this->isStuck())
        <div class="flex flex-col gap-3 rounded-xl border-2 border-ink bg-secondary/40 px-4 py-4 text-ink sm:flex-row sm:items-center sm:justify-between">
            <span class="text-sm font-semibold">
                ⏳ La roue semble bloquée (connexion perdue pendant l’animation ?)
            </span>
            <button type="button" wire:click="unstick" class="btn-press shrink-0 rounded-xl border-2 border-ink bg-primary px-4 py-2 font-display text-xs text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                DÉBLOQUER
            </button>
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

                    @if($pendingElimination)
                    <div class="mt-8 flex items-center gap-2 rounded-xl border-2 border-ink bg-danger/10 px-5 py-3 font-display text-sm text-danger shadow-hard">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-danger opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-danger"></span>
                        </span>
                        Cible : {{ $pendingElimination }}
                    </div>
                    @elseif($winner)
                    <div class="mt-8 flex items-center gap-2 rounded-xl border-2 border-ink bg-secondary px-6 py-3 font-display text-lg text-ink shadow-hard">
                        🏆 Gagnant : {{ $winner }}
                    </div>
                    @endif
                </section>
            </div>

            {{-- CONTRÔLES --}}
            <div class="space-y-6 lg:col-span-5">

                {{-- Config --}}
                <section class="card-hard overflow-hidden rounded-2xl border-2 border-ink bg-panel p-5">
                    <h2 class="mb-4 font-display text-lg text-ink">
                        Configuration
                    </h2>

                    @php
                    $indexedColors = [];
                    foreach ($participants as $index => $name) {
                    $indexedColors[$index] = $colors[$name] ?? '#ccc';
                    }
                    @endphp

                    <x-draw.participant-form :participants="$participants" :colors="$indexedColors" :locked="$this->started()" :error="$error" />
                </section>

                {{-- Mode auto --}}
                @if(! $winner && ($this->started() || $this->canStart()))
                <section class="card-hard flex items-center justify-between rounded-2xl border-2 border-ink bg-panel p-5">
                    <div>
                        <p class="font-display text-sm text-ink">Mode automatique</p>
                        <p class="text-xs text-subtle">Enchaîne les éliminations automatiquement</p>
                    </div>

                    <label class="relative inline-flex cursor-pointer select-none items-center">
                        <input type="checkbox" x-model="autoMode" class="peer sr-only" :disabled="$wire.winner">
                        <div class="h-6 w-11 rounded-full border-2 border-ink bg-wash after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border-2 after:border-ink after:bg-panel after:transition-all peer-checked:bg-primary peer-checked:after:translate-x-full"></div>
                    </label>
                </section>
                @endif

                {{-- Action principale --}}
                @if(! $winner)
                @if($autoMode)
                <button type="button" wire:click="$set('autoMode', false)" class="card-hard w-full rounded-xl border-2 border-ink bg-ink py-4 font-display text-sm text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ⏸ METTRE EN PAUSE
                </button>
                @else
                <button type="button" wire:click="handleAction" wire:loading.attr="disabled" wire:target="handleAction,eliminateNext" x-bind:disabled="busy || {{ (! $this->started() && ! $this->canStart()) ? 'true' : 'false' }}" @disabled(! $this->started() && ! $this->canStart())
                    class="btn-press w-full rounded-xl border-2 border-ink bg-primary py-4 font-display text-sm text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
                    >
                    <span x-show="!busy">
                        @if(! $this->started() && ! $this->canStart())
                        AJOUTER AU MOINS 5 PARTICIPANTS
                        @elseif($this->started())
                        ▶ ÉLIMINER LE PROCHAIN
                        @else
                        ▶ COMMENCER LA PARTIE
                        @endif
                    </span>
                    <span x-show="busy" x-cloak>
                        LA ROUE TOURNE...
                    </span>
                </button>
                @endif
                @endif

                {{-- Historique éliminations --}}
                @if(count($eliminated))
                <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
                    <h3 class="mb-4 font-display text-base text-ink">
                        Ordre d’élimination
                    </h3>

                    <div class="max-h-[220px] space-y-2 overflow-y-auto pr-1">
                        @foreach(array_reverse($eliminated, true) as $index => $player)
                        <div class="flex items-center justify-between rounded-xl border-2 border-ink bg-danger/5 px-4 py-3 text-sm">
                            <span class="font-semibold text-subtle line-through">
                                {{ $player }}
                            </span>
                            <span class="rounded-md border-2 border-ink bg-panel px-2.5 py-1 font-mono text-[11px] font-bold text-danger">
                                #{{ $index + 1 }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- Restart --}}
                @if($winner)
                <button type="button" wire:click="restart" class="card-hard w-full rounded-xl border-2 border-ink bg-panel py-4 font-display text-sm text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    NOUVELLE PARTIE
                </button>
                @endif

                {{-- Résumé rapide des tournois précédents --}}
                <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h3 class="font-display text-base text-ink">Tournois précédents</h3>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('history') }}?filter=elimination" class="font-mono text-[10px] uppercase tracking-widest text-info hover:underline">
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
                                {{ count($entry['participants']) }} participants
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="rounded-xl border-2 border-dashed border-line py-6 text-center">
                        <p class="text-sm font-medium text-muted">Aucun tournoi terminé pour l’instant</p>
                    </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</div>
