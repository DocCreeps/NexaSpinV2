@props([
'event' => 'coin-flip',
'pileLabel' => 'Pile',
'faceLabel' => 'Face',
])

<div {{ $attributes->class(['relative mx-auto h-44 w-44 select-none sm:h-52 sm:w-52']) }} style="perspective: 1000px;" x-data="{
        rotation: 0,
        spins: 0,
        isFlipping: false
    }" x-on:{{ $event }}.window="
        isFlipping = true;
        spins++;
        rotation = (spins * 1080) + ($event.detail.face === 'pile' ? 180 : 0);

        setTimeout(() => {
            isFlipping = false;
            $dispatch('{{ $event }}-finished');
        }, 1000);
    ">
    {{-- Ombre --}}
    <div class="absolute -bottom-6 left-1/2 h-3 w-3/4 -translate-x-1/2 rounded-full bg-ink/15 blur-sm transition-opacity duration-300" :class="isFlipping ? 'opacity-30' : 'opacity-100'" aria-hidden="true"></div>

    {{-- Pièce 3D --}}
    <div class="relative h-full w-full" x-bind:style="`
            transform: rotateY(${rotation}deg);
            transform-style: preserve-3d;
            transition: transform 1s cubic-bezier(.2, .8, .3, 1);
        `">
        {{-- FACE (or) --}}
        <div class="absolute inset-0 flex items-center justify-center rounded-full border-4 border-ink bg-gradient-to-br from-secondary via-[#f0b429] to-[#d97706] shadow-hard" style="backface-visibility: hidden;">
            <div class="flex h-[88%] w-[88%] items-center justify-center rounded-full border-2 border-ink/20 px-2">
                <span class="break-words text-center font-display text-xl uppercase tracking-wider text-ink sm:text-2xl">
                    {{ $faceLabel }}
                </span>
            </div>
        </div>

        {{-- PILE (argent) --}}
        <div class="absolute inset-0 flex items-center justify-center rounded-full border-4 border-ink bg-gradient-to-br from-[#f3f4f6] via-[#d1d5db] to-[#9ca3af] shadow-hard" style="backface-visibility: hidden; transform: rotateY(180deg);">
            <div class="flex h-[88%] w-[88%] items-center justify-center rounded-full border-2 border-ink/20 px-2">
                <span class="break-words text-center font-display text-xl uppercase tracking-wider text-ink sm:text-2xl">
                    {{ $pileLabel }}
                </span>
            </div>
        </div>
    </div>
</div>
