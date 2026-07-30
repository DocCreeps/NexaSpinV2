@props([
'participants' => [],
'colors' => [],
'locked' => false,
'error' => null,
'weights' => null,
])

@php
$totalWeight = $weights !== null ? max(array_sum($weights), 1) : 1;
@endphp

<div {{ $attributes->class(['space-y-5']) }} x-data="{ editingIndex: null, editValue: '' }">

    {{-- AJOUT --}}
    @unless($locked)
    <form wire:submit.prevent="addParticipant" class="space-y-3">
        <div class="flex gap-2">
            <div class="relative flex-grow">
                <label for="participant-input" class="sr-only">Nom du nouveau participant</label>
                <input type="text" id="participant-input" wire:model="participant" placeholder="Nom du participant..." aria-invalid="{{ $error ? 'true' : 'false' }}" @if($error) aria-describedby="participant-error" @endif class="w-full rounded-xl border-2 border-ink bg-panel px-4 py-2.5 text-sm text-ink placeholder:text-faint focus:outline-none focus:ring-2 focus:ring-info focus:ring-offset-2">
            </div>

            <button type="submit" class="btn-press shrink-0 rounded-xl border-2 border-ink bg-primary px-5 py-2.5 text-sm font-display text-white focus:outline-none focus:ring-2 focus:ring-info focus:ring-offset-2">
                AJOUTER
            </button>
        </div>

        @if($error)
        <p id="participant-error" role="alert" class="flex items-center gap-1.5 px-1 text-xs font-semibold text-danger">
            <span aria-hidden="true">⚠</span> {{ $error }}
        </p>
        @endif
    </form>
    @endunless

    {{-- SÉPARATEUR --}}
    <div class="relative flex items-center py-1" aria-hidden="true">
        <div class="flex-grow border-t-2 border-line"></div>
        <span class="mx-4 shrink-0 font-mono text-[10px] font-bold uppercase tracking-wider text-faint">
            Membres ({{ count($participants) }})
        </span>
        <div class="flex-grow border-t-2 border-line"></div>
    </div>

    {{-- LISTE --}}
    <ul aria-label="Liste des participants inscrits" class="max-h-[280px] space-y-1.5 overflow-y-auto pr-1">
        @forelse($participants as $index => $name)
        @php
        $currentWeight = $weights[$index] ?? 1;
        $percentage = $weights !== null ? round(($currentWeight / $totalWeight) * 100) : 0;
        @endphp

        <li class="group flex min-h-[46px] items-center justify-between rounded-xl border-2 border-ink bg-panel px-4 py-2.5 text-sm transition" :class="{ 'bg-wash shadow-hard': editingIndex === {{ $index }} }">
            {{-- LECTURE --}}
            <div x-show="editingIndex !== {{ $index }}" class="flex w-full items-center justify-between gap-3">
                <span class="flex min-w-0 items-center gap-2.5">
                    @if(isset($colors[$index]))
                    <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-full border border-ink/30" style="background-color: {{ $colors[$index] }}" aria-hidden="true"></span>
                    @endif

                    <span class="cursor-pointer select-none truncate font-semibold text-ink" @unless($locked) @dblclick="editingIndex = {{ $index }}; editValue = '{{ addslashes($name) }}'; $nextTick(() => $refs.editInput_{{ $index }}.focus())" title="Double-cliquez pour modifier" @endunless>
                        {{ $name }}
                    </span>
                </span>

                <div class="flex shrink-0 items-center gap-2">
                    @if($weights !== null)
                    <div class="flex items-center gap-2" title="Poids du tirage pour {{ $name }}">
                        <div class="hidden w-11 shrink-0 flex-col items-end sm:flex">
                            <span class="text-[10px] font-bold tabular-nums leading-none text-subtle">
                                {{ $percentage }}%
                            </span>
                            <div class="mt-1 h-1 w-full overflow-hidden rounded-full bg-line">
                                <div class="h-full rounded-full bg-primary transition-all duration-300" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center overflow-hidden rounded-lg border-2 border-ink bg-panel">
                            <label for="weight-{{ $index }}" class="sr-only">Poids de {{ $name }}</label>

                            @unless($locked)
                            <button type="button" wire:click="updateParticipantWeight({{ $index }}, {{ max($currentWeight - 1, 1) }})" @if($currentWeight <=1) disabled @endif class="flex h-7 w-6 items-center justify-center text-subtle transition hover:bg-wash hover:text-ink disabled:pointer-events-none disabled:opacity-30" title="Diminuer le poids de {{ $name }}" aria-label="Diminuer le poids de {{ $name }}">
                                −
                            </button>
                            @endunless

                            <input type="number" id="weight-{{ $index }}" min="1" max="100" value="{{ $currentWeight }}" @unless($locked) wire:change="updateParticipantWeight({{ $index }}, $event.target.value)" @else disabled @endunless class="w-9 border-x-2 border-ink bg-panel py-1 text-center text-xs font-bold text-ink focus:outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">

                            @unless($locked)
                            <button type="button" wire:click="updateParticipantWeight({{ $index }}, {{ min($currentWeight + 1, 100) }})" @if($currentWeight>= 100) disabled @endif
                                class="flex h-7 w-6 items-center justify-center text-subtle transition hover:bg-wash hover:text-ink disabled:pointer-events-none disabled:opacity-30"
                                title="Augmenter le poids de {{ $name }}"
                                aria-label="Augmenter le poids de {{ $name }}"
                                >
                                +
                            </button>
                            @endunless
                        </div>
                    </div>
                    @endif

                    @unless($locked)
                    <div class="flex items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100 focus-within:opacity-100">
                        <button type="button" @click="editingIndex = {{ $index }}; editValue = '{{ addslashes($name) }}'; $nextTick(() => $refs.editInput_{{ $index }}.focus())" class="rounded-lg p-1 text-subtle transition hover:bg-wash hover:text-ink focus:outline-none focus:ring-2 focus:ring-info" title="Modifier {{ $name }}" aria-label="Modifier le participant {{ $name }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>

                        <button type="button" wire:click="removeParticipant({{ $index }})" class="rounded-lg p-1 text-subtle transition hover:bg-danger/10 hover:text-danger focus:outline-none focus:ring-2 focus:ring-danger/30" title="Supprimer {{ $name }}" aria-label="Supprimer le participant {{ $name }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    @endunless
                </div>
            </div>

            {{-- ÉDITION --}}
            @unless($locked)
            <div x-show="editingIndex === {{ $index }}" x-cloak class="flex w-full items-center gap-2">
                @if(isset($colors[$index]))
                <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-full border border-ink/30" style="background-color: {{ $colors[$index] }}" aria-hidden="true"></span>
                @endif

                <input type="text" x-model="editValue" x-ref="editInput_{{ $index }}" @keydown.enter="$wire.updateParticipant({{ $index }}, editValue); editingIndex = null" @keydown.escape="editingIndex = null" class="flex-grow rounded-lg border-2 border-ink bg-panel px-2 py-1 text-xs text-ink focus:outline-none focus:ring-2 focus:ring-info" aria-label="Modifier le nom de {{ $name }}">

                <div class="flex shrink-0 items-center gap-1">
                    <button type="button" @click="$wire.updateParticipant({{ $index }}, editValue); editingIndex = null" class="rounded-lg p-1 text-success transition hover:bg-wash focus:outline-none focus:ring-2 focus:ring-success/30" title="Enregistrer" aria-label="Enregistrer les modifications">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>

                    <button type="button" @click="editingIndex = null" class="rounded-lg p-1 text-subtle transition hover:bg-wash hover:text-ink focus:outline-none focus:ring-2 focus:ring-info" title="Annuler" aria-label="Annuler la modification">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            @endunless
        </li>
        @empty
        <li class="list-none p-6 text-center text-sm text-faint">
            Aucun participant pour le moment.
        </li>
        @endforelse
    </ul>
</div>
