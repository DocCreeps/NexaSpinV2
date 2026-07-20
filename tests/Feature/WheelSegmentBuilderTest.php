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

it('makes weighted segments proportional to weight instead of equal', function () {
    // Poids [1, 3] sur 2 participants -> parts de 90° et 270° (ratio 1:3),
    // et non 180°/180° comme un partage égal. On le vérifie via le "large
    // arc flag" du path SVG : 0 pour une part <= 180°, 1 pour une part > 180°.
    $segments = WheelSegmentBuilder::build(['John', 'Jane'], weights: [1, 3]);

    $flagFor = fn(array $segment): string => (string) preg_match('/0 1,0/', (string) $segment['path']);

    expect($flagFor($segments[0]))->toBe('0') // Part de 90° : pas de grand arc
        ->and($flagFor($segments[1]))->toBe('1'); // Part de 270° : grand arc
});

it('keeps equal segments when no weights are provided (backward compatibility)', function () {
    $withoutWeights = WheelSegmentBuilder::build(['John', 'Jane', 'Bob']);
    $withNullWeights = WheelSegmentBuilder::build(['John', 'Jane', 'Bob'], weights: null);

    expect($withNullWeights)->toBe($withoutWeights);
});

it('targets the true center of a weighted (variable-size) slice for rotation', function () {
    // Poids [1, 3] sur 2 participants -> segment 0 = [0°, 90°] (centre 45°),
    // segment 1 = [90°, 360°] (centre 225°).
    $rotationForFirst = WheelSegmentBuilder::rotationFor(0, 2, weights: [1, 3]);
    $rotationForSecond = WheelSegmentBuilder::rotationFor(1, 2, weights: [1, 3]);

    expect($rotationForFirst)->toBe((6 * 360) + (360 - 45.0))
        ->and($rotationForSecond)->toBe((6 * 360) + (360 - 225.0));
});

it('falls back to equal slices when weights are missing, empty or non-positive', function () {
    $equal = WheelSegmentBuilder::build(['John', 'Jane']);

    expect(WheelSegmentBuilder::build(['John', 'Jane'], weights: []))->toBe($equal)
        ->and(WheelSegmentBuilder::build(['John', 'Jane'], weights: [0, 0]))->toBe($equal);
});
