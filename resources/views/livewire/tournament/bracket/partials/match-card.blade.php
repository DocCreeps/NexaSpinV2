@php
    $a = $match->participantA();
    $b = $match->participantB();
    $winner = $match->winner();
    $isResolved = $match->isResolved();
    $isPlayable = $match->isPlayable();
    $isBye = $match->isBye();

    $prefix = $round !== null ? "{$section}_{$round}_{$position}" : $section;
    $savedResult = collect($results)->firstWhere(
        fn($r) => $r['section'] === $section && $r['round'] === $round && $r['position'] === $position
    );
    $roundArg = $round === null ? 'null' : $round;
    $positionArg = $position === null ? 'null' : $position;

    // Un match "bye" n'a jamais été saisi manuellement (pas d'entrée dans les
    // résultats) : rien à éditer. Sinon, éditable seulement si son résultat
    // n'a pas encore influencé un match suivant (vainqueur propagé / perdant
    // repêché déjà résolu là-bas) — voir DoubleEliminationBracket::hasDownstreamResult().
    $isEditable = $savedResult !== null
        && ! $this->bracket->hasDownstreamResult($section, $round, $position);
@endphp

<div class="card-hard flex items-center gap-2 rounded-lg border-2 border-ink bg-panel px-2.5 py-2 text-xs shadow-hard" title="{{ $label }}" x-data="{ editing: false }">
    <span @class([
        'h-1.5 w-1.5 shrink-0 rounded-full',
        'bg-faint/40' => $isBye,
        'bg-success' => ! $isBye && $isResolved,
        'bg-info animate-pulse' => ! $isBye && ! $isResolved && $isPlayable,
        'bg-line' => ! $isBye && ! $isResolved && ! $isPlayable,
    ])></span>

    <span class="min-w-0 flex-1 truncate font-bold {{ $winner && $a && $winner->equals($a) ? 'text-ink' : ($a ? 'text-ink' : 'text-faint') }}">
        {{ $winner && $a && $winner->equals($a) ? '🏆 ' : '' }}{{ $a?->name ?? 'À déterminer' }}
    </span>

    <div class="flex shrink-0 items-center gap-1 font-mono text-[11px] font-bold">
        @if($isPlayable && ! $isResolved)
        <input type="number" min="0" wire:model.defer="scores.{{ $prefix }}_a" wire:change="autoRecordIfReady('{{ $section }}', {{ $roundArg }}, {{ $positionArg }})" class="h-7 w-9 rounded-md border border-ink bg-wash text-center focus:outline-none focus:ring-2 focus:ring-info" placeholder="–">
        <span class="text-faint">:</span>
        <input type="number" min="0" wire:model.defer="scores.{{ $prefix }}_b" wire:change="autoRecordIfReady('{{ $section }}', {{ $roundArg }}, {{ $positionArg }})" class="h-7 w-9 rounded-md border border-ink bg-wash text-center focus:outline-none focus:ring-2 focus:ring-info" placeholder="–">
        @elseif($isResolved)
            @if($isEditable)
            {{-- Ni l'affichage statique ni son édition ne font d'aller-retour
                 serveur pour basculer de l'un à l'autre : un clic accidentel
                 sur ✎ se corrige d'un clic sur ✕, sans rien avoir modifié. --}}
            <template x-if="! editing">
                <div class="flex items-center gap-1">
                    <span class="{{ $winner && $a && $winner->equals($a) ? 'text-ink' : 'text-subtle' }}">{{ $savedResult['score_a'] ?? '–' }}</span>
                    <span class="text-faint">:</span>
                    <span class="{{ $winner && $b && $winner->equals($b) ? 'text-ink' : 'text-subtle' }}">{{ $savedResult['score_b'] ?? '–' }}</span>
                    <button type="button" x-on:click="editing = true" title="Modifier le résultat" class="ml-1 rounded p-1 text-faint transition hover:bg-wash hover:text-ink">
                        ✎
                    </button>
                </div>
            </template>
            <template x-if="editing">
                <div class="flex items-center gap-1">
                    <input type="number" min="0" wire:model.defer="scores.{{ $prefix }}_a" wire:change="autoRecordIfReady('{{ $section }}', {{ $roundArg }}, {{ $positionArg }})" class="h-7 w-9 rounded-md border border-ink bg-wash text-center focus:outline-none focus:ring-2 focus:ring-info">
                    <span class="text-faint">:</span>
                    <input type="number" min="0" wire:model.defer="scores.{{ $prefix }}_b" wire:change="autoRecordIfReady('{{ $section }}', {{ $roundArg }}, {{ $positionArg }})" class="h-7 w-9 rounded-md border border-ink bg-wash text-center focus:outline-none focus:ring-2 focus:ring-info">
                    <button type="button" x-on:click="editing = false" title="Annuler" class="ml-1 rounded p-1 text-faint transition hover:bg-wash hover:text-ink">
                        ✕
                    </button>
                </div>
            </template>
            @else
            <span class="{{ $winner && $a && $winner->equals($a) ? 'text-ink' : 'text-subtle' }}">{{ $savedResult['score_a'] ?? '–' }}</span>
            <span class="text-faint">:</span>
            <span class="{{ $winner && $b && $winner->equals($b) ? 'text-ink' : 'text-subtle' }}">{{ $savedResult['score_b'] ?? '–' }}</span>
            @if($savedResult !== null)
            <span title="Déjà répercuté sur un match suivant : impossible à modifier tant que celui-ci n'est pas annulé" class="ml-1 cursor-not-allowed rounded p-1 text-faint/40">
                ✎
            </span>
            @endif
            @endif
        @else
        <span class="text-faint">– : –</span>
        @endif
    </div>

    <span class="min-w-0 flex-1 truncate text-right font-bold {{ $winner && $b && $winner->equals($b) ? 'text-ink' : ($b ? 'text-ink' : 'text-faint') }}">
        {{ $b?->name ?? ($isBye ? 'BYE' : 'À déterminer') }}{{ $winner && $b && $winner->equals($b) ? ' 🏆' : '' }}
    </span>
</div>
