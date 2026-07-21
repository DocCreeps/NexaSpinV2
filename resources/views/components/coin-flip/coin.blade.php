@props([
'event' => 'coin-flip',
])

<div {{ $attributes->class(['relative w-44 h-44 sm:w-52 sm:h-52 mx-auto select-none']) }} style="perspective: 1000px;" x-data="{
        rotation: 0,
        spins: 0,
        isFlipping: false
     }" x-on:{{ $event }}.window="
        isFlipping = true;
        spins++;
        rotation = (spins * 1080) + ($event.detail.face === 'Pile' ? 180 : 0);

        setTimeout(() => {
            isFlipping = false;
            $dispatch('{{ $event }}-finished');
        }, 1000);
     ">

    {{-- Ombre sous la pièce --}}
    <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 w-3/4 h-3 rounded-full bg-slate-900/15 blur-sm transition-opacity duration-300" :class="isFlipping ? 'opacity-30' : 'opacity-100'">
    </div>

    {{-- Pièce 3D --}}
    <div class="relative w-full h-full" x-bind:style="`
            transform: rotateY(${rotation}deg);
            transform-style: preserve-3d;
            transition: transform 1s cubic-bezier(.2, .8, .3, 1);
         `">

        {{-- FACE (Or) --}}
        <div class="absolute inset-0 rounded-full flex items-center justify-center
                    bg-gradient-to-br from-amber-300 via-amber-400 to-amber-500
                    border-4 border-amber-200 shadow-lg" style="backface-visibility: hidden;">

            <div class="w-[88%] h-[88%] rounded-full border border-amber-600/30 flex items-center justify-center">
                <span class="text-2xl sm:text-3xl font-extrabold text-amber-950 tracking-wider">
                    FACE
                </span>
            </div>
        </div>

        {{-- PILE (Argent) --}}
        <div class="absolute inset-0 rounded-full flex items-center justify-center
                    bg-gradient-to-br from-slate-200 via-slate-300 to-slate-400
                    border-4 border-slate-100 shadow-lg" style="backface-visibility: hidden; transform: rotateY(180deg);">

            <div class="w-[88%] h-[88%] rounded-full border border-slate-500/30 flex items-center justify-center">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-wider">
                    PILE
                </span>
            </div>
        </div>

    </div>
</div>
