@props([
'participants' => [],
'colors' => [],
'locked' => false,
'error' => null,
])

<div {{ $attributes->class(['space-y-4']) }}>

    @if($error)
    <p class="text-sm text-red-600">
        {{ $error }}
    </p>
    @endif

    @unless($locked)
    <div class="flex gap-2">
        <input type="text" wire:model="participant" wire:keydown.enter="addParticipant" placeholder="Nom du participant" class="w-full rounded-lg border px-4 py-2">
        <button wire:click="addParticipant" class="rounded-lg bg-blue-500 px-4 py-2 text-white shrink-0">
            Ajouter
        </button>
    </div>
    @endunless

    <div class="space-y-1 max-h-[280px] overflow-y-auto">
        @foreach($participants as $index => $name)
        <div class="flex items-center justify-between rounded-lg border px-3 py-1.5 text-sm">
            <span class="flex items-center gap-2">
                @isset($colors[$index])
                <span class="inline-block w-3 h-3 rounded-full shrink-0" style="background-color: {{ $colors[$index] }}"></span>
                @endisset

                {{ $name }}
            </span>

            @unless($locked)
            <button wire:click="removeParticipant({{ $index }})" class="text-red-500">
                ✕
            </button>
            @endunless
        </div>
        @endforeach
    </div>

</div>
