<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink"
    x-data="{
        spinning: false,
        revealed: null,
        finished: false,
        cycleTimer: null
    }"
    x-on:roulette-spin.window="
        spinning = true;
        finished = false;
        revealed = null;
        const pockets = ['0', '00', ...Array.from({ length: 36 }, (_, i) => String(i + 1))];
        clearInterval(cycleTimer);
        cycleTimer = setInterval(() => { revealed = pockets[Math.floor(Math.random() * pockets.length)]; }, 70);
        setTimeout(() => {
            clearInterval(cycleTimer);
            revealed = $event.detail.result;
            spinning = false;
            finished = true;
            setTimeout(() => $wire.confirmSpin(), 500);
        }, 1500);
    ">
    <div class="mx-auto max-w-6xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">

        {{-- HEADER --}}
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">
                    ← Salle
                </a>
                <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">◆ Roulette américaine ◆</p>
                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">Roulette numérique</h1>
            </div>

            <div class="card-hard self-start rounded-xl border-2 border-ink bg-panel px-5 py-3 text-center min-w-[140px]">
                <p class="font-mono text-[9px] uppercase tracking-widest text-subtle">Cagnotte</p>
                <p class="mt-0.5 font-display text-2xl text-ink">{{ number_format($bankroll, 0, ',', ' ') }}</p>
                <button type="button" wire:click="resetBankroll" wire:confirm="Réinitialiser la cagnotte ?" @disabled($spinning) class="mt-1 font-mono text-[9px] uppercase tracking-widest text-subtle underline hover:text-danger disabled:opacity-50">
                    Réinitialiser
                </button>
            </div>
        </header>

        @if($error)
        <div role="alert" class="rounded-xl border-2 border-ink bg-danger/10 px-4 py-3 text-sm font-semibold text-danger">
            ⚠ {{ $error }}
        </div>
        @endif

        <div class="grid items-start gap-6 lg:grid-cols-12 lg:gap-8">
            {{-- TABLE DE JEU --}}
            <div class="space-y-6 lg:col-span-7">
                <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5 sm:p-6">
                    {{-- RÉSULTAT --}}
                    <div class="mb-6 flex flex-col items-center justify-center gap-3">
                        <div class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-ink font-display text-3xl shadow-hard transition-all"
                            x-bind:class="(spinning ? 'animate-pulse ' : '') + (revealed === null ? 'bg-wash text-faint' : (revealed === '0' || revealed === '00' ? 'bg-emerald-600 text-white' : ({{ json_encode(collect(\App\Domain\Roulette\RoulettePocket::all())->mapWithKeys(fn($p) => [$p => \App\Domain\Roulette\RoulettePocket::color($p)])) }}[revealed] === 'red' ? 'bg-red-600 text-white' : 'bg-ink text-white')))"
                            x-text="revealed ?? '?'">
                        </div>

                        <div x-show="finished && !spinning" x-cloak :class="{{ $lastWin ? "'bg-secondary text-ink'" : "'bg-danger/10 text-danger'" }}" class="rounded-xl border-2 border-ink px-5 py-2 font-display text-base shadow-hard">
                            @if($lastWin !== null)
                                {{ $lastWin ? '✓ Gain net' : '✗ Perdu' }}
                                @if($lastPayout !== null)
                                    ({{ $lastPayout > 0 ? '+' : '' }}{{ number_format($lastPayout, 0, ',', ' ') }})
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- 0 / 00 --}}
                    <div class="mb-1.5 flex gap-1.5">
                        @foreach(['0', '00'] as $zero)
                        <button type="button" wire:click="selectNumber('{{ $zero }}')" @disabled($spinning) @class([
                            'flex-1 rounded-lg border-2 border-ink py-2.5 font-mono text-sm font-bold transition',
                            'ring-2 ring-info ring-offset-1 bg-emerald-600 text-white shadow-hard' => $selectedBetType === 'straight' && $selectedBetNumber === $zero,
                            'bg-emerald-600/80 text-white hover:bg-emerald-600' => ! ($selectedBetType === 'straight' && $selectedBetNumber === $zero),
                        ])>
                            {{ $zero }}
                        </button>
                        @endforeach
                    </div>

                    {{-- GRILLE 1-36 --}}
                    <div class="space-y-1.5">
                        @for($row = 3; $row >= 1; $row--)
                        <div class="grid grid-cols-12 gap-1.5">
                            @for($col = 1; $col <= 12; $col++)
                            @php
                                $number = (string) (($col - 1) * 3 + $row);
                                $color = \App\Domain\Roulette\RoulettePocket::color($number);
                                $selected = $selectedBetType === 'straight' && $selectedBetNumber === $number;
                            @endphp
                            <button type="button" wire:click="selectNumber('{{ $number }}')" @disabled($spinning) @class([
                                'rounded-md border-2 border-ink py-2 font-mono text-[11px] font-bold transition sm:text-xs',
                                'ring-2 ring-info ring-offset-1' => $selected,
                                'bg-red-600 text-white hover:bg-red-500' => $color === 'red',
                                'bg-ink text-white hover:bg-ink/80' => $color === 'black',
                            ])>
                                {{ $number }}
                            </button>
                            @endfor
                        </div>
                        @endfor
                    </div>

                    {{-- CHANCES SIMPLES --}}
                    <div class="mt-3 grid grid-cols-3 gap-1.5 sm:grid-cols-6">
                        @foreach($this->simpleChances() as $bet)
                        <button type="button" wire:click="selectBetType('{{ $bet->value }}')" @disabled($spinning) @class([
                            'rounded-lg border-2 border-ink px-2 py-2 font-mono text-[10px] uppercase tracking-wide transition',
                            'bg-ink text-white shadow-hard' => $selectedBetType === $bet->value,
                            'bg-wash text-muted hover:text-ink' => $selectedBetType !== $bet->value,
                        ])>
                            {{ $bet->label() }}
                        </button>
                        @endforeach
                    </div>

                    {{-- DOUZAINES --}}
                    <div class="mt-1.5 grid grid-cols-3 gap-1.5">
                        @foreach($this->dozenBets() as $bet)
                        <button type="button" wire:click="selectBetType('{{ $bet->value }}')" @disabled($spinning) @class([
                            'rounded-lg border-2 border-ink px-2 py-2 font-mono text-[10px] uppercase tracking-wide transition',
                            'bg-ink text-white shadow-hard' => $selectedBetType === $bet->value,
                            'bg-wash text-muted hover:text-ink' => $selectedBetType !== $bet->value,
                        ])>
                            {{ $bet->label() }}
                        </button>
                        @endforeach
                    </div>

                    {{-- COLONNES --}}
                    <div class="mt-1.5 grid grid-cols-3 gap-1.5">
                        @foreach($this->columnBets() as $bet)
                        <button type="button" wire:click="selectBetType('{{ $bet->value }}')" @disabled($spinning) @class([
                            'rounded-lg border-2 border-ink px-2 py-2 font-mono text-[10px] uppercase tracking-wide transition',
                            'bg-ink text-white shadow-hard' => $selectedBetType === $bet->value,
                            'bg-wash text-muted hover:text-ink' => $selectedBetType !== $bet->value,
                        ])>
                            {{ $bet->label() }}
                        </button>
                        @endforeach
                    </div>

                    {{-- TOP LINE (0 - 00 - 1 - 2 - 3) --}}
                    <div class="mt-1.5">
                        <button type="button" wire:click="selectBetType('top_line')" @disabled($spinning) @class([
                            'w-full rounded-lg border-2 border-ink px-2 py-2 font-mono text-[10px] uppercase tracking-wide transition',
                            'bg-ink text-white shadow-hard' => $selectedBetType === 'top_line',
                            'bg-wash text-muted hover:text-ink' => $selectedBetType !== 'top_line',
                        ])>
                            Top Line (0 - 00 - 1 - 2 - 3) [Cote 6:1]
                        </button>
                    </div>
                </section>
            </div>

            {{-- GESTION DES PARIS ET SOUMISSION --}}
            <div class="space-y-6 lg:col-span-5">
                <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
                    <h2 class="mb-3 font-display text-lg text-ink">Placer une mise</h2>

                    <div class="mb-4 rounded-xl border-2 border-ink bg-wash px-4 py-3">
                        <p class="font-mono text-[10px] uppercase tracking-widest text-subtle">Choix actuel</p>
                        <p class="mt-0.5 font-display text-base text-ink">
                            {{ \App\Domain\Roulette\Enums\RouletteBetType::from($selectedBetType)->label() }}
                            @if($selectedBetType === 'straight' && $selectedBetNumber !== null)
                                <span class="font-mono text-sm text-subtle">— {{ $selectedBetNumber }}</span>
                            @endif
                        </p>
                    </div>

                    {{-- MONTANT STAKE --}}
                    <label for="stake-input" class="mb-1.5 block font-mono text-[10px] uppercase tracking-widest text-subtle">
                        Montant de ce pari
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="$set('stake', {{ max($stake - 10, 1) }})" @disabled($spinning) class="h-10 w-10 shrink-0 rounded-lg border-2 border-ink bg-wash font-bold text-ink transition hover:bg-panel disabled:opacity-40">−</button>
                        <input type="number" id="stake-input" wire:model="stake" min="1" max="{{ $bankroll }}" @disabled($spinning) class="w-full rounded-xl border-2 border-ink bg-panel px-4 py-2.5 text-center text-sm font-bold text-ink focus:outline-none focus:ring-2 focus:ring-info focus:ring-offset-2 [appearance:textfield]">
                        <button type="button" wire:click="$set('stake', {{ min($stake + 10, max($bankroll, 1)) }})" @disabled($spinning) class="h-10 w-10 shrink-0 rounded-lg border-2 border-ink bg-wash font-bold text-ink transition hover:bg-panel disabled:opacity-40">+</button>
                    </div>

                    <div class="mt-2 flex gap-1.5">
                        @foreach([25, 50, 100, 250] as $amount)
                        <button type="button" wire:click="$set('stake', {{ $amount }})" @disabled($spinning) class="flex-1 rounded-md border-2 border-ink bg-wash py-1.5 font-mono text-[10px] text-muted transition hover:text-ink disabled:opacity-40">
                            {{ $amount }}
                        </button>
                        @endforeach
                    </div>

                    <button type="button" wire:click="addBet" @disabled($spinning || $stake > $bankroll) class="mt-4 w-full rounded-xl border-2 border-ink bg-wash py-2.5 font-display text-xs uppercase tracking-wider text-ink transition hover:bg-panel disabled:opacity-40">
                        + Engager ce pari (-{{ $stake }})
                    </button>
                </section>

                {{-- LISTE DES PARIS ENGAGÉS --}}
                <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-display text-base text-ink">Paris engagés ({{ count($bets) }})</h3>
                        @if(count($bets))
                        <button type="button" wire:click="clearBets" @disabled($spinning) class="font-mono text-[10px] uppercase text-subtle hover:text-danger disabled:opacity-50">Tout annuler</button>
                        @endif
                    </div>

                    @if(count($bets))
                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        @foreach($bets as $index => $bet)
                        <div class="flex items-center justify-between rounded-lg border-2 border-ink bg-wash px-3 py-2 text-xs">
                            <div>
                                <span class="font-semibold text-ink">
                                    {{ \App\Domain\Roulette\Enums\RouletteBetType::from($bet['bet_type'])->label() }}
                                </span>
                                @if($bet['bet_number'] !== null)
                                <span class="font-mono text-subtle">({{ $bet['bet_number'] }})</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-mono font-bold text-ink">{{ $bet['stake'] }}</span>
                                <button type="button" wire:click="removeBet({{ $index }})" @disabled($spinning) class="text-danger hover:underline disabled:opacity-50" title="Annuler ce pari">✕</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-3 flex justify-between border-t border-ink/20 pt-2 font-mono text-xs font-bold text-ink">
                        <span>Total sur la table :</span>
                        <span>{{ $this->totalStake }}</span>
                    </div>
                    @else
                    <p class="py-4 text-center font-mono text-xs text-muted">Aucun pari engagé pour le moment.</p>
                    @endif
                </section>

                {{-- BOUTON DE LANCER --}}
                <button type="button" wire:click="spin" wire:loading.attr="disabled" wire:target="spin" x-bind:disabled="spinning || {{ $this->canSpin() ? 'false' : 'true' }}" @disabled(! $this->canSpin())
                    class="btn-press w-full rounded-xl border-2 border-ink bg-primary py-4 font-display text-sm text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                    <span x-show="!spinning">
                        ▶ LANCER LA ROULETTE ({{ count($bets) }} {{ Str::plural('pari', count($bets)) }})
                    </span>
                    <span x-show="spinning" x-cloak>LA BILLE TOURNE...</span>
                </button>
            </div>
        </div>

        {{-- HISTORIQUE DES TIRAGES --}}
        <section class="card-hard rounded-2xl border-2 border-ink bg-panel p-5 sm:p-6">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="font-display text-lg text-ink">Derniers tirages</h3>
                    <p class="font-mono text-[10px] uppercase tracking-widest text-subtle">Historique des parties</p>
                </div>
                @if(count($history))
                <button type="button" wire:click="clearHistory" wire:confirm="Effacer l'historique ?" @disabled($spinning) class="font-mono text-[10px] uppercase text-subtle hover:text-danger disabled:opacity-50">
                    Effacer
                </button>
                @endif
            </div>

            @if(count($history))
            <div class="flex flex-wrap gap-2 overflow-x-auto pb-2">
                @foreach(array_reverse($history) as $entry)
                <div @class([
                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border-2 border-ink font-mono text-xs font-bold text-white shadow-hard',
                    'bg-emerald-600' => in_array($entry['result'], ['0', '00'], true),
                    'bg-red-600' => $entry['color'] === 'red',
                    'bg-ink' => $entry['color'] === 'black',
                ]) title="Mise : {{ $entry['total_stake'] }} | Gain net : {{ $entry['payout'] }}">
                    {{ $entry['result'] }}
                </div>
                @endforeach
            </div>
            @else
            <p class="py-4 text-center font-mono text-xs text-muted">Aucun tirage enregistré.</p>
            @endif
        </section>

    </div>
</div>
