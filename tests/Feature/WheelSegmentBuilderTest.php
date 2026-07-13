<?php

use App\Application\Draw\Support\WheelSegmentBuilder;

it('builds one segment per participant', function () {
    $segments = WheelSegmentBuilder::build(['John', 'Jane', 'Bob']);

    expect($segments)->toHaveCount(3)
        ->and(collect($segments)->pluck('name')->all())->toBe(['John', 'Jane', 'Bob']);
});

it('returns no segments for an empty list', function () {
    expect(WheelSegmentBuilder::build([]))->toBe([]);
});

it('marks a single participant as a full circle instead of a degenerate 360° arc', function () {
    // Un arc SVG ne peut pas tracer un cercle complet quand le point de
    // départ égale le point d'arrivée (arc de longueur nulle, invisible).
    $segments = WheelSegmentBuilder::build(['Solo']);

    expect($segments)->toHaveCount(1)
        ->and($segments[0]['fullCircle'])->toBeTrue()
        ->and($segments[0]['path'])->toBeNull();
});

it('reuses fixed colors when provided instead of recomputing the palette', function () {
    $colors = ['John' => 'hsl(10, 70%, 55%)', 'Jane' => 'hsl(200, 70%, 55%)'];

    $segments = WheelSegmentBuilder::build(['John', 'Jane'], $colors);

    expect(collect($segments)->firstWhere('name', 'John')['color'])->toBe('hsl(10, 70%, 55%)')
        ->and(collect($segments)->firstWhere('name', 'Jane')['color'])->toBe('hsl(200, 70%, 55%)');
});

it('assigns one stable color per participant', function () {
    $colors = WheelSegmentBuilder::assignColors(['John', 'Jane', 'Bob']);

    expect($colors)->toHaveCount(3)
        ->and(array_keys($colors))->toBe(['John', 'Jane', 'Bob'])
        ->and(array_unique($colors))->toHaveCount(3);
});

it('targets a full number of spins plus the winning slice angle', function () {
    // 4 participants -> chaque part fait 90°. Le gagnant à l'index 0 est
    // centré à 45°, donc la roue doit tourner de (6 tours) + (360 - 45).
    $rotation = WheelSegmentBuilder::rotationFor(0, 4);

    expect($rotation)->toBe((6 * 360) + (360 - 45.0));
});
