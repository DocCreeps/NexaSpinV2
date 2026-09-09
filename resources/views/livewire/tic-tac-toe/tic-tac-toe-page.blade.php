<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink">
    <div class="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

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
                    Morpion
                </h1>

                <p class="mt-3 max-w-md text-sm text-muted">
                    {{ $this->opponent()->xLabel() }} (✕) contre {{ $this->opponent()->oLabel() }} (◯). Alignez trois symboles identiques pour gagner.
                </p>
            </div>

            <div class="card-hard min-w-[110px] self-start rounded-xl border-2 border-ink bg-panel px-5 py-3 text-center">
                <p class="font-mono text-[9px] uppercase tracking-widest text-subtle">Parties</p>
                <p class="mt-0.5 font-display text-2xl text-ink">{{ count($history) }}</p>
            </div>
        </header>

        {{-- SÉLECTEUR D'ADVERSAIRE ET DIFFICULTÉ --}}
        <div class="flex flex-wrap items-center gap-2">
            @php $locked = count($moves) > 0 && !$this->board()->isOver(); @endphp

            {{-- Choix d'adversaire --}}
            <div class="inline-flex rounded-lg border-2 border-ink bg-panel p-0.5" @if($locked) title="Terminez la partie ou cliquez sur Nouvelle Partie pour changer d'adversaire" @endif>
                @foreach(\App\Domain\TicTacToe\Enums\TicTacToeOpponentType::cases() as $type)
                <button type="button" wire:click="setOpponentType('{{ $type->value }}')" @disabled($locked) @class([ 'rounded-md px-3.5 py-1.5 font-mono text-[10px] uppercase tracking-widest transition disabled:cursor-not-allowed disabled:opacity-60' , 'bg-ink text-white'=> $opponentType === $type->value,
                    'text-muted' => $opponentType !== $type->value,
                    ])>
                    {{ $type->label() }}
                </button>
                @endforeach
            </div>

            {{-- Choix de difficulté (affiché uniquement en mode IA) --}}
            @if($opponentType === \App\Domain\TicTacToe\Enums\TicTacToeOpponentType::AI->value)
            <div class="inline-flex rounded-lg border-2 border-ink bg-panel p-0.5" @if($locked) title="Terminez la partie pour changer de difficulté" @endif>
                @foreach(\App\Domain\TicTacToe\Enums\DifficultyLevel::cases() as $level)
                <button type="button" wire:click="setDifficulty('{{ $level->value }}')" @disabled($locked) @class([ 'rounded-md px-3 py-1.5 font-mono text-[10px] uppercase tracking-widest transition disabled:cursor-not-allowed disabled:opacity-60' , 'bg-primary text-white'=> $difficulty === $level->value,
                    'text-muted' => $difficulty !== $level->value,
                    ])>
                    {{ $level->label() }}
                </button>
                @endforeach
            </div>
            @endif

            @if($locked)
            <span class="font-mono text-[10px] text-faint">🔒 Partie en cours</span>
            @endif
        </div>

        {{-- ERREUR --}}
        @if($error)
        <div role="alert" class="rounded-xl border-2 border-ink bg-danger/10 px-4 py-3 text-sm font-semibold text-danger">
            ⚠ {{ $error }}
        </div>
        @endif

        {{-- PLATEAU --}}
        <section class="card-hard flex flex-col items-center gap-6 rounded-2xl border-2 border-ink bg-panel p-6 sm:p-8">

            {{-- État --}}
            <div class="min-h-[36px] text-center">
                @if($this->board()->isOver())
                @if($this->board()->winner())
                <p class="font-display text-2xl text-primary">
                    {{ $this->board()->winner()->value === 'x' ? $this->opponent()->xLabel() : $this->opponent()->oLabel() }} GAGNE !
                </p>
                @else
                <p class="font-display text-2xl text-ink">ÉGALITÉ</p>
                @endif
                @else
                <p class="font-mono text-sm tracking-wider text-muted">
                    Au tour de <strong class="text-ink">{{ $this->board()->currentTurn()->value === 'x' ? $this->opponent()->xLabel() : $this->opponent()->oLabel() }} ({{ $this->board()->currentTurn()->value === 'x' ? '✕' : '◯' }})</strong>
                </p>
                @endif
            </div>

            {{-- Grille --}}
            <div class="grid grid-cols-3 gap-2" role="group" aria-label="Grille de morpion">
                @foreach($this->board()->cells() as $index => $mark)
                <button type="button" wire:click="play({{ $index }})" @disabled($mark !==null || $this->board()->isOver()) class="flex h-20 w-20 items-center justify-center rounded-xl border-2 border-ink bg-wash font-display text-3xl shadow-hard transition disabled:cursor-not-allowed sm:h-24 sm:w-24" aria-label="Case {{ $index + 1 }}{{ $mark ? ', occupée par '.($mark->value === 'x' ? 'X' : 'O') : '' }}">
                    @if($mark?->value === 'x')
                    <span class="text-primary">✕</span>
                    @elseif($mark?->value === 'o')
                    <span class="text-info">◯</span>
                    @endif
                </button>
                @endforeach
            </div>

            @if($this->board()->isOver())
            <button type="button" wire:click="restart" class="card-hard rounded-xl border-2 border-ink bg-primary px-6 py-3 font-display text-sm text-white transition hover:-translate-x-px hover:-translate-y-px hover:shadow-hard-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                NOUVELLE PARTIE
            </button>
            @endif
        </section>

        {{-- HISTORIQUE DANS LA PAGE GAME --}}
        @if(count($history))
        <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-display text-base text-ink">Historique de la session</h3>
                <button type="button" wire:click="clearHistory" wire:confirm="Vider l'historique ?" class="font-mono text-[10px] uppercase tracking-widest text-subtle transition hover:text-danger">
                    Vider
                </button>
            </div>

            <div class="max-h-64 space-y-1.5 overflow-y-auto pr-1">
                @foreach(array_reverse($history) as $entry)
                @php
                $opponentVal = $entry['opponent_type'] ?? $entry['opponent'] ?? '';
                $isAi = $opponentVal === \App\Domain\TicTacToe\Enums\TicTacToeOpponentType::AI->value;
                $diffEnum = isset($entry['difficulty']) ? \App\Domain\TicTacToe\Enums\DifficultyLevel::tryFrom($entry['difficulty']) : null;
                @endphp

                <div class="flex items-center justify-between rounded-xl border-2 border-line bg-wash px-3 py-2 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-ink">
                            @if($entry['winner'] === null)
                            Égalité
                            @elseif($entry['winner'] === 'x')
                            {{ $isAi ? 'Joueur' : 'Joueur 1' }} gagne
                            @else
                            {{ $isAi ? 'IA' : 'Joueur 2' }} gagne
                            @endif
                        </span>

                        {{-- Badge de difficulté si partie contre l'IA --}}
                        @if($isAi && $diffEnum)
                        <span class="rounded bg-line px-1.5 py-0.5 font-mono text-[9px] uppercase text-muted">
                            {{ $diffEnum->label() }}
                        </span>
                        @else
                        <span class="rounded bg-line px-1.5 py-0.5 font-mono text-[9px] uppercase text-muted">
                            Local
                        </span>
                        @endif
                    </div>

                    <span class="font-mono text-[11px] text-subtle">
                        {{ $entry['moves_count'] }} coups
                    </span>
                </div>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</div>
