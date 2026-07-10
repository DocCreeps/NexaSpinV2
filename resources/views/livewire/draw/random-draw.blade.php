<div class="max-w-3xl mx-auto p-6 space-y-8">

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

    @if($error)

        <p class="text-sm text-red-600">
            {{ $error }}
        </p>

    @endif

    <div class="space-y-2">

        @foreach($participants as $index => $p)

            <div
                class="flex items-center justify-between rounded-lg border px-4 py-2"
            >
                <span class="flex items-center gap-2">
                    <span
                        class="inline-block w-3 h-3 rounded-full shrink-0"
                        style="background-color: {{ $this->segments[$index]['color'] ?? '#ccc' }}"
                    ></span>
                    {{ $p }}
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

    @if(count($participants) > 0)

        <div
            class="flex flex-col md:flex-row items-center md:items-start gap-6"
            x-data="{ finished: false }"
            x-on:wheel-spin.window="finished = false"
            x-on:wheel-spin-finished.window="finished = true"
        >

            <x-draw.wheel
                :segments="$this->segments"
                :show-labels="$this->showLabelsOnWheel()"
            />

            @unless($this->showLabelsOnWheel())

                <div class="w-full md:w-56 space-y-1 max-h-[320px] overflow-y-auto">

                    <p class="text-sm font-medium text-gray-500 mb-2">
                        Participants ({{ count($participants) }})
                    </p>

                    @foreach($this->segments as $segment)

                        <div class="flex items-center gap-2 text-sm">
                            <span
                                class="inline-block w-3 h-3 rounded-full shrink-0"
                                style="background-color: {{ $segment['color'] }}"
                            ></span>
                            <span class="truncate">{{ $segment['name'] }}</span>
                        </div>

                    @endforeach

                </div>

            @endunless

            @if($result)

                <div
                    x-show="finished"
                    x-cloak
                    class="w-full md:w-56 rounded-xl bg-green-100 p-6 text-center self-center"
                >
                    <p class="text-sm text-gray-500">
                        Gagnant
                    </p>

                    <p class="text-2xl font-bold">
                        {{ $result }}
                    </p>
                </div>

            @endif

        </div>

    @endif

    <button
        wire:click="draw"
        x-data="{ spinning: false }"
        x-on:wheel-spin.window="spinning = true"
        x-on:wheel-spin-finished.window="spinning = false"
        x-bind:disabled="spinning"
        class="w-full rounded-lg bg-black px-4 py-3 text-white disabled:opacity-50"
    >
        Lancer le tirage
    </button>

</div>
