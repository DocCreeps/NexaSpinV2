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
@endphp

<div class="card-hard flex items-center gap-2 rounded-lg border-2 border-ink bg-panel px-2.5 py-2 text-xs shadow-hard" title="{{ $label }}">
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
        <span class="{{ $winner && $a && $winner->equals($a) ? 'text-ink' : 'text-subtle' }}">{{ $savedResult['score_a'] ?? '–' }}</span>
        <span class="text-faint">:</span>
        <span class="{{ $winner && $b && $winner->equals($b) ? 'text-ink' : 'text-subtle' }}">{{ $savedResult['score_b'] ?? '–' }}</span>
        @else
        <span class="text-faint">– : –</span>
        @endif
    </div>

    <span class="min-w-0 flex-1 truncate text-right font-bold {{ $winner && $b && $winner->equals($b) ? 'text-ink' : ($b ? 'text-ink' : 'text-faint') }}">
        {{ $b?->name ?? ($isBye ? 'BYE' : 'À déterminer') }}{{ $winner && $b && $winner->equals($b) ? ' 🏆' : '' }}
    </span>
</div>
