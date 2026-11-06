@php
    $game = $this->game;
    $aiPending = $this->aiPending();
    $humanTurn = $started && ! $game->isOver() && ! $aiPending;
    $lastMove = $this->lastMove();
    $resultPending = $this->resultPending();
    $score = $this->score();
    $remaining = $game->remaining();
    $rule = $this->variantRule();
    $lastWins = $rule === \App\Domain\Sticks\Enums\SticksVariant::LAST_WINS;
    $vsAi = $this->isVsAi();
    $maxTake = \App\Domain\Sticks\Entities\SticksGame::MAX_TAKE;

    // Couleur de chaque joueur : 1 = bleu, 2 = rouge.
    $colors = [
        1 => ['bg' => 'bg-info', 'text' => 'text-info'],
        2 => ['bg' => 'bg-primary', 'text' => 'text-primary'],
    ];
    $current = $game->currentPlayer();
    $critical = ! $game->isOver() && $remaining <= ($lastWins ? $maxTake : $maxTake + 1);
    $progress = $game->stickCount > 0 ? round($remaining / $game->stickCount * 100) : 0;
@endphp

<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink">
    <div class="mx-auto max-w-3xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- HEADER --}}
        <header class="border-b-4 border-ink pb-5">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                ← Salle
            </a>

            <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">
                ◆ {{ $this->opponent()->label() }} ◆
            </p>

            <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">
                Jeu des bâtonnets
            </h1>

            <p class="mt-3 max-w-md text-sm text-muted">
                {{ $stickCount }} bâtonnets. À tour de rôle, retirez-en 1, 2 ou 3, où vous voulez. {{ $rule->description() }}
            </p>
        </header>

        @if(! $started)
        {{-- RÉGLAGES (avant le lancement de la partie) --}}
        <div class="space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                {{-- Choix d'adversaire --}}
                <div class="inline-flex rounded-lg border-2 border-ink bg-panel p-0.5">
                    @foreach(\App\Domain\Sticks\Enums\SticksOpponentType::cases() as $type)
                    <button type="button" wire:click="setOpponentType('{{ $type->value }}')" @class([
                        'rounded-md px-3.5 py-1.5 font-mono text-[10px] uppercase tracking-widest transition',
                        'bg-ink text-white' => $opponentType === $type->value,
                        'text-muted' => $opponentType !== $type->value,
                    ])>
                        {{ $type->label() }}
                    </button>
                    @endforeach
                </div>

                {{-- Règle --}}
                <div class="inline-flex rounded-lg border-2 border-ink bg-panel p-0.5">
                    @foreach(\App\Domain\Sticks\Enums\SticksVariant::cases() as $option)
                    <button type="button" wire:click="setVariant('{{ $option->value }}')" @class([
                        'rounded-md px-3 py-1.5 font-mono text-[10px] uppercase tracking-widest transition',
                        'bg-info text-white' => $variant === $option->value,
                        'text-muted' => $variant !== $option->value,
                    ])>
                        {{ $option->label() }}
                    </button>
                    @endforeach
                </div>

                @if($vsAi)
                {{-- Difficulté --}}
                <div class="inline-flex rounded-lg border-2 border-ink bg-panel p-0.5">
                    @foreach(\App\Domain\Sticks\Enums\SticksDifficulty::cases() as $level)
                    <button type="button" wire:click="setDifficulty('{{ $level->value }}')" @class([
                        'rounded-md px-3 py-1.5 font-mono text-[10px] uppercase tracking-widest transition',
                        'bg-primary text-white' => $difficulty === $level->value,
                        'text-muted' => $difficulty !== $level->value,
                    ])>
                        {{ $level->label() }}
                    </button>
                    @endforeach
                </div>

                {{-- Qui commence --}}
                <div class="inline-flex rounded-lg border-2 border-ink bg-panel p-0.5">
                    <button type="button" wire:click="setAiStarts(false)" @class([
                        'rounded-md px-3 py-1.5 font-mono text-[10px] uppercase tracking-widest transition',
                        'bg-secondary text-ink' => ! $aiStarts,
                        'text-muted' => $aiStarts,
                    ])>
                        Je commence
                    </button>
                    <button type="button" wire:click="setAiStarts(true)" @class([
                        'rounded-md px-3 py-1.5 font-mono text-[10px] uppercase tracking-widest transition',
                        'bg-secondary text-ink' => $aiStarts,
                        'text-muted' => ! $aiStarts,
                    ])>
                        L’IA commence
                    </button>
                </div>
                @endif
            </div>

            @unless($vsAi)
            {{-- Duel local : prénoms et ordre de départ --}}
            <div class="card-hard space-y-3 rounded-xl border-2 border-ink bg-panel px-4 py-3"
                x-data="{ p1: @js($player1Name), p2: @js($player2Name) }">
                <p class="font-mono text-[10px] uppercase tracking-widest text-subtle">Joueurs</p>

                <div class="grid gap-2 sm:grid-cols-2">
                    <label class="flex items-center gap-2 rounded-lg border-2 border-ink bg-wash px-3 py-2">
                        <span class="h-3 w-3 shrink-0 rounded-full border-2 border-ink bg-info" aria-hidden="true"></span>
                        <input type="text" x-model="p1" maxlength="{{ \App\Livewire\Sticks\SticksPage::MAX_NAME_LENGTH }}" placeholder="Joueur 1" x-on:change="$wire.setPlayerNames(p1, p2)" aria-label="Prénom du joueur 1" class="w-full bg-transparent font-semibold text-ink placeholder:font-normal placeholder:text-faint focus:outline-none">
                    </label>

                    <label class="flex items-center gap-2 rounded-lg border-2 border-ink bg-wash px-3 py-2">
                        <span class="h-3 w-3 shrink-0 rounded-full border-2 border-ink bg-primary" aria-hidden="true"></span>
                        <input type="text" x-model="p2" maxlength="{{ \App\Livewire\Sticks\SticksPage::MAX_NAME_LENGTH }}" placeholder="Joueur 2" x-on:change="$wire.setPlayerNames(p1, p2)" aria-label="Prénom du joueur 2" class="w-full bg-transparent font-semibold text-ink placeholder:font-normal placeholder:text-faint focus:outline-none">
                    </label>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-[10px] uppercase tracking-widest text-subtle">Commence</span>
                    <div class="inline-flex rounded-lg border-2 border-ink bg-panel p-0.5">
                        @foreach([1, 2] as $player)
                        <button type="button" wire:click="setLocalStarter({{ $player }})" @class([
                            'rounded-md px-3 py-1.5 font-mono text-[10px] uppercase tracking-widest transition',
                            'bg-secondary text-ink' => $localStarter === $player,
                            'text-muted' => $localStarter !== $player,
                        ])>
                            {{ $this->playerLabel($player) }}
                        </button>
                        @endforeach
                    </div>
                    <span class="font-mono text-[10px] text-faint">Les rôles s’échangent à chaque revanche.</span>
                </div>
            </div>
            @endunless

            {{-- Nombre de bâtonnets --}}
            <div class="card-hard flex items-center gap-3 rounded-xl border-2 border-ink bg-panel px-4 py-3">
                <label for="stick-count" class="shrink-0 font-mono text-[10px] uppercase tracking-widest text-subtle">Bâtonnets</label>
                <input id="stick-count" type="range" min="{{ \App\Domain\Sticks\Entities\SticksGame::MIN_STICKS }}" max="{{ \App\Domain\Sticks\Entities\SticksGame::MAX_STICKS }}" value="{{ $stickCount }}" wire:change="setStickCount($event.target.value)" class="h-2 w-full cursor-pointer accent-primary">
                <span class="w-8 shrink-0 text-right font-display text-lg text-ink">{{ $stickCount }}</span>
            </div>

            <div class="flex justify-center pt-2">
                <button type="button" wire:click="startGame" class="btn-press rounded-xl border-2 border-ink bg-primary px-8 py-3.5 font-display text-base text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ▶ LANCER LA PARTIE
                </button>
            </div>
        </div>
        @else
        {{-- Rappel des réglages pendant la partie --}}
        <div class="flex flex-wrap items-center gap-2 font-mono text-[10px] uppercase tracking-widest text-subtle">
            <span class="rounded-md border-2 border-ink bg-panel px-2.5 py-1">{{ $this->opponent()->label() }}</span>
            <span class="rounded-md border-2 border-ink bg-panel px-2.5 py-1">{{ $rule->label() }}</span>
            @if($vsAi)
            <span class="rounded-md border-2 border-ink bg-panel px-2.5 py-1">{{ \App\Domain\Sticks\Enums\SticksDifficulty::from($difficulty)->label() }}</span>
            @endif
            <span class="rounded-md border-2 border-ink bg-panel px-2.5 py-1">{{ $stickCount }} bâtonnets</span>
        </div>

        {{-- PLATEAU --}}
        <section class="card-hard flex flex-col items-center gap-6 rounded-2xl border-2 border-ink bg-panel p-5 sm:p-8"
            @if($humanTurn) x-on:keydown.window="if (['1','2','3'].includes($event.key) && ! $event.target.closest('input, textarea, select')) $wire.takeFirst(parseInt($event.key))" @endif
            x-data="{
                selected: [],
                max: {{ $maxTake }},
                toggle(index) {
                    const position = this.selected.indexOf(index);
                    if (position >= 0) {
                        this.selected.splice(position, 1);
                    } else if (this.selected.length < this.max) {
                        this.selected.push(index);
                    }
                },
                async confirm() {
                    if (this.selected.length === 0) return;
                    await $wire.take([...this.selected]);
                    this.selected = [];
                }
            }">

            {{-- Déclencheurs temporisés : coup de l'IA, puis annonce du résultat --}}
            @if($aiPending)
            <div wire:key="ai-turn-{{ count($moves) }}" x-data x-init="setTimeout(() => $wire.playAiTurn(), {{ \App\Livewire\Sticks\SticksPage::AI_DELAY_MS }})" class="hidden"></div>
            @elseif($resultPending)
            <div wire:key="result-{{ count($moves) }}" x-data x-init="setTimeout(() => $wire.revealResult(), {{ \App\Livewire\Sticks\SticksPage::RESULT_DELAY_MS }})" class="hidden"></div>
            @endif

            {{-- Joueurs + score de la session --}}
            <div class="flex w-full items-stretch gap-3">
                @foreach([1, 2] as $player)
                @php
                    $active = ! $game->isOver() && $current === $player;
                    $won = $game->isOver() && $resultRevealed && $game->winner() === $player;
                @endphp
                <div @class([
                    'min-w-0 flex-1 rounded-xl border-2 border-ink px-3 py-2.5 text-center transition duration-300',
                    $colors[$player]['bg'].' -translate-y-0.5 text-white shadow-hard' => $active || $won,
                    'bg-wash text-ink opacity-70' => ! $active && ! $won,
                ])>
                    <p class="truncate font-mono text-[10px] uppercase tracking-widest">{{ $won ? '🏆 ' : '' }}{{ $this->playerLabel($player) }}</p>
                    <p class="font-display text-3xl leading-tight">{{ $score['p'.$player] }}</p>
                    <p class="h-3 font-mono text-[9px] uppercase tracking-widest">{{ $active ? ($aiPending ? 'réfléchit…' : '● à jouer') : '' }}</p>
                </div>

                @if($player === 1)
                <div class="flex items-center font-display text-sm text-faint" aria-hidden="true">VS</div>
                @endif
                @endforeach
            </div>

            {{-- État --}}
            <div class="min-h-[72px] space-y-1.5 text-center" aria-live="polite">
                @if($game->isOver() && $resultRevealed)
                <p class="font-display text-3xl {{ $colors[$game->winner()]['text'] }}">
                    @if($vsAi)
                    {{ $game->winner() === 1 ? 'VICTOIRE !' : 'DÉFAITE…' }}
                    @else
                    {{ $this->playerLabel($game->winner()) }} GAGNE !
                    @endif
                </p>
                <p class="font-mono text-xs text-muted">
                    @if($lastWins)
                    {{ $this->playerLabel($game->winner()) }} a pris le dernier bâtonnet.
                    @else
                    Le dernier bâtonnet revient à {{ $this->playerLabel($game->loser()) }}.
                    @endif
                </p>
                @else
                @if($aiPending)
                <p class="animate-pulse font-mono text-sm tracking-wider text-muted">
                    <strong class="{{ $colors[2]['text'] }}">{{ $this->playerLabel(2) }}</strong> réfléchit…
                </p>
                @elseif(! $game->isOver())
                <p class="font-mono text-sm tracking-wider text-muted">
                    Au tour de <strong class="{{ $colors[$current]['text'] }}">{{ $this->playerLabel($current) }}</strong>
                </p>
                @endif

                @if($lastMove)
                <p @class([
                    'font-mono text-xs text-subtle',
                    'animate-pulse text-sm text-ink' => $resultPending,
                ])>
                    {{ $this->playerLabel($lastMove['player']) }} {{ $lastMove['player'] === 1 && $vsAi ? 'avez retiré' : 'a retiré' }}
                    <strong class="text-ink">{{ $lastMove['count'] }} bâtonnet{{ $lastMove['count'] > 1 ? 's' : '' }}</strong>.
                </p>
                @endif
                @endif
            </div>

            {{-- Compteur de bâtonnets restants --}}
            <div class="w-full max-w-md">
                <div class="mb-1.5 flex items-end justify-between">
                    <span class="font-mono text-[10px] uppercase tracking-widest text-subtle">Bâtonnets restants</span>
                    <span @class([
                        'font-display text-3xl leading-none transition-colors',
                        'text-ink' => ! $critical,
                        'text-danger' => $critical,
                    ])>{{ $remaining }}<span class="ml-1 text-sm text-faint">/ {{ $game->stickCount }}</span></span>
                </div>

                <div class="h-3.5 overflow-hidden rounded-full border-2 border-ink bg-wash" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $game->stickCount }}" aria-valuenow="{{ $remaining }}" aria-label="Bâtonnets restants">
                    <div @class([
                        'h-full rounded-full transition-all duration-500',
                        'bg-secondary' => ! $critical,
                        'bg-danger' => $critical,
                    ]) style="width: {{ $progress }}%"></div>
                </div>

                <p class="mt-1.5 h-4 text-center font-mono text-[10px] uppercase tracking-widest text-danger">
                    @if($critical && $humanTurn)
                    {{ $lastWins ? 'Vous pouvez tout prendre pour gagner !' : 'Attention, fin de partie proche !' }}
                    @endif
                </p>
            </div>

            {{-- Bâtonnets : posés sur une table, retirés avec une petite animation --}}
            <div class="w-full">
                <div class="flex min-h-[9rem] flex-wrap items-end justify-center rounded-t-2xl border-2 border-b-0 border-ink bg-wash px-3 pb-0 pt-8 sm:min-h-[11.5rem]" role="group" aria-label="Bâtonnets">
                    @for($i = 0; $i < $game->stickCount; $i++)
                    @php $removed = $game->isRemoved($i); @endphp
                    <span wire:key="stick-{{ $i }}" @class([
                        'flex h-24 items-end justify-center transition-all duration-500 ease-out sm:h-32',
                        'mx-[2px] w-5 sm:mx-[3px] sm:w-6' => ! $removed,
                        'pointer-events-none mx-0 w-0 -translate-y-10 rotate-12 scale-y-50 opacity-0' => $removed,
                    ])>
                        <button type="button" x-on:click="toggle({{ $i }})" :aria-pressed="selected.includes({{ $i }}).toString()" @disabled(! $humanTurn || $removed) @if($removed) tabindex="-1" aria-hidden="true" @endif aria-label="Bâtonnet {{ $i + 1 }}" class="group flex h-full w-full items-end justify-center focus:outline-none disabled:cursor-default">
                            <span class="relative h-full w-3 overflow-hidden rounded-full border-2 border-ink bg-amber-400 shadow-[2px_2px_0_0_rgba(23,23,27,0.25)] transition duration-200 group-focus-visible:ring-2 group-focus-visible:ring-info group-focus-visible:ring-offset-2 sm:w-3.5" :class="selected.includes({{ $i }}) ? '-translate-y-3' : 'group-enabled:group-hover:-translate-y-1'">
                                <span class="absolute left-[2px] top-1 h-2/3 w-[3px] rounded-full bg-white/50"></span>
                                <span x-cloak x-show="selected.includes({{ $i }})" class="absolute inset-0 {{ $colors[$current]['bg'] }}"></span>
                            </span>
                        </button>
                    </span>
                    @endfor
                </div>
                <div class="h-3 rounded-b-xl border-2 border-ink bg-ink" aria-hidden="true"></div>
            </div>

            {{-- Actions --}}
            @if($game->isOver() && ! $resultRevealed)
            <div class="h-[52px]" aria-hidden="true"></div>
            @elseif($game->isOver())
            <div class="flex flex-col items-center gap-2">
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <button type="button" wire:click="rematch" class="card-hard rounded-xl border-2 border-ink bg-primary px-6 py-3 font-display text-sm text-white transition hover:-translate-x-px hover:-translate-y-px hover:shadow-hard-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                        🔁 REVANCHE
                    </button>

                    <button type="button" wire:click="restart" class="card-hard rounded-xl border-2 border-ink bg-panel px-6 py-3 font-display text-sm text-ink transition hover:-translate-x-px hover:-translate-y-px hover:shadow-hard-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                        ⚙ AUTRES RÉGLAGES
                    </button>
                </div>

                @unless($vsAi)
                <p class="font-mono text-[10px] uppercase tracking-widest text-subtle">
                    Revanche : {{ $this->playerLabel(3 - $localStarter) }} commence
                </p>
                @endunless
            </div>
            @else
            <div class="flex flex-col items-center gap-3">
                {{-- Retrait rapide : les N premiers bâtonnets restants --}}
                <div class="flex flex-wrap items-center justify-center gap-2" role="group" aria-label="Retrait rapide">
                    @foreach(range(\App\Domain\Sticks\Entities\SticksGame::MIN_TAKE, $maxTake) as $n)
                    <button type="button" wire:click="takeFirst({{ $n }})" @disabled(! $humanTurn || $n > $remaining) class="btn-press rounded-xl border-2 border-ink bg-secondary px-5 py-2.5 font-display text-sm text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-40">
                        − {{ $n }}
                    </button>
                    @endforeach
                </div>

                <p class="font-mono text-[10px] uppercase tracking-widest text-faint">ou cliquez sur les bâtonnets à retirer</p>

                <button type="button" x-on:click="confirm()" x-bind:disabled="selected.length === 0" @disabled(! $humanTurn) class="btn-press rounded-xl border-2 border-ink bg-primary px-6 py-3 font-display text-sm text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                    <span x-text="selected.length ? 'RETIRER ' + selected.length + ' BÂTONNET' + (selected.length > 1 ? 'S' : '') : 'CHOISISSEZ 1 À 3 BÂTONNETS'"></span>
                </button>

                <div class="flex items-center gap-4">
                    @if(! $vsAi && $moves !== [])
                    <button type="button" wire:click="undo" class="font-mono text-[10px] uppercase tracking-widest text-subtle underline transition hover:text-ink">
                        ↩ Annuler le dernier coup
                    </button>
                    @endif

                    <button type="button" wire:click="restart" wire:confirm="Abandonner la partie en cours ?" class="font-mono text-[10px] uppercase tracking-widest text-subtle underline transition hover:text-danger">
                        Abandonner
                    </button>
                </div>
            </div>
            @endif
        </section>
        @endif

        {{-- HISTORIQUE DE LA SESSION --}}
        @if(count($history))
        <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-display text-base text-ink">Historique de la session</h3>
                <div class="flex items-center gap-3">
                    <a href="{{ route('history') }}?filter=sticks" class="font-mono text-[10px] uppercase tracking-widest text-info hover:underline">
                        Historique complet →
                    </a>
                    <button type="button" wire:click="clearHistory" wire:confirm="Vider l'historique ?" class="font-mono text-[10px] uppercase tracking-widest text-subtle transition hover:text-danger">
                        Vider
                    </button>
                </div>
            </div>

            <div class="max-h-64 space-y-1.5 overflow-y-auto pr-1">
                @foreach(array_reverse($history) as $entry)
                @php
                $entryOpponent = \App\Domain\Sticks\Enums\SticksOpponentType::tryFrom($entry['opponent_type'] ?? '') ?? \App\Domain\Sticks\Enums\SticksOpponentType::LOCAL;
                $entryLevel = isset($entry['difficulty']) ? \App\Domain\Sticks\Enums\SticksDifficulty::tryFrom($entry['difficulty']) : null;
                @endphp

                <div class="flex items-center justify-between rounded-xl border-2 border-line bg-wash px-3 py-2 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-ink">
                            🏆 {{ $entry['winner_label'] ?? ($entry['winner'] === 1 ? $entryOpponent->p1Label() : $entryOpponent->p2Label()) }}
                        </span>

                        <span class="rounded bg-line px-1.5 py-0.5 font-mono text-[9px] uppercase text-muted">
                            {{ $entryLevel ? $entryLevel->label() : 'Local' }}
                        </span>

                        @if(($entry['variant'] ?? 'last_loses') === 'last_wins')
                        <span class="rounded bg-secondary/60 px-1.5 py-0.5 font-mono text-[9px] uppercase text-ink">Dernier gagne</span>
                        @endif
                    </div>

                    <span class="font-mono text-[11px] text-subtle">
                        {{ $entry['stick_count'] }} bâtonnets · {{ $entry['moves_count'] }} coups
                    </span>
                </div>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</div>
