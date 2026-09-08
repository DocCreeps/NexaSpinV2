<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink" x-data="{
        playing: false,
        revealed: false,
        passOverlay: false
    }" x-on:rps-played.window="
        playing = true;
        revealed = false;
        setTimeout(() => {
            revealed = true;
            playing = false;
            setTimeout(() => $wire.confirmRound(), 500);
        }, 700);
    " x-on:rps-local-first-played.window="passOverlay = true;">
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- HEADER --}}
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>

                <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">
                    ◆ {{ $this->opponent()->label() }} ◆
                </p>

                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">
                    Pierre · Feuille · Ciseaux
                </h1>

                <p class="mt-3 max-w-md text-sm text-muted">
                    Affrontez {{ $opponentType === 'local' ? 'un ami sur le même appareil' : 'un adversaire aléatoire' }}, manche après manche.
                </p>
            </div>

            <div class="card-hard self-start rounded-xl border-2 border-ink bg-panel px-5 py-3 text-center min-w-[110px]">
                <p class="font-mono text-[9px] uppercase tracking-widest text-subtle">Manches</p>
                <p class="mt-0.5 font-display text-2xl text-ink">{{ count($history) }}</p>
            </div>
        </header>

        {{-- SÉLECTEUR D'ADVERSAIRE --}}
        <div class="flex flex-wrap items-center gap-2">
            @php $locked = count($history) > 0 || $localFirstChoice !== null; @endphp
            <div class="inline-flex rounded-lg border-2 border-ink bg-panel p-0.5" @if($locked) title="Videz l'historique pour changer d'adversaire" @endif>
                @foreach(\App\Domain\RockPaperScissors\Enums\RpsOpponentType::cases() as $type)
                <button type="button" wire:click="setOpponentType('{{ $type->value }}')" @disabled($locked) @class([
                        'rounded-md px-3.5 py-1.5 font-mono text-[10px] uppercase tracking-widest transition disabled:cursor-not-allowed disabled:opacity-60',
                        'bg-ink text-white' => $opponentType === $type->value,
                        'text-muted' => $opponentType !== $type->value,
                    ])>
                    {{ $type->label() }}
                </button>
                @endforeach
            </div>
            @if($locked)
            <span class="font-mono text-[10px] text-faint">🔒 verrouillé pour cette session</span>
            @endif
        </div>

        {{-- GRILLE --}}
        <div class="grid items-start gap-6 lg:grid-cols-12 lg:gap-8">

            {{-- GAUCHE : arène --}}
            <div class="lg:col-span-7">
                <section class="card-hard flex min-h-[380px] flex-col items-center justify-center gap-8 rounded-2xl border-2 border-ink bg-panel p-6 sm:p-8">

                    {{-- Face à face --}}
                    <div class="flex w-full items-center justify-center gap-6 sm:gap-12">
                        <div class="flex flex-col items-center gap-2">
                            <span class="font-mono text-[10px] uppercase tracking-widest text-subtle">{{ $this->opponent()->playerALabel() }}</span>
                            <div class="flex h-24 w-24 items-center justify-center rounded-2xl border-2 border-ink bg-wash text-4xl shadow-hard sm:h-28 sm:w-28">
                                <span x-show="revealed" x-cloak>{{ $playerChoice ? ['rock' => '🪨', 'paper' => '📄', 'scissors' => '✂️'][$playerChoice] : '' }}</span>
                                <span x-show="!revealed" class="animate-pulse text-faint">?</span>
                            </div>
                        </div>

                        <span class="font-display text-2xl text-faint">VS</span>

                        <div class="flex flex-col items-center gap-2">
                            <span class="font-mono text-[10px] uppercase tracking-widest text-subtle">{{ $this->opponent()->playerBLabel() }}</span>
                            <div class="flex h-24 w-24 items-center justify-center rounded-2xl border-2 border-ink bg-wash text-4xl shadow-hard sm:h-28 sm:w-28">
                                <span x-show="revealed" x-cloak>{{ $opponentChoice ? ['rock' => '🪨', 'paper' => '📄', 'scissors' => '✂️'][$opponentChoice] : '' }}</span>
                                <span x-show="!revealed" class="animate-pulse text-faint">?</span>
                            </div>
                        </div>
                    </div>

                    {{-- Résultat --}}
                    <div x-show="revealed" x-cloak class="flex items-center gap-2 rounded-xl border-2 border-ink px-6 py-3 font-display text-lg shadow-hard" @class([
                            'bg-secondary text-ink' => $outcome === 'win',
                            'bg-danger/10 text-danger' => $outcome === 'lose',
                            'bg-wash text-ink' => $outcome === 'draw',
                        ])>
                        @if($outcome === 'win')
                        🏆 Gagné !
                        @elseif($outcome === 'lose')
                        ✗ Perdu
                        @elseif($outcome === 'draw')
                        🤝 Égalité
                        @endif
                    </div>

                    @if($opponentType === 'local' && $localFirstChoice !== null)
                    {{-- En attente du 2e joueur : écran "passez l'appareil" puis ses boutons --}}
                    <div x-show="passOverlay" x-cloak class="flex w-full max-w-md flex-col items-center gap-4 rounded-xl border-2 border-dashed border-ink bg-wash px-6 py-8 text-center">
                        <span class="text-3xl">🔁</span>
                        <p class="font-display text-lg text-ink">Passez l'appareil à {{ $this->opponent()->playerBLabel() }}</p>
                        <p class="text-xs text-muted">{{ $this->opponent()->playerALabel() }} a déjà choisi.</p>
                        <button type="button" x-on:click="passOverlay = false" class="card-hard rounded-xl border-2 border-ink bg-primary px-5 py-2.5 font-display text-sm text-white transition hover:-translate-x-px hover:-translate-y-px hover:shadow-hard-lg">
                            C'est bon, j'ai l'appareil
                        </button>
                    </div>

                    <div x-show="!passOverlay" x-cloak class="grid w-full max-w-md grid-cols-3 gap-3">
                        <p class="col-span-3 -mt-2 mb-1 text-center font-mono text-[10px] uppercase tracking-widest text-subtle">
                            À {{ $this->opponent()->playerBLabel() }} de choisir
                        </p>
                        <button type="button" wire:click="play('rock')" :disabled="playing" class="btn-press flex flex-col items-center gap-1 rounded-xl border-2 border-ink bg-primary py-4 text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:opacity-50">
                            <span class="text-2xl">🪨</span>
                            <span class="font-mono text-[10px] uppercase tracking-widest">Pierre</span>
                        </button>
                        <button type="button" wire:click="play('paper')" :disabled="playing" class="btn-press flex flex-col items-center gap-1 rounded-xl border-2 border-ink bg-primary py-4 text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:opacity-50">
                            <span class="text-2xl">📄</span>
                            <span class="font-mono text-[10px] uppercase tracking-widest">Feuille</span>
                        </button>
                        <button type="button" wire:click="play('scissors')" :disabled="playing" class="btn-press flex flex-col items-center gap-1 rounded-xl border-2 border-ink bg-primary py-4 text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:opacity-50">
                            <span class="text-2xl">✂️</span>
                            <span class="font-mono text-[10px] uppercase tracking-widest">Ciseaux</span>
                        </button>
                    </div>
                    @else
                    {{-- Adversaire IA, ou local en attente du 1er joueur --}}
                    <div class="grid w-full max-w-md grid-cols-3 gap-3">
                        @if($opponentType === 'local')
                        <p class="col-span-3 -mt-2 mb-1 text-center font-mono text-[10px] uppercase tracking-widest text-subtle">
                            À {{ $this->opponent()->playerALabel() }} de choisir
                        </p>
                        @endif
                        <button type="button" wire:click="play('rock')" :disabled="playing" class="btn-press flex flex-col items-center gap-1 rounded-xl border-2 border-ink bg-primary py-4 text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:opacity-50">
                            <span class="text-2xl">🪨</span>
                            <span class="font-mono text-[10px] uppercase tracking-widest">Pierre</span>
                        </button>
                        <button type="button" wire:click="play('paper')" :disabled="playing" class="btn-press flex flex-col items-center gap-1 rounded-xl border-2 border-ink bg-primary py-4 text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:opacity-50">
                            <span class="text-2xl">📄</span>
                            <span class="font-mono text-[10px] uppercase tracking-widest">Feuille</span>
                        </button>
                        <button type="button" wire:click="play('scissors')" :disabled="playing" class="btn-press flex flex-col items-center gap-1 rounded-xl border-2 border-ink bg-primary py-4 text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:opacity-50">
                            <span class="text-2xl">✂️</span>
                            <span class="font-mono text-[10px] uppercase tracking-widest">Ciseaux</span>
                        </button>
                    </div>
                    @endif
                </section>
            </div>

            {{-- DROITE : stats + historique --}}
            <div class="space-y-6 lg:col-span-5">
                <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
                    <h2 class="mb-4 font-display text-lg text-ink">Statistiques</h2>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-xl border-2 border-ink bg-secondary/40 px-3 py-3 text-center">
                            <p class="truncate font-mono text-[9px] uppercase tracking-widest text-ink/70">{{ $this->opponent()->playerALabel() }}</p>
                            <p class="mt-0.5 font-display text-xl text-ink">{{ $this->winCount() }}</p>
                        </div>
                        <div class="rounded-xl border-2 border-ink bg-wash px-3 py-3 text-center">
                            <p class="font-mono text-[9px] uppercase tracking-widest text-subtle">Nulles</p>
                            <p class="mt-0.5 font-display text-xl text-ink">{{ $this->drawCount() }}</p>
                        </div>
                        <div class="rounded-xl border-2 border-ink bg-danger/10 px-3 py-3 text-center">
                            <p class="truncate font-mono text-[9px] uppercase tracking-widest text-danger/70">{{ $this->opponent()->playerBLabel() }}</p>
                            <p class="mt-0.5 font-display text-xl text-danger">{{ $this->loseCount() }}</p>
                        </div>
                    </div>
                </section>

                <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-display text-base text-ink">Historique</h3>
                        @if(count($history))
                        <button type="button" wire:click="resetHistory" wire:confirm="Vider l'historique ?" class="font-mono text-[10px] uppercase tracking-widest text-subtle transition hover:text-danger">
                            Vider
                        </button>
                        @endif
                    </div>

                    @if(count($history))
                    <div class="custom-scrollbar max-h-[320px] space-y-1.5 overflow-y-auto pr-1">
                        @foreach(array_reverse($history, true) as $index => $entry)
                        <div @class([
                            'flex items-center justify-between rounded-xl border-2 border-ink px-3 py-2 text-sm',
                            'bg-secondary/20' => $entry['outcome'] === 'win',
                            'bg-danger/5' => $entry['outcome'] === 'lose',
                            'bg-wash' => $entry['outcome'] === 'draw',
                            ])>
                            <span class="font-mono text-xs text-muted">
                                {{ ['rock' => 'Pierre', 'paper' => 'Feuille', 'scissors' => 'Ciseaux'][$entry['a']] }}
                                vs
                                {{ ['rock' => 'Pierre', 'paper' => 'Feuille', 'scissors' => 'Ciseaux'][$entry['b']] }}
                            </span>
                            <span @class([
                                'rounded-md border px-1.5 py-0.5 font-mono text-[10px] font-bold uppercase',
                                'border-ink bg-secondary text-ink' => $entry['outcome'] === 'win',
                                'border-danger/30 bg-danger/10 text-danger' => $entry['outcome'] === 'lose',
                                'border-ink/20 bg-panel text-subtle' => $entry['outcome'] === 'draw',
                                ])>
                                @if($entry['outcome'] === 'win')
                                {{ $opponentType === 'local' ? $this->opponent()->playerALabel() : 'Gagné' }}
                                @elseif($entry['outcome'] === 'lose')
                                {{ $opponentType === 'local' ? $this->opponent()->playerBLabel() : 'Perdu' }}
                                @else
                                Nul
                                @endif
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="rounded-xl border-2 border-dashed border-line py-8 text-center">
                        <p class="text-sm font-medium text-muted">Aucune manche pour l’instant</p>
                    </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</div>
