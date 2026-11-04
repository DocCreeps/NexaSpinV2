<section x-data="{ expanded: false }" class="card-hard overflow-hidden rounded-2xl border-2 border-ink bg-panel">
    <div class="flex items-center justify-between gap-3 px-5 py-4">
        <button type="button" x-on:click="expanded = !expanded" class="flex min-w-0 flex-1 items-center justify-between gap-3 text-left">
            <span>
                <span class="block font-display text-lg">Historique des parties</span>
                <span class="mt-0.5 block font-mono text-[9px] uppercase tracking-widest text-faint">{{ count($history) }} {{ \Illuminate\Support\Str::plural('partie', count($history)) }}</span>
            </span>
            <span class="text-xs text-subtle" x-text="expanded ? '▲' : '▼'"></span>
        </button>
        @if(count($history))
        <button type="button" wire:click="clearHistory" wire:confirm="Vider l’historique du nombre secret ?" class="shrink-0 font-mono text-[9px] uppercase tracking-widest text-subtle hover:text-danger">Vider</button>
        @endif
    </div>
    <div x-show="expanded" x-cloak x-transition.opacity.duration.150ms class="border-t-2 border-dashed border-line px-4 py-4">
        @if(count($history))
        <div class="custom-scrollbar max-h-[560px] space-y-2 overflow-y-auto pr-1">
            @foreach($history as $entry)
            @php
                $entryMode = \App\Domain\SecretNumber\Enums\SecretNumberMode::tryFrom($entry['game_mode'] ?? 'solo') ?? \App\Domain\SecretNumber\Enums\SecretNumberMode::SOLO;
                $entryIsDuel = $entryMode === \App\Domain\SecretNumber\Enums\SecretNumberMode::DUEL;
                $entryIsVersus = $entryMode === \App\Domain\SecretNumber\Enums\SecretNumberMode::VERSUS;
                $entryName = fn (int $player): string => $entryNames[$player] ?? \App\Domain\SecretNumber\ValueObjects\SecretNumberPlayers::defaultName($player);
                $entryAttempts = $entry['attempts'] ?? [1 => 0, 2 => 0];
                $entryGuesses = $entry['guesses'] ?? [1 => [], 2 => []];
                $entryVersusResults = $entry['versus_results'] ?? [];
                $entryRankings = $entry['rankings'] ?? [];
                $entryPlayerCount = $entry['player_count'] ?? 2;
                $entryNames = $entry['player_names'] ?? [];
                $entrySecret = $entry['secret'] ?? '?';
            @endphp
            <details class="group rounded-xl border-2 border-line bg-wash">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-3 py-2.5">
                    <span class="min-w-0">
                <span class="block truncate font-display text-sm">{{ $entryMode->label() }}@if($entryIsVersus) · 2 manches@else · nombre {{ $entrySecret }}@endif</span>
                        <span class="mt-0.5 block font-mono text-[9px] uppercase tracking-widest text-faint">{{ \Illuminate\Support\Carbon::parse($entry['recorded_at'])->format('d/m/Y H:i') }}</span>
                    </span>
                    <span class="shrink-0 text-[10px] text-subtle group-open:rotate-180">⌄</span>
                </summary>
                <div class="space-y-2 border-t border-line px-3 py-3 text-xs">
                    <p class="font-semibold text-ink">
                        @if($entry['winner'] ?? null)Victoire de {{ $entryName($entry['winner']) }}
                        @elseif($entryMode === \App\Domain\SecretNumber\Enums\SecretNumberMode::SOLO)Le nombre n’a pas été trouvé
                        @elseif(($entryIsDuel || $entryIsVersus) && !($entry['any_player_found'] ?? collect($entryVersusResults)->contains('found', true)))Personne n’a trouvé le nombre
                        @else Égalité
                        @endif
                        @if($entryIsDuel)· classement à {{ $entryPlayerCount }} joueurs
                        @elseif($entryIsVersus)· {{ $entryName(1) }} : {{ $entryVersusResults[1]['attempts'] ?? 0 }} · {{ $entryName(2) }} : {{ $entryVersusResults[0]['attempts'] ?? 0 }} essais
                        @else· {{ $entryAttempts[1] ?? 0 }} {{ \Illuminate\Support\Str::plural('essai', $entryAttempts[1] ?? 0) }}
                        @endif
                    </p>
                    @if($entryIsDuel)
                    <div class="space-y-1.5">
                        @foreach($entryRankings as $ranking)
                        <div class="flex items-center justify-between rounded-lg border border-line bg-panel px-2.5 py-1.5">
                            <span class="font-semibold">#{{ $ranking['rank'] }} · {{ $ranking['name'] ?? $entryName($ranking['player']) }}</span>
                            <span class="font-mono text-[10px] text-subtle">{{ $ranking['attempts'] }} {{ \Illuminate\Support\Str::plural('essai', $ranking['attempts']) }}</span>
                        </div>
                        @endforeach
                    </div>
                    @elseif($entryIsVersus)
                        @foreach($entryVersusResults as $result)
                        <div>
                            <p class="mb-1 font-mono text-[9px] uppercase tracking-widest text-faint">{{ $result['name'] ?? $entryName($result['player']) }} · {{ $result['attempts'] }} {{ \Illuminate\Support\Str::plural('essai', $result['attempts']) }} · nombre {{ $result['secret'] }}</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($result['guesses'] as $guessEntry)
                                <span @class(['rounded-md border px-2 py-1 font-mono text-[10px]', 'border-ink bg-secondary/40 text-ink' => $guessEntry['hint'] === 'trouvé', 'border-line bg-panel text-subtle' => $guessEntry['hint'] !== 'trouvé'])>{{ $guessEntry['number'] }} · {{ $guessEntry['hint'] }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    @elseif(count($entryGuesses[1] ?? []))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($entryGuesses[1] as $guessEntry)
                            <span @class(['rounded-md border px-2 py-1 font-mono text-[10px]', 'border-ink bg-secondary/40 text-ink' => $guessEntry['hint'] === 'trouvé', 'border-line bg-panel text-subtle' => $guessEntry['hint'] !== 'trouvé'])>{{ $guessEntry['number'] }} · {{ $guessEntry['hint'] }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </details>
            @endforeach
        </div>
        @else
        <div class="rounded-xl border-2 border-dashed border-line py-8 text-center">
            <p class="mb-1 text-2xl">🗂️</p>
            <p class="text-sm font-medium text-muted">Aucune partie terminée</p>
            <p class="mt-0.5 text-xs text-faint">Vos récapitulatifs apparaîtront ici.</p>
        </div>
        @endif
    </div>
</section>
