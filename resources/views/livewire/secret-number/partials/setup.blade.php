@php
    $selectedMode = \App\Domain\SecretNumber\Enums\SecretNumberMode::tryFrom($gameMode);
    $selectedIsDuel = $selectedMode === \App\Domain\SecretNumber\Enums\SecretNumberMode::DUEL;
    $selectedHasNames = $selectedMode !== \App\Domain\SecretNumber\Enums\SecretNumberMode::SOLO;
    $namedPlayers = $selectedIsDuel
        ? min(max($playerCount, \App\Domain\SecretNumber\ValueObjects\SecretNumberPlayers::MIN_PLAYERS), \App\Domain\SecretNumber\ValueObjects\SecretNumberPlayers::MAX_PLAYERS)
        : \App\Domain\SecretNumber\ValueObjects\SecretNumberPlayers::MIN_PLAYERS;
@endphp
<section class="card-hard space-y-5 rounded-2xl border-2 border-ink bg-panel p-5 sm:p-7">
    <div>
        <h2 class="font-display text-xl text-ink">Choisissez votre mode</h2>
        <div class="mt-3 grid gap-2 sm:grid-cols-3">
            @foreach($modes as $mode)
            <button type="button" wire:click="setGameMode('{{ $mode->value }}')" @class(['rounded-xl border-2 px-4 py-3 text-left transition', 'border-primary bg-primary/10 text-ink' => $gameMode === $mode->value, 'border-line bg-wash text-muted hover:border-ink' => $gameMode !== $mode->value])>
                <span class="block font-display text-base">{{ $mode->label() }}</span>
                <span class="mt-1 block text-xs leading-relaxed">{{ $mode->description() }}</span>
            </button>
            @endforeach
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <label class="text-sm font-semibold text-ink">Nombre minimum
            <input type="number" wire:model="minNumber" min="0" max="1000000" class="mt-1 w-full rounded-xl border-2 border-ink bg-wash px-3 py-2.5 text-center font-mono text-base focus:outline-none focus:ring-2 focus:ring-info">
        </label>
        <label class="text-sm font-semibold text-ink">Nombre maximum
            <input type="number" wire:model="maxNumber" min="1" max="1000000" class="mt-1 w-full rounded-xl border-2 border-ink bg-wash px-3 py-2.5 text-center font-mono text-base focus:outline-none focus:ring-2 focus:ring-info">
        </label>
        <label class="text-sm font-semibold text-ink">Limite d’essais
            <input type="number" wire:model="maxAttempts" min="0" max="1000" class="mt-1 w-full rounded-xl border-2 border-ink bg-wash px-3 py-2.5 text-center font-mono text-base focus:outline-none focus:ring-2 focus:ring-info">
            <span class="mt-1 block text-xs font-normal text-muted">0 = sans limite</span>
        </label>
    </div>

    <div>
        <p class="mb-2 text-sm font-semibold text-ink">Plages rapides</p>
        <div class="flex flex-wrap gap-2">
            @foreach($rangePresets as $preset => $definition)
            @php $isCurrentPreset = $minNumber === $definition['minimum'] && $maxNumber === $definition['maximum']; @endphp
            <button type="button" wire:click="setRangePreset('{{ $preset }}')" @class(['rounded-lg border-2 px-3 py-1.5 font-mono text-[10px] transition', 'border-primary bg-primary/10 text-ink' => $isCurrentPreset, 'border-line bg-wash text-muted hover:border-ink' => ! $isCurrentPreset])>{{ $definition['label'] }}</button>
            @endforeach
        </div>
        <p class="mt-1.5 text-xs text-muted">Vous pouvez aussi saisir vos propres bornes ci-dessus.</p>
    </div>

    @if($selectedIsDuel)
    <label class="block max-w-xs text-sm font-semibold text-ink">Nombre de joueurs
        <input type="number" wire:model.live="playerCount" min="2" max="10" class="mt-1 w-full rounded-xl border-2 border-ink bg-wash px-3 py-2.5 text-center font-mono text-base focus:outline-none focus:ring-2 focus:ring-info">
        <span class="mt-1 block text-xs font-normal text-muted">De 2 à 10 joueurs. 2 par défaut.</span>
    </label>
    @endif

    @if($selectedHasNames)
    <div>
        <p class="mb-2 text-sm font-semibold text-ink">Noms des joueurs</p>
        <div class="grid gap-3 sm:grid-cols-2">
            @for($player = 1; $player <= $namedPlayers; $player++)
            <label class="text-xs font-semibold text-muted">Joueur {{ $player }}
                <input type="text" name="playerNames[{{ $player }}]" wire:model.blur="playerNames.{{ $player }}" maxlength="24" placeholder="Joueur {{ $player }}" aria-label="Nom du joueur {{ $player }}" class="mt-1 w-full rounded-xl border-2 border-ink bg-wash px-3 py-2.5 text-sm font-semibold text-ink focus:outline-none focus:ring-2 focus:ring-info">
            </label>
            @endfor
        </div>
    </div>
    @endif

    <button type="button" wire:click="startGame" class="card-hard w-full rounded-xl border-2 border-ink bg-primary px-6 py-3 font-display text-sm text-white transition hover:-translate-x-px hover:-translate-y-px hover:shadow-hard-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">COMMENCER</button>
</section>
