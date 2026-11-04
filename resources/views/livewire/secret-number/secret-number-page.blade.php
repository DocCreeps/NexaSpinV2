@php
    $phase = \App\Domain\SecretNumber\Enums\SecretNumberPhase::tryFrom((string) ($gameState['phase'] ?? '')) ?? \App\Domain\SecretNumber\Enums\SecretNumberPhase::SETUP;
    $activeMode = $gameState['mode'] ?? $gameMode;
    $isSetup = $phase === \App\Domain\SecretNumber\Enums\SecretNumberPhase::SETUP;
    $isChooseSecret = $phase === \App\Domain\SecretNumber\Enums\SecretNumberPhase::CHOOSE_SECRET;
    $isHandoff = $phase === \App\Domain\SecretNumber\Enums\SecretNumberPhase::HANDOFF;
    $isPlaying = $phase === \App\Domain\SecretNumber\Enums\SecretNumberPhase::PLAYING;
    $isFinished = $phase === \App\Domain\SecretNumber\Enums\SecretNumberPhase::FINISHED;
    $isSolo = $activeMode === \App\Domain\SecretNumber\Enums\SecretNumberMode::SOLO->value;
    $isDuel = $activeMode === \App\Domain\SecretNumber\Enums\SecretNumberMode::DUEL->value;
    $isVersus = $activeMode === \App\Domain\SecretNumber\Enums\SecretNumberMode::VERSUS->value;
    $attempts = $gameState['attempts'] ?? [1 => 0, 2 => 0];
    $guesses = $gameState['guesses'] ?? [1 => [], 2 => []];
    $rankings = $gameState['rankings'] ?? [];
    $versusResults = $gameState['versus_results'] ?? [];
    $playerCount = $gameState['player_count'] ?? 2;
    $displayNames = $gameState['player_names'] ?? $playerNames;
    $currentPlayer = $gameState['current_player'] ?? 1;
    $winner = $gameState['winner'] ?? null;
    $anyPlayerFound = $gameState['any_player_found'] ?? false;
    $feedback = $gameState['feedback'] ?? null;
    $revealedNumber = $gameState['revealed_number'] ?? null;
    $activeMinimum = $gameState['minimum'] ?? $minNumber;
    $activeMaximum = $gameState['maximum'] ?? $maxNumber;
    $activeMaxAttempts = $gameState['max_attempts'] ?? $maxAttempts;
    $secretSetter = $gameState['secret_setter'] ?? 1;
    $guesser = $isVersus ? 3 - $secretSetter : $currentPlayer;
    $isChooseSecretHandoff = ($gameState['handoff_purpose'] ?? '') === \App\Domain\SecretNumber\Enums\SecretNumberHandoffPurpose::CHOOSE_SECRET->value;
    $name = fn (int $player): string => $displayNames[$player] ?? \App\Domain\SecretNumber\ValueObjects\SecretNumberPlayers::defaultName($player);
    $modeLabel = fn (?string $mode): string => \App\Domain\SecretNumber\Enums\SecretNumberMode::tryFrom((string) $mode)?->label() ?? 'Partie';
@endphp
<div class="min-h-screen w-full bg-surface text-ink antialiased selection:bg-secondary selection:text-ink">
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-10 sm:py-10">
        <header class="flex flex-col gap-4 border-b-4 border-ink pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded text-sm font-semibold text-muted transition-colors hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-info focus-visible:ring-offset-2">← Salle</a>
                <p class="mt-4 font-mono text-[10px] uppercase tracking-widest text-faint">◆ Plus ou moins ◆</p>
                <h1 class="mt-1 font-display text-4xl leading-none text-ink sm:text-5xl">Nombre secret</h1>
                <p class="mt-3 max-w-md text-sm text-muted">Devinez le nombre secret grâce aux indices « plus » et « moins ». Jouez contre l’IA ou défiez d’autres joueurs.</p>
            </div>
            <div class="card-hard min-w-[110px] self-start rounded-xl border-2 border-ink bg-panel px-5 py-3 text-center">
                <p class="font-mono text-[9px] uppercase tracking-widest text-subtle">Parties jouées</p>
                <p class="mt-0.5 font-display text-2xl text-ink">{{ count($history) }}</p>
            </div>
        </header>

        @if(! $isSetup)
        <button type="button" wire:click="returnToModeSelection" class="inline-flex items-center gap-2 rounded-xl border-2 border-ink bg-panel px-4 py-2.5 font-mono text-[10px] uppercase tracking-widest text-muted transition hover:bg-wash hover:text-ink">← Choix des modes</button>
        @endif

        @if($error)
        <div role="alert" class="rounded-xl border-2 border-ink bg-danger/10 px-4 py-3 text-sm font-semibold text-danger">⚠ {{ $error }}</div>
        @endif

        <div class="grid items-start gap-6 lg:grid-cols-12 lg:gap-8">
            <main class="space-y-6 lg:col-span-7">
                @if($isSetup) @include('livewire.secret-number.partials.setup') @endif
                @if($isChooseSecret) @include('livewire.secret-number.partials.choose-secret') @endif
                @if($isHandoff) @include('livewire.secret-number.partials.handoff') @endif
                @if($isPlaying) @include('livewire.secret-number.partials.playing') @endif
                @if($isFinished) @include('livewire.secret-number.partials.finished') @endif
            </main>

            <aside class="space-y-6 lg:col-span-5">
                @include('livewire.secret-number.partials.attempts-panel')
                @if($isDuel && ! $isSetup) @include('livewire.secret-number.partials.duel-ranking') @endif
                @include('livewire.secret-number.partials.history')
            </aside>
        </div>
    </div>
</div>
