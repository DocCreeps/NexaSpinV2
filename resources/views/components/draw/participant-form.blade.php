@props([
'participants' => [],
'colors' => [],
'locked' => false,
'error' => null,
'theme' => 'indigo',
'weights' => null,
])

@php
$themeClasses = [
'indigo' => [
'btn' => 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500/20 focus:border-indigo-500',
'ring' => 'focus:ring-indigo-500/20 focus:border-indigo-500',
'bar' => 'from-indigo-400 to-purple-500',
],
'orange' => [
'btn' => 'bg-orange-600 hover:bg-orange-700 focus:ring-orange-500/20 focus:border-orange-500',
'ring' => 'focus:ring-orange-500/20 focus:border-orange-500',
'bar' => 'from-orange-400 to-amber-500',
]
][$theme] ?? $themeClasses['indigo'];

$totalWeight = $weights !== null ? max(array_sum($weights), 1) : 1;
@endphp

<div {{ $attributes->class(['space-y-5']) }} x-data="{ editingIndex: null, editValue: '' }">

    {{-- ZONE 1 : FORMULAIRE D'AJOUT --}}
    @unless($locked)
    <form wire:submit.prevent="addParticipant" class="space-y-3">
        <div class="flex gap-2">
            <div class="relative flex-grow">
                <label for="participant-input" class="sr-only">Nom du nouveau participant</label>
                <input type="text" id="participant-input" wire:model="participant" placeholder="Nom du participant..." aria-invalid="{{ $error ? 'true' : 'false' }}" @if($error) aria-describedby="participant-error" @endif class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 {{ $themeClasses['ring'] }} transition placeholder:text-slate-400">
            </div>

            <button type="submit" class="rounded-xl {{ $themeClasses['btn'] }} active:scale-95 px-5 py-2.5 text-sm font-bold text-white shrink-0 shadow-sm transition focus:outline-none focus:ring-2">
                Ajouter
            </button>
        </div>

        @if($error)
        <p id="participant-error" role="alert" class="text-xs font-semibold text-red-500 flex items-center gap-1.5 px-1">
            <span aria-hidden="true">⚠️</span> {{ $error }}
        </p>
        @endif
    </form>
    @endunless

    {{-- SÉPARATEUR VISUEL --}}
    <div class="relative flex py-1 items-center" aria-hidden="true">
        <div class="flex-grow border-t border-slate-100"></div>
        <span class="flex-shrink mx-4 text-[10px] font-bold uppercase tracking-wider text-slate-400">
            Membres inscrits ({{ count($participants) }})
        </span>
        <div class="flex-grow border-t border-slate-100"></div>
    </div>

    {{-- ZONE 2 : LISTE DES INSCRITS --}}
    <ul aria-label="Liste des participants inscrits" class="space-y-1.5 max-h-[280px] overflow-y-auto pr-1">
        @forelse($participants as $index => $name)
        @php
        $currentWeight = $weights[$index] ?? 1;
        $percentage = $weights !== null ? round(($currentWeight / $totalWeight) * 100) : 0;
        @endphp
        <li class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/30 px-4 py-2.5 text-sm hover:bg-slate-50/80 transition group min-h-[46px]" :class="{ 'ring-2 ring-slate-200 bg-white hover:bg-white shadow-sm': editingIndex === {{ $index }} }">
            {{-- MODE LECTURE --}}
            <div x-show="editingIndex !== {{ $index }}" class="flex items-center justify-between w-full gap-3">
                <span class="flex items-center gap-2.5 min-w-0">
                    @if(isset($colors[$index]))
                    <span class="inline-block w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $colors[$index] }}" aria-hidden="true"></span>
                    @endif

                    <span class="font-semibold text-slate-700 truncate cursor-pointer select-none" @unless($locked) @dblclick="editingIndex = {{ $index }}; editValue = '{{ addslashes($name) }}'; $nextTick(() => $refs.editInput_{{ $index }}.focus())" title="Double-cliquez pour modifier" @endunless>
                        {{ $name }}
                    </span>
                </span>

                <div class="flex items-center gap-2 shrink-0">
                    @if($weights !== null)
                    {{-- BLOC POIDS : barre de probabilité + stepper --}}
                    <div class="flex items-center gap-2" title="Poids du tirage pour {{ $name }}">

                        {{-- Barre de probabilité --}}
                        <div class="hidden sm:flex flex-col items-end w-11 shrink-0">
                            <span class="text-[10px] font-bold text-slate-400 tabular-nums leading-none">
                                {{ $percentage }}%
                            </span>
                            <div class="w-full h-1 rounded-full bg-slate-100 overflow-hidden mt-1">
                                <div class="h-full rounded-full bg-gradient-to-r {{ $themeClasses['bar'] }} transition-all duration-300" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>

                        {{-- Stepper +/- --}}
                        <div class="flex items-center rounded-lg border border-slate-200 overflow-hidden bg-white shrink-0">
                            <label for="weight-{{ $index }}" class="sr-only">Poids de {{ $name }}</label>

                            @unless($locked)
                            <button type="button" wire:click="updateParticipantWeight({{ $index }}, {{ max($currentWeight - 1, 1) }})" @if($currentWeight <=1) disabled @endif class="w-6 h-7 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition disabled:opacity-30 disabled:pointer-events-none" title="Diminuer le poids de {{ $name }}" aria-label="Diminuer le poids de {{ $name }}">
                                −
                            </button>
                            @endunless

                            <input type="number" id="weight-{{ $index }}" min="1" max="100" value="{{ $currentWeight }}" @unless($locked) wire:change="updateParticipantWeight({{ $index }}, $event.target.value)" @else disabled @endunless class="w-9 border-x border-slate-200 py-1 text-xs text-center font-bold text-slate-700 focus:outline-none focus:ring-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">

                            @unless($locked)
                            <button type="button" wire:click="updateParticipantWeight({{ $index }}, {{ min($currentWeight + 1, 100) }})" @if($currentWeight>= 100) disabled @endif class="w-6 h-7 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition disabled:opacity-30 disabled:pointer-events-none" title="Augmenter le poids de {{ $name }}" aria-label="Augmenter le poids de {{ $name }}">
                                +
                            </button>
                            @endunless
                        </div>
                    </div>
                    @endif

                    @unless($locked)
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity">
                        {{-- Bouton Modifier --}}
                        <button type="button" @click="editingIndex = {{ $index }}; editValue = '{{ addslashes($name) }}'; $nextTick(() => $refs.editInput_{{ $index }}.focus())" class="text-slate-400 hover:text-indigo-600 p-1 rounded-lg hover:bg-indigo-50 transition focus:outline-none focus:ring-2 focus:ring-indigo-500/20" title="Modifier {{ $name }}" aria-label="Modifier le participant {{ $name }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>

                        {{-- Bouton Supprimer --}}
                        <button type="button" wire:click="removeParticipant({{ $index }})" class="text-slate-400 hover:text-red-500 p-1 rounded-lg hover:bg-red-50 transition focus:outline-none focus:ring-2 focus:ring-red-500/20" title="Supprimer {{ $name }}" aria-label="Supprimer le participant {{ $name }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    @endunless
                </div>
            </div>

            {{-- MODE ÉDITION INLINE --}}
            @unless($locked)
            <div x-show="editingIndex === {{ $index }}" x-cloak class="flex items-center gap-2 w-full">
                @if(isset($colors[$index]))
                <span class="inline-block w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $colors[$index] }}" aria-hidden="true"></span>
                @endif

                <input type="text" x-model="editValue" x-ref="editInput_{{ $index }}" @keydown.enter="$wire.updateParticipant({{ $index }}, editValue); editingIndex = null" @keydown.escape="editingIndex = null" class="flex-grow rounded-lg border border-slate-200 px-2 py-1 text-xs focus:outline-none focus:ring-2 {{ $themeClasses['ring'] }} transition" aria-label="Modifier le nom de {{ $name }}">

                <div class="flex items-center gap-1 shrink-0">
                    {{-- Confirmer l'édition --}}
                    <button type="button" @click="$wire.updateParticipant({{ $index }}, editValue); editingIndex = null" class="text-emerald-600 hover:text-emerald-700 p-1 rounded-lg hover:bg-emerald-50 transition focus:outline-none focus:ring-2 focus:ring-emerald-500/20" title="Enregistrer" aria-label="Enregistrer les modifications">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>

                    {{-- Annuler l'édition --}}
                    <button type="button" @click="editingIndex = null" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition focus:outline-none focus:ring-2 focus:ring-slate-500/20" title="Annuler" aria-label="Annuler la modification">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            @endunless
        </li>
        @empty
        <li class="p-6 text-center text-slate-400 text-sm list-none">
            Aucun participant pour le moment.
        </li>
        @endforelse
    </ul>

</div>
