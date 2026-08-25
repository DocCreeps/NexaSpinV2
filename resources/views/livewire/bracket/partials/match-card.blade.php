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
@endphp

<div class="card-hard relative overflow-hidden rounded-xl border-2 border-ink bg-panel transition-all hover:scale-[1.02] hover:shadow-md">
    <div class="flex items-center justify-between border-b border-ink/10 bg-wash px-3 py-1 font-mono text-[9px]">
        <span class="font-bold text-subtle">{{ $label }}</span>
        @if($isBye)
        <span class="rounded bg-faint/10 px-1.5 font-semibold text-faint uppercase">BYE</span>
        @elseif($isResolved)
        <span class="font-bold text-success uppercase">✓ Terminé</span>
        @elseif($isPlayable)
        <span class="animate-pulse font-bold text-info uppercase">● En cours</span>
        @else
        <span class="text-faint uppercase">En attente</span>
        @endif
    </div>

    <div class="space-y-2 p-2.5">
        {{-- Participant A --}}
        <div class="flex items-center justify-between gap-2 rounded-lg border-2 px-2.5 py-1.5 text-xs font-bold transition-all {{ $winner && $a && $winner->equals($a) ? 'border-ink bg-secondary text-ink shadow-sm' : ($a ? 'border-line bg-wash text-ink' : 'border-dashed border-line text-faint') }}">
            <span class="truncate">{{ $a?->name ?? 'À déterminer' }}</span>
            <div class="flex shrink-0 items-center gap-1.5">
                @if($isPlayable && ! $isResolved)
                <input type="number" min="0" wire:model.defer="scores.{{ $prefix }}_a" class="h-7 w-10 rounded-md border border-ink bg-panel text-center font-mono text-xs font-bold text-ink focus:outline-none focus:ring-2 focus:ring-info" placeholder="0">
                <button type="button" wire:click="recordResult('{{ $section }}', {{ $round === null ? 'null' : $round }}, {{ $position === null ? 'null' : $position }}, '{{ addslashes($a?->name ?? '') }}')" title="Déclarer gagnant directement" class="btn-press rounded border border-ink bg-panel px-1.5 py-0.5 font-mono text-[9px] uppercase text-subtle hover:bg-ink hover:text-white">
                    Gagne
                </button>
                @elseif($isResolved && isset($savedResult['score_a']))
                <span class="rounded border border-ink/20 bg-panel px-2 py-0.5 font-mono text-xs font-black">{{ $savedResult['score_a'] }}</span>
                @elseif($winner && $a && $winner->equals($a))
                <span class="text-sm">🏆</span>
                @endif
            </div>
        </div>

        {{-- Participant B --}}
        <div class="flex items-center justify-between gap-2 rounded-lg border-2 px-2.5 py-1.5 text-xs font-bold transition-all {{ $winner && $b && $winner->equals($b) ? 'border-ink bg-secondary text-ink shadow-sm' : ($b ? 'border-line bg-wash text-ink' : 'border-dashed border-line text-faint') }}">
            <span class="truncate">{{ $b?->name ?? 'À déterminer' }}</span>
            <div class="flex shrink-0 items-center gap-1.5">
                @if($isPlayable && ! $isResolved)
                <input type="number" min="0" wire:model.defer="scores.{{ $prefix }}_b" class="h-7 w-10 rounded-md border border-ink bg-panel text-center font-mono text-xs font-bold text-ink focus:outline-none focus:ring-2 focus:ring-info" placeholder="0">
                <button type="button" wire:click="recordResult('{{ $section }}', {{ $round === null ? 'null' : $round }}, {{ $position === null ? 'null' : $position }}, '{{ addslashes($b?->name ?? '') }}')" title="Déclarer gagnant directement" class="btn-press rounded border border-ink bg-panel px-1.5 py-0.5 font-mono text-[9px] uppercase text-subtle hover:bg-ink hover:text-white">
                    Gagne
                </button>
                @elseif($isResolved && isset($savedResult['score_b']))
                <span class="rounded border border-ink/20 bg-panel px-2 py-0.5 font-mono text-xs font-black">{{ $savedResult['score_b'] }}</span>
                @elseif($winner && $b && $winner->equals($b))
                <span class="text-sm">🏆</span>
                @endif
            </div>
        </div>

        @if($isPlayable && ! $isResolved)
        <div class="pt-1">
            <button type="button" wire:click="recordResult('{{ $section }}', {{ $round === null ? 'null' : $round }}, {{ $position === null ? 'null' : $position }})" class="btn-press w-full rounded-lg border-2 border-ink bg-primary py-1.5 font-mono text-[10px] font-bold uppercase tracking-wider text-white hover:bg-info">
                ✓ Valider le score
            </button>
        </div>
        @endif
    </div>
</div>
