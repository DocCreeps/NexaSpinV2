<div class="max-w-xl mx-auto p-6 space-y-6">

    <div class="flex gap-2">

        <input
            type="text"
            wire:model="participant"
            wire:keydown.enter="addParticipant"
            placeholder="Nom du participant"
            class="w-full rounded-lg border px-4 py-2"
        >

        <button
            wire:click="addParticipant"
            class="rounded-lg bg-blue-500 px-4 py-2 text-white"
        >
            Ajouter
        </button>

    </div>

    <div class="space-y-2">

        @foreach($participants as $index => $participant)

            <div
                class="flex items-center justify-between rounded-lg border px-4 py-2"
            >
                <span>
                    {{ $participant }}
                </span>

                <button
                    wire:click="removeParticipant({{ $index }})"
                    class="text-red-500"
                >
                    ✕
                </button>
            </div>

        @endforeach

    </div>

    <button
        wire:click="draw"
        class="w-full rounded-lg bg-black px-4 py-3 text-white"
    >
        Lancer le tirage
    </button>

    @if($result)

        <div class="rounded-xl bg-green-100 p-6 text-center">

            <p class="text-sm text-gray-500">
                Gagnant
            </p>

            <p class="text-3xl font-bold">
                {{ $result }}
            </p>

        </div>

    @endif

</div>
