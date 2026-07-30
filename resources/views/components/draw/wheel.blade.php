@props([
'segments' => [],
'showLabels' => true,
'event' => 'wheel-spin',
])

<div {{ $attributes->class(['relative mx-auto w-full max-w-[320px]']) }} x-data="{
        rotation: 0,
        spinning: false
    }" x-on:{{ $event }}.window="
        spinning = true;
        rotation += $event.detail?.rotation ?? 0;

        setTimeout(() => {
            spinning = false;
            $dispatch('{{ $event }}-finished');
        }, 4500);
    ">
    {{-- Pointeur --}}
    <div class="absolute left-1/2 top-[-6px] z-10 -translate-x-1/2">
        <div class="h-0 w-0 border-l-[12px] border-r-[12px] border-t-[20px] border-l-transparent border-r-transparent border-t-ink"></div>
    </div>

    {{-- Cadre arcade --}}
    <div class="card-hard overflow-hidden rounded-full border-4 border-ink bg-panel p-1">
        <svg viewBox="0 0 300 300" class="w-full">
            <g x-bind:style="`
                    transform: rotate(${rotation}deg);
                    transform-origin: 150px 150px;
                    transition: transform 4.5s cubic-bezier(.17,.67,.2,1);
                `">
                @foreach($segments as $segment)
                @if($segment['fullCircle'] ?? false)
                <circle cx="150" cy="150" r="150" fill="{{ $segment['color'] }}" />
                @else
                <path d="{{ $segment['path'] }}" fill="{{ $segment['color'] }}" stroke="#17171B" stroke-width="2" />
                @endif

                @if($showLabels)
                <text transform="{{ $segment['labelTransform'] }}" text-anchor="middle" class="fill-white text-[11px] font-semibold" style="paint-order: stroke; stroke: rgba(23,23,27,.45); stroke-width: 2px;">
                    {{ Str::limit($segment['name'], 12) }}
                </text>
                @endif
                @endforeach
            </g>

            {{-- Centre --}}
            <circle cx="150" cy="150" r="12" fill="#FAFAF8" stroke="#17171B" stroke-width="3" />
            <circle cx="150" cy="150" r="4" fill="#17171B" />
        </svg>
    </div>
</div>
