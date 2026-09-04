<?php

use App\Application\Draw\Support\WheelSegmentBuilder;

it('builds exactly one segment per participant, in the given order', function () {
    $segments = WheelSegmentBuilder::build(['Alice', 'Bob', 'Charlie']);

    expect($segments)->toHaveCount(3)
        ->and(array_column($segments, 'name'))->toBe(['Alice', 'Bob', 'Charlie']);
});

it('falls back to a full circle when there is only one participant left', function () {
    $segments = WheelSegmentBuilder::build(['LastOneStanding']);

    expect($segments)->toHaveCount(1)
        ->and($segments[0]['fullCircle'])->toBeTrue()
        ->and($segments[0]['path'])->toBeNull();
});

it('draws a real arc (not a full circle) as soon as there is more than one participant', function () {
    $segments = WheelSegmentBuilder::build(['Alice', 'Bob']);

    foreach ($segments as $segment) {
        expect($segment['fullCircle'])->toBeFalse()
            ->and($segment['path'])->not->toBeNull();
    }
});

it('assigns the same color to a participant every time and different colors to different participants', function () {
    $colors = WheelSegmentBuilder::assignColors(['Alice', 'Bob', 'Charlie']);

    expect($colors)->toHaveCount(3)
        ->and(array_unique($colors))->toHaveCount(3);

    $again = WheelSegmentBuilder::assignColors(['Alice', 'Bob', 'Charlie']);
    expect($again)->toBe($colors);
});

it('never spins the wheel backward across consecutive draws (regression: absolute vs cumulative rotation)', function () {
    // Historique du projet : la roue s'arrêtait au mauvais endroit après un
    // premier tirage car un angle absolu écrasait la rotation déjà appliquée.
    // Le contrat attendu est simple : chaque nouveau tirage doit faire
    // avancer la roue, jamais reculer.
    $rotation = 0;

    foreach ([2, 0, 4, 1, 3, 0, 2] as $targetIndex) {
        $result = WheelSegmentBuilder::cumulativeRotationFor(
            targetIndex: $targetIndex,
            total: 5,
            currentRotation: $rotation,
        );

        expect($result['delta'])->toBeGreaterThan(0)
            ->and($result['newRotation'])->toBeGreaterThan($rotation);

        $rotation = $result['newRotation'];
    }
});

it('produces a positive multi-turn rotation for a single, non-cumulative draw', function () {
    $rotation = WheelSegmentBuilder::rotationFor(targetIndex: 0, total: 4);

    // Au moins un tour complet (l'animation ne doit jamais paraître statique).
    expect($rotation)->toBeGreaterThanOrEqual(360);
});
